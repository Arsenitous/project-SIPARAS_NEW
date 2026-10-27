<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} — SIPARAS YGPKB</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; background: #fff; padding: 30px; }

  /* HEADER */
  .doc-header { display: flex; align-items: center; border-bottom: 3px solid #1a3a6b; padding-bottom: 14px; margin-bottom: 18px; }
  .doc-logo { width: 54px; height: 54px; }
  .doc-logo-placeholder { width: 54px; height: 54px; background: #1a3a6b; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 10px; letter-spacing: 1px; text-align: center; line-height: 1.3; }
  .doc-title-block { flex: 1; padding-left: 14px; }
  .doc-org { font-size: 13px; font-weight: 700; color: #1a3a6b; letter-spacing: 0.5px; }
  .doc-subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
  .doc-doctype { text-align: right; }
  .doc-doctype-title { font-size: 16px; font-weight: 900; color: #1a3a6b; letter-spacing: 1px; text-transform: uppercase; }
  .doc-doctype-num { font-size: 12px; font-weight: 700; color: #c8a84b; margin-top: 3px; }

  /* STATUS BADGE */
  .status-row { display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; }
  .status-badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; }
  .status-approved   { background: #dcfce7; color: #15803d; }
  .status-rejected   { background: #fef2f2; color: #dc2626; }
  .status-pending    { background: #fffbeb; color: #d97706; }
  .status-inprogress { background: #eff6ff; color: #2563eb; }
  .urgency-high   { background: #fef2f2; color: #dc2626; }
  .urgency-medium { background: #fffbeb; color: #d97706; }
  .urgency-low    { background: #eff6ff; color: #2563eb; }

  /* TITLE */
  .ticket-title { font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
  .ticket-routing { font-size: 10px; color: #64748b; margin-bottom: 16px; }

  /* INFO GRID */
  .info-grid { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .info-row { display: table-row; }
  .info-label { display: table-cell; width: 160px; padding: 5px 8px; font-weight: 700; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
  .info-value { display: table-cell; padding: 5px 8px; color: #1e293b; font-size: 11px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }

  /* ITEMS TABLE */
  .section-title { font-size: 11px; font-weight: 700; color: #1a3a6b; text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #e2e8f0; }
  .items-table { width: 100%; border-collapse: collapse; font-size: 10px; }
  .items-table thead tr { background: #1a3a6b; color: #fff; }
  .items-table thead th { padding: 6px 8px; text-align: left; font-weight: 700; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
  .items-table tbody tr:nth-child(even) { background: #f8fafc; }
  .items-table td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
  .items-table tfoot tr { background: #f1f5f9; font-weight: 700; }
  .items-table tfoot td { padding: 7px 8px; border-top: 2px solid #1a3a6b; }
  .text-right { text-align: right; }

  /* APPROVAL SECTION */
  .approval-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin: 16px 0; }
  .approval-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 10px; letter-spacing: 0.5px; }
  .approval-grid { display: table; width: 100%; }
  .approval-step { display: table-cell; width: 50%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; vertical-align: top; margin: 4px; }
  .approval-step-name { font-size: 10px; font-weight: 700; color: #1a3a6b; }
  .approval-step-status { font-size: 10px; margin-top: 2px; }
  .approval-step-date { font-size: 9px; color: #94a3b8; margin-top: 2px; }
  .approval-step-notes { font-size: 9px; color: #475569; font-style: italic; margin-top: 3px; background: rgba(0,0,0,0.03); padding: 3px 4px; border-radius: 3px; }

  /* SIGNATURE BLOCK */
  .signature-section { display: table; width: 100%; margin-top: 32px; }
  .sig-col { display: table-cell; width: 33%; text-align: center; padding: 0 10px; vertical-align: top; }
  .sig-label { font-size: 10px; color: #475569; font-weight: 600; margin-bottom: 40px; }
  .sig-line { border-top: 1.5px solid #1a3a6b; padding-top: 4px; }
  .sig-name { font-size: 10px; font-weight: 700; color: #0f172a; }
  .sig-role { font-size: 9px; color: #64748b; margin-top: 2px; }

  /* FOOTER */
  .doc-footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="doc-header">
  <div class="doc-logo-placeholder">YGPKB</div>
  <div class="doc-title-block">
    <div class="doc-org">Yayasan Gereja Protestan Kampung Bali (YGPKB)</div>
    <div class="doc-subtitle">Sistem Informasi Prasarana & Sarana · Divisi Sarpras</div>
  </div>
  <div class="doc-doctype">
    <div class="doc-doctype-title">Tiket {{ strtoupper($ticket->procedure_type) }}</div>
    <div class="doc-doctype-num">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
  </div>
</div>

<!-- STATUS ROW -->
<div class="status-row">
  <span class="status-badge
    @if(in_array($ticket->status, ['approved','completed','signed_archived'])) status-approved
    @elseif($ticket->status === 'rejected') status-rejected
    @elseif($ticket->status === 'in_progress') status-inprogress
    @else status-pending @endif
  ">{{ strtoupper(str_replace('_',' ',$ticket->status)) }}</span>
  <span class="status-badge
    @if($ticket->urgency_level === 'tinggi_darurat') urgency-high
    @elseif($ticket->urgency_level === 'sedang') urgency-medium
    @else urgency-low @endif
  ">URGENSI: {{ strtoupper(str_replace('_',' ',$ticket->urgency_level)) }}</span>
  <span style="font-size:10px;color:#64748b;margin-left:auto;">Dicetak: {{ now()->format('d M Y, H:i') }} WIB</span>
</div>

<!-- TICKET TITLE -->
<div class="ticket-title">{{ $ticket->title }}</div>
<div class="ticket-routing">Jalur Routing: {{ str_replace('_', ' ', $ticket->routing_path ?? '—') }}</div>

<!-- INFO GRID -->
<div class="info-grid">
  <div class="info-row">
    <div class="info-label">Pemohon</div>
    <div class="info-value">{{ $ticket->user?->name ?? '—' }} ({{ str_replace('_',' ',$ticket->user?->role) }})</div>
  </div>
  <div class="info-row">
    <div class="info-label">Unit / Sekolah</div>
    <div class="info-value">{{ $ticket->unit?->name ?? '—' }}</div>
  </div>
  <div class="info-row">
    <div class="info-label">Ruangan</div>
    <div class="info-value">{{ $ticket->room?->name ?? '—' }} @if($ticket->specific_location)({{ $ticket->specific_location }})@endif</div>
  </div>
  <div class="info-row">
    <div class="info-label">Tanggal Ajukan</div>
    <div class="info-value">{{ $ticket->created_at?->format('d M Y, H:i') }} WIB</div>
  </div>
  @if($ticket->problem_cause)
  <div class="info-row">
    <div class="info-label">Uraian / Justifikasi</div>
    <div class="info-value">{{ $ticket->problem_cause }}</div>
  </div>
  @endif
  @if($ticket->procedure_type === 'perbaikan' || $ticket->action_plan)
  <div class="info-row">
    <div class="info-label">Rencana Pengerjaan</div>
    <div class="info-value">{{ $ticket->action_plan ?: '-' }}</div>
  </div>
  @endif
  @if($ticket->estimated_cost > 0)
  <div class="info-row">
    <div class="info-label">Estimasi Biaya</div>
    <div class="info-value" style="font-weight:700;color:#1a3a6b;">Rp {{ number_format($ticket->estimated_cost, 0, ',', '.') }}</div>
  </div>
  @endif
</div>

<!-- ITEMS TABLE (Pengadaan / Mutasi) -->
@if($ticket->items && count($ticket->items) > 0)
<div class="section-title">Daftar Item</div>
<table class="items-table">
  <thead>
    <tr>
      <th>#</th>
      @if($ticket->procedure_type === 'mutasi') <th>Kode Aset</th> @endif
      <th>Nama Barang</th>
      <th>Jml</th>
      <th>Satuan</th>
      @if($ticket->procedure_type === 'pengadaan') <th class="text-right">Harga Satuan</th><th class="text-right">Subtotal</th> @endif
      @if($ticket->procedure_type === 'mutasi') <th>Kondisi</th> @endif
    </tr>
  </thead>
  <tbody>
    @foreach($ticket->items as $i => $item)
    <tr>
      <td>{{ $i + 1 }}</td>
      @if($ticket->procedure_type === 'mutasi') <td>{{ $item->inventory_code ?? '—' }}</td> @endif
      <td>{{ $item->item_description }}</td>
      <td>{{ $item->quantity }}</td>
      <td>{{ $item->unit_metric }}</td>
      @if($ticket->procedure_type === 'pengadaan')
      <td class="text-right">Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
      <td class="text-right">Rp {{ number_format($item->estimated_subtotal, 0, ',', '.') }}</td>
      @endif
      @if($ticket->procedure_type === 'mutasi') <td>{{ ucfirst(str_replace('_',' ',$item->condition_status)) }}</td> @endif
    </tr>
    @endforeach
  </tbody>
  @if($ticket->procedure_type === 'pengadaan')
  <tfoot>
    <tr>
      <td colspan="5">Total Estimasi Biaya</td>
      <td class="text-right" style="font-weight:900;color:#1a3a6b;">Rp {{ number_format($ticket->estimated_cost, 0, ',', '.') }}</td>
    </tr>
  </tfoot>
  @endif
</table>
@endif

<!-- APPROVAL STATUS -->
<div class="approval-section">
  <div class="approval-title">Status Persetujuan</div>
  <table class="approval-grid">
    <tr>
      <td class="approval-step" style="margin-right:8px;">
        <div class="approval-step-name">Review ASP Wilayah</div>
        <div class="approval-step-status">{{ ucfirst(str_replace('_',' ',$ticket->asp_approval_status ?? 'pending')) }}</div>
        @if($ticket->asp_approval_at)
        <div class="approval-step-date">{{ $ticket->asp_approval_at->format('d M Y, H:i') }}</div>
        @endif
        @if($ticket->asp_notes)
        <div class="approval-step-notes">"{{ $ticket->asp_notes }}"</div>
        @endif
      </td>
      <td class="approval-step" style="margin-left:8px;">
        <div class="approval-step-name">Approval KD Sarpras</div>
        <div class="approval-step-status">{{ ucfirst(str_replace('_',' ',$ticket->kd_approval_status ?? 'pending')) }}</div>
        @if($ticket->kd_approval_at)
        <div class="approval-step-date">{{ $ticket->kd_approval_at->format('d M Y, H:i') }}</div>
        @endif
        @if($ticket->kd_notes)
        <div class="approval-step-notes">"{{ $ticket->kd_notes }}"</div>
        @endif
      </td>
    </tr>
  </table>
</div>

<!-- SIGNATURE BLOCK -->
<div class="signature-section">
  <div class="sig-col">
    <div class="sig-label">Pemohon</div>
    <div class="sig-line">
      <div class="sig-name">{{ $ticket->user?->name ?? '—' }}</div>
      <div class="sig-role">{{ str_replace('_',' ',$ticket->user?->role) }}</div>
    </div>
  </div>
  <div class="sig-col">
    <div class="sig-label">Diketahui ASP Wilayah</div>
    <div class="sig-line">
      <div class="sig-name">{{ $ticket->aspApprovalBy?->name ?? '________________' }}</div>
      <div class="sig-role">ASP Wilayah</div>
    </div>
  </div>
  <div class="sig-col">
    <div class="sig-label">Disetujui KD Sarpras</div>
    <div class="sig-line">
      <div class="sig-name">{{ $ticket->kdApprovalBy?->name ?? '________________' }}</div>
      <div class="sig-role">Kepala Divisi Sarpras</div>
    </div>
  </div>
</div>

<div class="doc-footer">
  Dokumen ini diterbitkan secara digital oleh Sistem SIPARAS · Yayasan Gereja Protestan Kampung Bali (YGPKB) · Dicetak {{ now()->format('d M Y, H:i') }} WIB
</div>

</body>
</html>
