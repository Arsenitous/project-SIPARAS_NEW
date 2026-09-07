<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt;
    color: #000;
    padding: 20px 30px;
  }

  /* ── KOP SURAT ────────────────────────────────────────────── */
  .kop {
    display: table;
    width: 100%;
    border-bottom: 3px solid #000;
    padding-bottom: 10px;
    margin-bottom: 18px;
  }
  .kop-logo {
    display: table-cell;
    width: 80px;
    vertical-align: middle;
  }
  .kop-logo img {
    width: 70px;
    height: auto;
  }
  .kop-text {
    display: table-cell;
    vertical-align: middle;
    padding-left: 10px;
  }
  .kop-text h1 {
    font-size: 14pt;
    font-weight: bold;
    margin-bottom: 3px;
  }
  .kop-text p {
    font-size: 9pt;
    line-height: 1.4;
    color: #333;
  }

  /* ── JUDUL ────────────────────────────────────────────────── */
  .title-section {
    text-align: center;
    margin: 16px 0 14px;
  }
  .title-section h2 {
    font-size: 14pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  /* ── INFO VENDOR ──────────────────────────────────────────── */
  .vendor-box {
    border: 1px solid #000;
    padding: 8px 10px;
    margin-bottom: 14px;
    font-size: 10pt;
    line-height: 1.7;
  }
  .vendor-box .po-number {
    font-weight: bold;
    font-size: 10.5pt;
  }

  /* ── KALIMAT PEMBUKA ──────────────────────────────────────── */
  .intro {
    font-size: 10.5pt;
    margin-bottom: 10px;
    font-style: italic;
  }

  /* ── TABEL ITEM ───────────────────────────────────────────── */
  .items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 10pt;
  }
  .items-table th {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: center;
    font-weight: bold;
    background: #f0f0f0;
  }
  .items-table td {
    border: 1px solid #000;
    padding: 6px 8px;
    vertical-align: top;
  }
  .items-table td.center { text-align: center; }
  .items-table td.right  { text-align: right; }
  .items-table .empty-row td { height: 22px; }
  .items-table .grand-total td {
    font-weight: bold;
    text-align: right;
    padding: 6px 8px;
  }
  .items-table .grand-total td.label {
    text-align: right;
    border-right: none;
  }

  /* ── TERBILANG & CATATAN ──────────────────────────────────── */
  .terbilang {
    font-size: 10pt;
    font-style: italic;
    margin: 6px 0;
  }
  .notes-section {
    margin: 10px 0 20px;
  }
  .notes-section p {
    font-size: 10pt;
    margin-bottom: 4px;
    font-weight: bold;
  }
  .notes-section ol {
    font-size: 10pt;
    padding-left: 18px;
    line-height: 1.6;
  }

  /* ── TTD SECTION ──────────────────────────────────────────── */
  .signature-section {
    display: table;
    width: 100%;
    margin-top: 24px;
  }
  .sig-col {
    display: table-cell;
    width: 33.33%;
    text-align: left;
    vertical-align: top;
    padding: 0 8px;
  }
  .sig-col.center-col {
    text-align: center;
    padding-top: 60px; /* Push down untuk vendor TTD */
  }
  .sig-col p.sig-label {
    font-size: 10pt;
    margin-bottom: 50px; /* Ruang tanda tangan */
  }
  .sig-col .sig-name {
    font-size: 10pt;
    font-weight: bold;
    border-top: 1px solid #000;
    padding-top: 4px;
    display: inline-block;
    min-width: 120px;
  }
  .sig-col .sig-role {
    font-size: 9.5pt;
    margin-top: 2px;
  }
  .sig-date {
    font-size: 10pt;
    margin-bottom: 4px;
  }
