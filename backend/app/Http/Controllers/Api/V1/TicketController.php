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
        $query = Ticket::with(['unit', 'user']);

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
            $query->where('title', 'like', '%'.$request->search.'%');
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
        $allowed = ['created_at', 'updated_at', 'status', 'urgency_level', 'title'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $tickets = $query->paginate($request->input('per_page', 15));

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
    public function show($id)
    {
        $ticket = Ticket::with([
            'items', 'photos', 'purchaseOrders.vendor',
            'bastDocument', 'unit',
            'targetUnit',
            'user', 'aspApprovalBy', 'kdApprovalBy',
        ])->findOrFail($id);

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
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'revision_required') {
            return response()->json(['success' => false, 'message' => 'Tiket tidak dalam status revisi.'], 422);
        }

        $old = $ticket->only(['title', 'problem_cause', 'status']);
        $ticket->update([
            'title'               => $request->input('title', $ticket->title),
            'problem_cause'       => $request->input('problem_cause', $ticket->problem_cause),
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
}
