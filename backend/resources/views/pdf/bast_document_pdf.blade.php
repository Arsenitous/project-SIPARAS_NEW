<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $bastTitle }} - Tiket #{{ $ticket->id }}</title>
<style>
@page { size: A4 portrait; margin: 15mm; }
body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
table { width: 100%; border-collapse: collapse; }
.header-table td { vertical-align: middle; }
.border-bottom-double { border-bottom: 3px double #000; padding-bottom: 8px; }
.data-table th { background-color: #f2f2f2; border: 1px solid #666; padding: 5px; font-weight: bold; }
.data-table td { border: 1px solid #666; padding: 5px; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.no-break { page-break-inside: avoid; }
.info-box { border: 1px solid #999; padding: 8px; margin-bottom: 12px; background-color: #fafafa; }
.photo-cell { width: 33.33%; text-align: center; padding: 4px; }
.photo-img { width: 100%; max-height: 140px; object-fit: cover; border: 1px solid #ccc; }
</style>
</head>
<body>
<!-- KOP DOKUMEN RASIO 1 : 3 -->
<table class="header-table border-bottom-double">
<tr>
<td style="width: 25%; text-align: center;">
<img src="{{ public_path('images/logo_ygpkb.png') }}" style="width: 85px; height: auto;" alt="Logo YGPKB">
</td>
<td style="width: 75%; text-align: center;">
<h2 style="margin: 0; font-size: 14pt; text-transform: uppercase;">Yayasan Gereja Protestan Kampung Bali</h2>
<h3 style="margin: 2px 0; font-size: 11pt; text-transform: uppercase;">{{ $bastTitle }}</h3>
<p style="margin: 0; font-size: 8pt; color: #555;">Rujukan Tiket Kerja: Tiket #{{ $ticket->id }} | Tanggal: {{ \Carbon\Carbon::parse($bast->handover_date)->translatedFormat('d F Y') }}</p>
</td>
</tr>
</table>
<div style="margin-top: 15px; margin-bottom: 12px;">
<p style="margin: 0;">Pada hari ini, <strong>{{ \Carbon\Carbon::parse($bast->handover_date)->translatedFormat('l, d F Y') }}</strong>, telah dilaksanakan serah terima penyelesaian pekerjaan sarana & prasarana dengan rincian identitas sebagai berikut:</p>
</div>

<!-- BLOK IDENTITAS KEGIATAN -->
<div class="info-box">
<table style="font-size: 8.5pt;">
<tr><td style="width: 25%; font-weight: bold;">Unit Kerja Pemohon:</td><td style="width: 75%;">{{ $ticket->unit->name }}</td></tr>
<tr><td style="font-weight: bold;">Lokasi / Ruangan:</td><td>{{ $ticket->room->name ?? 'Area Non-Permanen / Luar Ruangan' }} ({{ $ticket->specific_location ?? '-' }})</td></tr>
<tr><td style="font-weight: bold;">Pelaksana / Vendor:</td><td>{{ $bast->executor_or_vendor_name ?? '-' }}</td></tr>
@if($bast->actual_cost > 0)
<tr><td style="font-weight: bold;">Realisasi Biaya Riil:</td><td><strong>Rp {{ number_format($bast->actual_cost, 0, ',', '.') }}</strong></td></tr>
@endif
</table>
</div>

<!-- TABEL RINCIAN ITEM (KHUSUS PENGADAAN, MUTASI, & PEMUTIHAN) -->
@if($ticket->items && $ticket->items->count() > 0)
<table class="data-table" style="margin-bottom: 15px;">
<thead>
<tr>
<th style="width: 5%;">No</th>
<th style="width: 20%;">Kode Inventaris</th>
<th style="width: 45%;">Nama & Spesifikasi Barang</th>
<th style="width: 10%;">Jumlah</th>
<th style="width: 20%;">Kondisi / Catatan</th>
</tr>
</thead>
<tbody>
@foreach($ticket->items as $idx => $item)
<tr>
<td class="text-center">{{ $idx + 1 }}</td>
<td class="text-center">{{ $item->inventory_code ?? '-' }}</td>
<td>{{ $item->item_description }}</td>
<td class="text-center">{{ $item->quantity }} {{ $item->unit_metric }}</td>
<td>{{ ucfirst($item->condition_status ?? '-') }} {{ $item->item_notes ? '('.$item->item_notes.')' : '' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

<!-- GRID FOTO DOKUMENTASI FISIK (TERKOMPRESI) -->
@if($ticket->photos && $ticket->photos->count() > 0)
<div class="no-break" style="margin-bottom: 15px;">
<p style="font-weight: bold; margin-bottom: 5px; font-size: 8.5pt;">Lampiran Dokumentasi Fisik Lapangan:</p>
<table>
<tr>
@foreach($ticket->photos->take(3) as $photo)
<td class="photo-cell">
<img src="{{ storage_path('app/public/' . $photo->file_path) }}" class="photo-img">
<p style="margin: 2px 0 0 0; font-size: 7.5pt; color: #555;">{{ ucfirst(str_replace('_', ' ', $photo->photo_type)) }}</p>
</td>
@endforeach
</tr>
</table>
</div>
@endif

<!-- TABEL 3 KOLOM TANDA TANGAN BERITA ACARA -->
<table class="no-break" style="text-align: center; font-size: 8.5pt; margin-top: 20px;">
<tr>
<td style="width: 33%;">
<p style="margin-bottom: 60px;">{{ $signatoryLeftLabel ?? 'Dibuat / Diserahkan Oleh,' }}</p>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $signatoryLeftName ?? '( .................................... )' }}</p>
<p style="margin: 0; color: #555;">{{ $signatoryLeftRole ?? 'Pelaksana / Pengirim' }}</p>
</td>
<td style="width: 34%;">
<p style="margin-bottom: 60px;">{{ $signatoryCenterLabel ?? 'Diketahui / Diterima Oleh,' }}</p>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $signatoryCenterName ?? '( .................................... )' }}</p>
<p style="margin: 0; color: #555;">{{ $signatoryCenterRole ?? 'Kepala Unit Pemohon' }}</p>
</td>
<td style="width: 33%;">
<p style="margin-bottom: 60px;">Mengetahui & Menyetujui,</p>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">Benny</p>
<p style="margin: 0; color: #555;">KD.SarPras YGPKB</p>
</td>
</tr>
</table>
</body>
</html>
