<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct(protected AuditLogService $audit) {}

    /**
     * GET /purchase-orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['ticket', 'vendor']);

        // Filter
        if ($request->filled('status')) $query->where('po_status', $request->status);
        if ($request->filled('year'))   $query->where('year', $request->year);
        if ($request->filled('search')) $query->where(function($q) use ($request) {
            $q->where('po_number', 'like', '%'.$request->search.'%')
              ->orWhere('vendor_name_snapshot', 'like', '%'.$request->search.'%');
        });

        $pos = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $pos->items(),
            'meta'    => [
                'current_page' => $pos->currentPage(),
                'per_page'     => $pos->perPage(),
                'total'        => $pos->total(),
                'last_page'    => $pos->lastPage(),
            ],
        ]);
    }

    /**
     * POST /purchase-orders/draft
     */
    public function storeDraft(Request $request)
    {
        $validated = $request->validate([
            'ticket_id'             => 'nullable|exists:tickets,id',
            'unit_id'               => 'required|exists:units,id',
            'vendor_id'             => 'nullable|exists:vendors,id',
            'vendor_name_snapshot'  => 'required|string|max:150',
            'vendor_pic_snapshot'   => 'nullable|string|max:100',
            'vendor_phone_snapshot' => 'nullable|string|max:40',
            'vendor_address_snapshot'=> 'nullable|string',
            'subtotal'              => 'required|numeric|min:0',
            'discount_amount'       => 'nullable|numeric|min:0',
            'grand_total'           => 'required|numeric|min:0',
            'terbilang_text'        => 'required|string',
            'is_incidental_vendor'  => 'boolean',
        ]);

        $user = $request->user();
        $po   = PurchaseOrder::create(array_merge($validated, [
            'year'       => now()->year,
            'po_status'  => 'draft',
            'created_by' => $user->id,
        ]));

        $this->audit->log([
            'module'      => 'purchase_order',
            'record_id'   => $po->id,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => 'CREATE_DRAFT_PO',
            'description' => "{$user->name} membuat draf PO #{$po->id} untuk {$po->vendor_name_snapshot}.",
            'new_values'  => ['po_status' => 'draft', 'grand_total' => $po->grand_total],
        ], $request);

        return response()->json(['success' => true, 'message' => 'Draf PO berhasil dibuat.', 'data' => $po], 201);
    }

    /**
     * POST /purchase-orders/{id}/issue-number
     * ──────────────────────────────────────────────────────────────
     * ATOMIC PO Number generation menggunakan DB transaction + lock
     * agar tidak ada nomor yang bentrok/lompat meski request concurrent.
     *
     * Format: PO-{TAHUN}-{SEQUENCE 4 DIGIT}
     * Contoh: PO-2026-0001, PO-2026-0002, ...
     * ──────────────────────────────────────────────────────────────
     */
    public function issueNumber(Request $request, $id)
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($id, $user, $request) {
            // Lock the row untuk prevent race condition
            $po = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if ($po->po_number !== null) {
                throw new \Exception('Nomor PO sudah diterbitkan sebelumnya: '.$po->po_number);
            }
            if ($po->po_status !== 'draft') {
                throw new \Exception('Hanya PO berstatus Draft yang dapat diterbitkan nomornya.');
            }

            $year = now()->year;

            // Atomic: cari sequence terakhir di tahun ini + lock
            $lastSeq = PurchaseOrder::where('year', $year)
                ->whereNotNull('sequence_number')
                ->lockForUpdate()
                ->max('sequence_number') ?? 0;

            $nextSeq = $lastSeq + 1;
            $poNumber = sprintf('PO-%d-%04d', $year, $nextSeq);

            $po->update([
                'year'            => $year,
                'sequence_number' => $nextSeq,
                'po_number'       => $poNumber,
                'po_status'       => 'pending_approval',
                'approved_by'     => $user->id,
                'approved_at'     => now(),
            ]);

            return $po;
        });

        $this->audit->logPoIssued($result->id, $result->po_number, $user, $request);

        return response()->json([
            'success'  => true,
            'message'  => 'Nomor PO berhasil diterbitkan: '.$result->po_number,
            'data'     => $result,
        ]);
    }

    /**
     * GET /purchase-orders/{id}/pdf — Placeholder download PDF
     */
    public function downloadPdf($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // TODO: Implement PDF generation (e.g., with barryvdh/laravel-dompdf)
        return response()->json([
            'success' => false,
            'message' => 'Fitur ekspor PDF akan segera tersedia.',
            'data'    => ['po_number' => $po->po_number],
        ], 501);
    }

    /**
     * POST /purchase-orders/{id}/upload-scan
     */
    public function uploadScan(Request $request, $id)
    {
        $request->validate(['scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $po   = PurchaseOrder::findOrFail($id);
        $user = $request->user();

        $path = $request->file('scan')->store('po-scans', 'public');
        $po->update(['scan_document_path' => $path, 'po_status' => 'signed_archived']);

        $this->audit->log([
            'module'      => 'purchase_order',
            'record_id'   => $po->id,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => 'UPLOAD_SCAN',
            'description' => "{$user->name} mengunggah scan PO {$po->po_number}.",
            'new_values'  => ['scan_document_path' => $path, 'po_status' => 'signed_archived'],
        ], $request);

        return response()->json(['success' => true, 'message' => 'Scan PO berhasil diunggah.', 'data' => $po]);
    }
}
