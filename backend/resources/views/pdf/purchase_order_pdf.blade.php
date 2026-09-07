<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Purchase Order - {{ $po->po_number }}</title>
<style>
@page { size: A4 portrait; margin: 15mm; }
body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
table { width: 100%; border-collapse: collapse; }
.header-table td { vertical-align: middle; }
.border-bottom-double { border-bottom: 3px double #000; padding-bottom: 8px; }
.data-table th { background-color: #f2f2f2; border: 1px solid #666; padding: 6px; font-weight: bold; text-align: left; }
.data-table td { border: 1px solid #666; padding: 5px 6px; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-bold { font-weight: bold; }
.no-break { page-break-inside: avoid; }
.terbilang-box { background-color: #f9f9f9; border: 1px dashed #888; padding: 6px 10px; font-style: italic; }
</style>
</head>
<body>
<!-- 1. KOP SURAT RESMI (Rasio 1 : 3) -->
<table class="header-table border-bottom-double">
<tr>
<td style="width: 25%; text-align: center;">
<!-- Note: If logo doesn't exist yet, we will just fallback or assume it exists in public/images/logo_ygpkb.png -->
<img src="{{ public_path('images/logo_ygpkb.png') }}" style="width: 90px; height: auto;" alt="Logo YGPKB">
</td>
<td style="width: 75%; text-align: center;">
<h2 style="margin: 0; font-size: 15pt; text-transform: uppercase;">Yayasan Gereja Protestan Kampung Bali</h2>
<h3 style="margin: 2px 0; font-size: 12pt;">DEPARTEMEN SARANA & PRASARANA</h3>
<p style="margin: 0; font-size: 8pt; color: #555;">Jl. WR. Supratman No. 12, Pontianak - Kalimantan Barat | Telp: (0561) 732xxx</p>
</td>
</tr>
</table>
<div style="text-align: center; margin-top: 12px; margin-bottom: 15px;">
<h3 style="margin: 0; font-size: 13pt; text-decoration: underline;">PURCHASE ORDER (PO)</h3>
<p style="margin: 2px 0; font-size: 10pt; font-weight: bold;">Nomor: {{ $po->po_number }}</p>
</div>

<!-- 2. IDENTITAS TRANSAKSI & SNAPSHOT REKANAN -->
<table style="margin-bottom: 15px;">
<tr>
<td style="width: 55%; vertical-align: top;">
<table style="font-size: 8.5pt;">
<tr><td style="width: 30%; font-weight: bold;">Kepada Vendor:</td><td>{{ $po->vendor_name_snapshot }}</td></tr>
<tr><td style="font-weight: bold;">Up / Kontak:</td><td>{{ $po->vendor_pic_snapshot }} ({{ $po->vendor_phone_snapshot }})</td></tr>
<tr><td style="font-weight: bold;">Alamat:</td><td>{{ $po->vendor_address_snapshot }}</td></tr>
</table>
</td>
<td style="width: 45%; vertical-align: top;">
<table style="font-size: 8.5pt;">
<tr><td style="width: 40%; font-weight: bold;">Tanggal PO:</td><td>{{ \Carbon\Carbon::parse($po->created_at)->translatedFormat('d F Y') }}</td></tr>
<tr><td style="font-weight: bold;">Unit Pemesan:</td><td>{{ $po->unit->name }}</td></tr>
<tr><td style="font-weight: bold;">ID Tiket Sarpras:</td><td>Tiket #{{ $po->ticket_id ?? '-' }}</td></tr>
</table>
</td>
</tr>
</table>

<!-- 3. TABEL RINCIAN ITEM PEMESANAN -->
<table class="data-table" style="margin-bottom: 12px;">
<thead>
<tr>
<th style="width: 5%; text-align: center;">No</th>
<th style="width: 45%;">Nama Barang / Jasa & Spesifikasi</th>
<th style="width: 10%; text-align: center;">Qty</th>
<th style="width: 10%; text-align: center;">Satuan</th>
<th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
<th style="width: 15%; text-align: right;">Subtotal (Rp)</th>
</tr>
</thead>
<tbody>
@foreach($po->items as $index => $item)
<tr>
<td class="text-center">{{ $index + 1 }}</td>
<td>{{ $item->item_description }}</td>
<td class="text-center">{{ $item->order_quantity }}</td>
<td class="text-center">{{ $item->unit_metric }}</td>
<td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
<td class="text-right">{{ number_format($item->item_subtotal, 0, ',', '.') }}</td>
</tr>
@endforeach
<tr>
<td colspan="5" class="text-right text-bold">SUBTOTAL</td>
<td class="text-right text-bold">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td>
</tr>
@if($po->discount_amount > 0)
<tr>
<td colspan="5" class="text-right text-bold" style="color: #c00;">POTONGAN DISKON</td>
<td class="text-right text-bold" style="color: #c00;">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</td>
</tr>
@endif
<tr style="background-color: #f2f2f2;">
<td colspan="5" class="text-right text-bold" style="font-size: 10pt;">GRAND TOTAL</td>
<td class="text-right text-bold" style="font-size: 10pt;">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
</tr>
</tbody>
</table>

<!-- 4. TERBILANG BOX -->
<div class="terbilang-box" style="margin-bottom: 25px;">
<strong>Terbilang:</strong> <em># {{ $po->terbilang_text }} Rupiah #</em>
</div>

<!-- 5. KOTAK TANDA TANGAN BASAH FISIK 3 KOLOM -->
<table class="no-break" style="text-align: center; font-size: 8.5pt;">
<tr>
<td style="width: 33%;">
<p style="margin-bottom: 60px;">Disetujui,</p>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">Christiana Kwee</p>
<p style="margin: 0; color: #555;">Koordinator UTAMA</p>
</td>
<td style="width: 34%;">
<p style="margin-bottom: 60px;">Tanda Terima PO,</p>
<p style="margin: 0; font-weight: bold;">( .................................... )</p>
<p style="margin: 0; color: #555;">Penerima / Rekanan</p>
</td>
<td style="width: 33%;">
<p style="margin-bottom: 60px;">Pontianak, {{ \Carbon\Carbon::parse($po->created_at)->translatedFormat('d F Y') }}<br>Diorder,</p>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">Benny</p>
<p style="margin: 0; color: #555;">KD.SarPras</p>
</td>
</tr>
</table>
</body>
</html>