</style>
</head>
<body>

  {{-- ── KOP SURAT ─────────────────────────────────────────────────────── --}}
  <div class="kop">
    <div class="kop-logo">
      @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" alt="Logo YGPKB">
      @endif
    </div>
    <div class="kop-text">
      <h1>Yayasan Gereja Protestan Kampung Bali</h1>
      <p>
        Jl. Sisingamangaraja No. 114 - 118<br>
        Pontianak 78117 | Kalimantan Barat<br>
        Telepon: 0561 737045 | Email: ygpkb.ptk@gmail.com
      </p>
    </div>
  </div>

  {{-- ── JUDUL ────────────────────────────────────────────────────────── --}}
  <div class="title-section">
    <h2>PURCHASING ORDER (PO)</h2>
  </div>

  {{-- ── INFO VENDOR ─────────────────────────────────────────────────── --}}
  <div class="vendor-box">
    <div class="po-number">{{ $po->po_number }}</div>
    <div>Kepada &nbsp;: {{ $po->vendor_name }}</div>
    @if($po->vendor_address)
    <div>Alamat &nbsp;: {{ $po->vendor_address }}</div>
    @endif
    @if($po->vendor_phone)
    <div>Telp. {{ $po->vendor_phone }}</div>
    @endif
  </div>

  {{-- ── KALIMAT PEMBUKA ─────────────────────────────────────────────── --}}
  <p class="intro">Berdasarkan info yang di sampaikan, kami mengorder;</p>

  {{-- ── TABEL ITEM ──────────────────────────────────────────────────── --}}
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:55%">Jenis Barang/Jasa</th>
        <th style="width:15%">Jumlah Order</th>
        <th style="width:13%">Harga Barang/Jasa</th>
        <th style="width:12%">TOTAL</th>
      </tr>
    </thead>
    <tbody>
      @foreach($po->items->sortBy('sort_order') as $i => $item)
      <tr>
        <td class="center">{{ $i + 1 }}</td>
        <td>
          {{ $item->name }}
          @if($item->description)
            <br><small style="color:#444">{{ $item->description }}</small>
          @endif
        </td>
        <td class="center">{{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit_name }}</td>
        <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }},-</td>
        <td class="right">Rp {{ number_format($item->total_price, 0, ',', '.') }},-</td>
      </tr>
      @endforeach

      {{-- Baris kosong padding --}}
      @for($e = 0; $e < max(0, 3 - $po->items->count()); $e++)
      <tr class="empty-row">
        <td></td><td></td><td></td><td></td><td></td>
      </tr>
      @endfor

      {{-- Grand Total --}}
      <tr class="grand-total">
        <td colspan="4" class="label">GRAND TOTAL</td>
        <td class="right">Rp {{ number_format($po->grand_total, 0, ',', '.') }},-</td>
      </tr>
    </tbody>
  </table>

  {{-- ── TERBILANG ───────────────────────────────────────────────────── --}}
  <p class="terbilang">{{ $terbilang }}</p>

  {{-- ── CATATAN ─────────────────────────────────────────────────────── --}}
  @if($po->notes)
  <div class="notes-section">
    <p>Catatan :</p>
    <ol>
      @foreach(explode("\n", trim($po->notes)) as $note)
        @if(trim($note))
          <li>{{ trim($note) }}</li>
        @endif
      @endforeach
    </ol>
  </div>
  @endif

  {{-- ── TANDA TANGAN ───────────────────────────────────────────────── --}}
  <div class="signature-section">
    {{-- Kiri: Disetujui (Koordinator) --}}
    <div class="sig-col">
      <p class="sig-label">Disetujui,</p>
      <div class="sig-name">{{ $settings['signer_left_name'] ?? 'Koordinator' }}</div>
      <div class="sig-role">{{ $settings['signer_left_role'] ?? 'Koordinator UTAMA' }}</div>
    </div>

    {{-- Tengah: Vendor (Tanda Terima PO) - posisi bawah --}}
    <div class="sig-col center-col">
      <p class="sig-label">Tanda Terima PO,</p>
      <div class="sig-name">{{ $po->vendor_name }}</div>
      <div class="sig-role">Penerima</div>
    </div>

    {{-- Kanan: Diorder (KD Sarpras) --}}
    <div class="sig-col" style="text-align:right">
      <p class="sig-label">
        {{ $po->po_city }}, {{ \Carbon\Carbon::parse($po->po_date)->translatedFormat('d F Y') }}<br>
        Diorder,
      </p>
      <div class="sig-name">{{ $settings['signer_right_name'] ?? 'KD Sarpras' }}</div>
      <div class="sig-role">{{ $settings['signer_right_role'] ?? 'KD. SarPras' }}</div>
    </div>
  </div>

</body>
</html>
