<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * AuditLogService
 *
 * Mencatat setiap perubahan status / aksi penting ke tabel audit_logs
 * secara otomatis. Tabel audit_logs bersifat append-only (diproteksi trigger DB).
 */
class AuditLogService
{
    /**
     * Catat satu entri audit log.
     *
     * @param  array $data
     *   - module:       string  (perbaikan|pengadaan|mutasi|pemutihan|purchase_order|user_auth|...)
     *   - record_id:    int     (ID tiket / ID dokumen terkait)
     *   - user_id:      int
     *   - role:         string
     *   - action:       string  (SUBMIT|ASP_APPROVE|KD_APPROVE|REVISE|REJECT|ISSUE_PO_NUMBER|...)
     *   - description:  string  (Kalimat deskriptif human-readable)
     *   - old_values:   array|null
     *   - new_values:   array|null
     *   - request:      \Illuminate\Http\Request|null  (untuk IP & user-agent)
     */
    public function log(array $data, ?Request $request = null): AuditLog
    {
        return AuditLog::create([
            'module'      => $data['module'],
            'record_id'   => $data['record_id'],
            'user_id'     => $data['user_id'],
            'role'        => $data['role'],
            'action'      => $data['action'],
            'description' => $data['description'],
            'old_values'  => isset($data['old_values']) ? json_encode($data['old_values']) : null,
            'new_values'  => isset($data['new_values']) ? json_encode($data['new_values']) : null,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }

    /**
     * Shortcut: Log aksi submit tiket baru
     */
    public function logTicketSubmit(int $ticketId, string $procedureType, object $user, Request $request): void
    {
        $this->log([
            'module'      => $procedureType,
            'record_id'   => $ticketId,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => 'SUBMIT',
            'description' => "{$user->name} ({$user->role}) mengajukan tiket #{$ticketId} — {$procedureType}.",
            'new_values'  => ['procedure_type' => $procedureType, 'status' => 'submitted'],
        ], $request);
    }

    /**
     * Shortcut: Log review/aksi ASP
     */
    public function logAspReview(int $ticketId, string $decision, string $oldStatus, string $newStatus, object $user, Request $request): void
    {
        $actionMap = ['approved' => 'ASP_APPROVE', 'revision_required' => 'ASP_REVISION', 'rejected' => 'ASP_REJECT'];
        $labelMap  = ['approved' => 'menyetujui', 'revision_required' => 'meminta revisi pada', 'rejected' => 'menolak'];
        $this->log([
            'module'      => 'perbaikan', // will be overridden per-call if needed
            'record_id'   => $ticketId,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => $actionMap[$decision] ?? 'ASP_REVIEW',
            'description' => "{$user->name} (ASP Wilayah) {$labelMap[$decision]} tiket #{$ticketId}.",
            'old_values'  => ['status' => $oldStatus, 'asp_approval_status' => 'pending'],
            'new_values'  => ['status' => $newStatus, 'asp_approval_status' => $decision],
        ], $request);
    }

    /**
     * Shortcut: Log approval/aksi KD
     */
    public function logKdApproval(int $ticketId, string $decision, string $oldStatus, string $newStatus, object $user, Request $request): void
    {
        $actionMap = ['approved' => 'KD_APPROVE', 'revision_required' => 'KD_REVISION', 'rejected' => 'KD_REJECT'];
        $labelMap  = ['approved' => 'menyetujui', 'revision_required' => 'meminta revisi pada', 'rejected' => 'menolak'];
        $this->log([
            'module'      => 'perbaikan',
            'record_id'   => $ticketId,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => $actionMap[$decision] ?? 'KD_REVIEW',
            'description' => "{$user->name} (KD Sarpras) {$labelMap[$decision]} tiket #{$ticketId}.",
            'old_values'  => ['status' => $oldStatus, 'kd_approval_status' => 'pending'],
            'new_values'  => ['status' => $newStatus, 'kd_approval_status' => $decision],
        ], $request);
    }

    /**
     * Shortcut: Log penerbitan nomor PO
     */
    public function logPoIssued(int $poId, string $poNumber, object $user, Request $request): void
    {
        $this->log([
            'module'      => 'purchase_order',
            'record_id'   => $poId,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => 'ISSUE_PO_NUMBER',
            'description' => "{$user->name} menerbitkan nomor PO: {$poNumber}.",
            'new_values'  => ['po_number' => $poNumber],
        ], $request);
    }
}
