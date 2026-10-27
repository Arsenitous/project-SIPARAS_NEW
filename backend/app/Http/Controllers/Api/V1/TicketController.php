<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\TicketPhoto;
use App\Services\ApprovalRoutingService;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function __construct(
        protected ApprovalRoutingService $routing,
        protected AuditLogService $audit,
    ) {}

    /**
     * GET /tickets — Daftar tiket dengan isolasi berdasarkan role
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Ticket::with(['unit', 'user'])->withCount(['purchaseOrders', 'bastDocument']);

        // Role-based data isolation
        match ($user->role) {
            'kepsek', 'dept_ygpkb' => $query->where('unit_id', $user->unit_id),
            'asp_wilayah'          => $query->whereHas('unit', fn($q) => $q->where('asp_territory', $user->asp_territory)),
            default                => null, // kd_sarpras, admin, koordinator — lihat semua
        };

        // Filter opsional dari query params
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('procedure_type')) {
            $query->where('procedure_type', $request->procedure_type);
        }
        if ($request->filled('urgency_level')) {
            $query->where('urgency_level', $request->urgency_level);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanId = ltrim(preg_replace('/^#0*/', '', $s), '0');
            $query->where(function ($q) use ($s, $cleanId) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%")
                  ->orWhereHas('unit', fn($uq) => $uq->where('name', 'like', "%{$s}%"));
                if (is_numeric($cleanId) && (int)$cleanId > 0) {
                    $q->orWhere('id', (int) $cleanId);
                }
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['id', 'created_at', 'updated_at', 'status', 'urgency_level', 'title', 'procedure_type'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all' || (int)$perPage > 500) {
            $perPage = 500;
        }
        $tickets = $query->paginate((int) $perPage);

        $tickets->getCollection()->transform(function ($ticket) use ($user) {
            $ticket->can_delete = $this->canUserDeleteTicket($ticket, $user);
            return $ticket;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tiket',
            'data'    => $tickets->items(),
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'per_page'     => $tickets->perPage(),
                'total'        => $tickets->total(),
                'last_page'    => $tickets->lastPage(),
            ],
        ]);
    }

    /**
     * GET /tickets/{id} — Detail tiket
     */
    public function show(Request $request, $id)
    {
        $ticket = Ticket::with([
            'items', 'photos', 'purchaseOrders.vendor',
            'bastDocument', 'unit',
            'targetUnit',
            'user', 'aspApprovalBy', 'kdApprovalBy',
        ])->findOrFail($id);

        $ticket->can_delete = $this->canUserDeleteTicket($ticket, $request->user());

        return response()->json([
            'success' => true,
            'data'    => $ticket,
        ]);
    }

    /**
     * GET /tickets/{id}/pdf — Download tiket sebagai PDF
     */
    public function downloadPdf($id)
    {
        $ticket = Ticket::with([
            'items', 'photos', 'unit',
            'targetUnit',
            'user', 'aspApprovalBy', 'kdApprovalBy',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.ticket-detail', compact('ticket'))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $filename = 'Tiket-SIPARAS-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }


    /**
     * POST /tickets — Buat tiket baru
     */
    public function store(StoreTicketRequest $request)
    {
        $user    = $request->user();
        $routing = $this->routing->determine($user->role, $request->procedure_type);

        DB::beginTransaction();
        try {
            // 1. Hitung estimated_cost dari items (pengadaan)
            $estimatedCost = $request->input('estimated_cost', 0);
            if ($request->procedure_type === 'pengadaan' && $request->has('items')) {
                $estimatedCost = collect($request->items)->sum(fn($item) =>
                    ($item['quantity'] ?? 1) * ($item['estimated_unit_price'] ?? 0)
                );
            }

            // 2. Buat tiket
            $ticket = Ticket::create([
                'user_id'                       => $user->id,
                'unit_id'                        => $request->unit_id,
                'location_name'                  => $request->input('location_name'),
                'procedure_type'                 => $request->procedure_type,
                'routing_path'                   => $routing['routing_path'],
                'status'                         => $routing['status'],
                'asp_approval_status'            => $routing['asp_approval_status'],
                'kd_approval_status'             => $routing['kd_approval_status'],
                'target_object_category'         => $request->input('target_object_category'),
                'specific_location'              => $request->input('specific_location'),
                'item_name'                      => $request->input('item_name'),
                'inventory_code'                 => $request->input('inventory_code', '-'),
                'target_unit_id'                 => $request->input('target_unit_id') ?: null,
                'target_location_name'           => $request->input('target_location_name'),
                'disposal_action'                => $request->input('disposal_action'),
                'disposal_destination_or_notes'  => $request->input('disposal_destination_or_notes'),
                'estimated_salvage_value'        => $request->input('estimated_salvage_value', 0),
                'title'                          => $request->title,
                'urgency_level'                  => $request->urgency_level,
                'problem_cause'                  => $request->input('problem_cause'),
                'action_plan'                    => $request->input('action_plan'),
                'estimated_cost'                 => $estimatedCost,
            ]);

            // 3. Simpan items (pengadaan / mutasi)
            if ($request->has('items') && in_array($request->procedure_type, ['pengadaan', 'mutasi'])) {
                foreach ($request->items as $item) {
                    $subtotal = ($item['quantity'] ?? 1) * ($item['estimated_unit_price'] ?? 0);
                    TicketItem::create([
                        'ticket_id'             => $ticket->id,
                        'inventory_code'        => $item['inventory_code'] ?? '-',
                        'item_description'      => $item['item_description'],
                        'quantity'              => $item['quantity'] ?? 1,
                        'unit_metric'           => $item['unit_metric'] ?? 'Unit',
                        'condition_status'      => $item['condition_status'] ?? null,
                        'estimated_unit_price'  => $item['estimated_unit_price'] ?? 0,
                        'estimated_subtotal'    => $subtotal,
                        'item_notes'            => $item['item_notes'] ?? null,
                    ]);
                }
            }

            // 4. Simpan foto-foto
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('ticket-photos/'.$ticket->id, 'public');
                    TicketPhoto::create([
                        'ticket_id'    => $ticket->id,
                        'photo_type'   => 'photo_before',
                        'file_path'    => $path,
                        'file_size_kb' => (int) round($photo->getSize() / 1024),
                    ]);
                }
            }

            // 5. Catat ke audit_log
            $this->audit->logTicketSubmit($ticket->id, $ticket->procedure_type, $user, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil diajukan.',
                'data'    => $ticket->load(['unit', 'items', 'photos']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tiket: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /tickets/{id}/photos
     */
    public function uploadPhotos(Request $request, $id)
    {
        $request->validate(['photos.*' => 'required|file|image|max:5120']);
        $ticket = Ticket::findOrFail($id);

        $uploaded = [];
        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store('ticket-photos/'.$ticket->id, 'public');
            $p = TicketPhoto::create([
                'ticket_id'    => $ticket->id,
                'photo_type'   => $request->input('photo_type', 'photo_evidence'),
                'file_path'    => $path,
                'file_size_kb' => (int) round($photo->getSize() / 1024),
                'caption'      => $request->input('caption'),
            ]);
            $uploaded[] = $p;
        }

        return response()->json(['success' => true, 'message' => 'Foto berhasil diunggah', 'data' => $uploaded]);
    }

    /**
     * PUT /tickets/{id}/revision — Submit revisi oleh pemohon
     */
    public function submitRevision(Request $request, $id)
    {
        $request->validate([
            'title'         => 'sometimes|string|max:200',
            'problem_cause' => 'sometimes|string',
            'action_plan'   => 'sometimes|nullable|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'revision_required') {
            return response()->json(['success' => false, 'message' => 'Tiket tidak dalam status revisi.'], 422);
        }

        $old = $ticket->only(['title', 'problem_cause', 'action_plan', 'status']);
        $ticket->update([
            'title'               => $request->input('title', $ticket->title),
            'problem_cause'       => $request->input('problem_cause', $ticket->problem_cause),
            'action_plan'         => $request->input('action_plan', $ticket->action_plan),
            'status'              => 'submitted_to_asp',
            'asp_approval_status' => 'pending',
        ]);

        $user = $request->user();
        $this->audit->log([
            'module'      => $ticket->procedure_type,
            'record_id'   => $ticket->id,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => 'REVISE',
            'description' => "{$user->name} mengirimkan revisi tiket #{$ticket->id}.",
            'old_values'  => $old,
            'new_values'  => $ticket->only(['title', 'problem_cause', 'status']),
        ], $request);

        return response()->json(['success' => true, 'message' => 'Revisi berhasil disimpan.', 'data' => $ticket]);
    }

    /**
     * POST /tickets/{id}/review-asp — ASP melakukan review
     */
    public function reviewAsp(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,revision_required,rejected',
            'notes'    => 'nullable|string|max:500',
        ]);

        $ticket = Ticket::findOrFail($id);
        $user   = $request->user();
        $oldStatus = $ticket->status;

        if (!in_array($ticket->status, ['submitted_to_asp'])) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak dalam antrean review ASP.'], 422);
        }

        $decision  = $request->decision;
        $newStatus = match ($decision) {
            'approved'          => 'submitted_to_kd',
            'revision_required' => 'revision_required',
            'rejected'          => 'rejected',
        };

        $ticket->update([
            'asp_approval_status' => $decision,
            'asp_approval_by'     => $user->id,
            'asp_approval_at'     => now(),
            'asp_notes'           => $request->notes,
            'status'              => $newStatus,
        ]);

        $this->audit->logAspReview($ticket->id, $decision, $oldStatus, $newStatus, $user, $request);

        return response()->json(['success' => true, 'message' => 'Review ASP berhasil.', 'data' => $ticket]);
    }

    /**
     * POST /tickets/{id}/approval-kd — KD melakukan approval
     */
    public function approvalKd(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,revision_required,rejected',
            'notes'    => 'nullable|string|max:500',
        ]);

        $ticket = Ticket::findOrFail($id);
        $user   = $request->user();
        $oldStatus = $ticket->status;

        if (!in_array($ticket->status, ['submitted_to_kd'])) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak dalam antrean approval KD.'], 422);
        }

        $decision  = $request->decision;
        $newStatus = match ($decision) {
            'approved'          => 'approved',
            'revision_required' => 'revision_required',
            'rejected'          => 'rejected',
        };

        $ticket->update([
            'kd_approval_status' => $decision,
            'kd_approval_by'     => $user->id,
            'kd_approval_at'     => now(),
            'kd_notes'           => $request->notes,
            'status'             => $newStatus,
        ]);

        $this->audit->logKdApproval($ticket->id, $decision, $oldStatus, $newStatus, $user, $request);

        return response()->json(['success' => true, 'message' => 'Approval KD berhasil.', 'data' => $ticket]);
    }

    /**
     * DELETE /tickets/{id} — Hapus tiket sesuai alur/prosedur SIPARAS
     */
    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::with(['purchaseOrders', 'bastDocument'])->findOrFail($id);
        $user   = $request->user();

        // 1. Cek ketergantungan dokumen hilir (PO & BAST)
        if ($ticket->purchaseOrders && $ticket->purchaseOrders->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak dapat dihapus karena sudah memiliki dokumen Purchase Order (PO) terkait.',
            ], 422);
        }

        if ($ticket->bastDocument) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak dapat dihapus karena sudah memiliki Berita Acara Serah Terima (BAST).',
            ], 422);
        }

        if (in_array($ticket->status, ['completed', 'signed_archived'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak dapat dihapus karena sudah berstatus selesai atau diarsipkan.',
            ], 422);
        }

        // 2. Evaluasi izin hapus berdasarkan role dan alur approval
        $canDelete = false;
        $errorMessage = 'Anda tidak memiliki wewenang untuk menghapus tiket ini.';

        if (in_array($user->role, ['superadmin', 'admin_sarpras'])) {
            $canDelete = true;
        } elseif ($user->role === 'kepsek') {
            $isOwner = ((int)$ticket->unit_id === (int)$user->unit_id) || ((int)$ticket->user_id === (int)$user->id);
            if (!$isOwner) {
                $errorMessage = 'Anda hanya dapat menghapus tiket yang berasal dari unit sekolah Anda.';
            } elseif ($ticket->status !== 'submitted_to_asp' || $ticket->asp_approval_status !== 'pending') {
                $errorMessage = 'Tiket tidak dapat dihapus oleh Kepsek karena sudah diteruskan ke KD Sarpras atau sudah selesai direviu ASP.';
            } else {
                $canDelete = true;
            }
        } elseif ($user->role === 'dept_ygpkb') {
            $isOwner = ((int)$ticket->unit_id === (int)$user->unit_id) || ((int)$ticket->user_id === (int)$user->id);
            if (!$isOwner) {
                $errorMessage = 'Anda hanya dapat menghapus tiket dari departemen Anda.';
            } elseif ($ticket->kd_approval_status === 'pending' && in_array($ticket->status, ['submitted_to_kd', 'submitted_to_asp'])) {
                $canDelete = true;
            } else {
                $errorMessage = 'Tiket tidak dapat dihapus karena KD Sarpras sudah mereviu tiket ini.';
            }
        } elseif ($user->role === 'asp_wilayah') {
            $isCreator = ((int)$ticket->user_id === (int)$user->id) || ($ticket->routing_path === 'jalur_3_temuan_asp');
            if (!$isCreator) {
                $errorMessage = 'ASP Wilayah hanya dapat menghapus tiket temuan lapangan yang diajukan sendiri.';
            } elseif ($ticket->kd_approval_status === 'pending' && $ticket->status === 'submitted_to_kd') {
                $canDelete = true;
            } else {
                $errorMessage = 'Tiket tidak dapat dihapus karena KD Sarpras sudah mereviu tiket ini.';
            }
        } elseif ($user->role === 'kd_sarpras') {
            $canDelete = true;
        }

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 403);
        }

        // 3. Eksekusi penghapusan tiket
        DB::beginTransaction();
        try {
            // Catat log audit sebelum tiket dihapus
            $this->audit->log([
                'module'      => $ticket->procedure_type,
                'record_id'   => $ticket->id,
                'user_id'     => $user->id,
                'role'        => $user->role,
                'action'      => 'DELETE',
                'description' => "{$user->name} ({$user->role}) menghapus tiket #{$ticket->id}: {$ticket->title}.",
                'old_values'  => [
                    'id'                  => $ticket->id,
                    'title'               => $ticket->title,
                    'procedure_type'      => $ticket->procedure_type,
                    'routing_path'        => $ticket->routing_path,
                    'status'              => $ticket->status,
                    'asp_approval_status' => $ticket->asp_approval_status,
                    'kd_approval_status'  => $ticket->kd_approval_status,
                ],
            ], $request);

            // Hapus file fisik dari storage
            Storage::disk('public')->deleteDirectory('ticket-photos/' . $ticket->id);

            // Hapus items dan photos
            $ticket->photos()->delete();
            $ticket->items()->delete();

            // Hapus tiket
            $ticket->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tiket #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tiket: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper evaluasi apakah user saat ini dapat menghapus tiket sesuai alur/prosedur
     */
    public function canUserDeleteTicket(Ticket $ticket, $user): bool
    {
        if (!$user) {
            return false;
        }

        // Cek jika ada PO
        $hasPo = isset($ticket->purchase_orders_count)
            ? (int)$ticket->purchase_orders_count > 0
            : ($ticket->purchaseOrders && $ticket->purchaseOrders->count() > 0);
        if ($hasPo) {
            return false;
        }

        // Cek jika ada BAST
        $hasBast = isset($ticket->bast_document_count)
            ? (int)$ticket->bast_document_count > 0
            : (bool)$ticket->bastDocument;
        if ($hasBast) {
            return false;
        }

        // Cek status akhir
        if (in_array($ticket->status, ['completed', 'signed_archived'])) {
            return false;
        }

        $role = $user->role;

        if (in_array($role, ['superadmin', 'admin_sarpras'])) {
            return true;
        }

        if ($role === 'kepsek') {
            $isOwner = ((int)$ticket->unit_id === (int)$user->unit_id) || ((int)$ticket->user_id === (int)$user->id);
            if (!$isOwner) return false;

            // Kepsek hanya boleh hapus selama tiket masih di tahap awal belum direviu ASP
            // Begitu sudah sampai ke KD Sarpras atau sudah selesai direviu ASP, Kepsek tidak boleh hapus
            return $ticket->status === 'submitted_to_asp' && $ticket->asp_approval_status === 'pending';
        }

        if ($role === 'dept_ygpkb') {
            $isOwner = ((int)$ticket->unit_id === (int)$user->unit_id) || ((int)$ticket->user_id === (int)$user->id);
            if (!$isOwner) return false;

            return $ticket->kd_approval_status === 'pending' && in_array($ticket->status, ['submitted_to_kd', 'submitted_to_asp']);
        }

        if ($role === 'asp_wilayah') {
            $isCreator = ((int)$ticket->user_id === (int)$user->id) || ($ticket->routing_path === 'jalur_3_temuan_asp');
            if (!$isCreator) return false;

            return $ticket->kd_approval_status === 'pending' && $ticket->status === 'submitted_to_kd';
        }

        if ($role === 'kd_sarpras') {
            return true;
        }

        return false;
    }
}
