import os
import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

LOGBOOK_ITEMS = [
    {
        "no": 1,
        "hari_tanggal": "1 September 2026",
        "kegiatan": "Membuat Konsep Web SiParas: Environment, Ruang Lingkup dan POV User. Serta Berkoordinasi dengan Pihak Instansi berkaitan dengan konsep web yang diinginkan",
        "catatan": "-"
    },
    {
        "no": 2,
        "hari_tanggal": "2 September 2026",
        "kegiatan": "Melanjutkan Pembuatan Konsep Web SiParas: Spesifikasi/Fitur, Rancangan Basis Data, Arsitektur.",
        "catatan": "-"
    },
    {
        "no": 3,
        "hari_tanggal": "4 September 2026",
        "kegiatan": "Membuat Rekapan Konsep Web Siparas: Gambar Alur Sistem Bekerja dan Storyboard",
        "catatan": "-"
    },
    {
        "no": 4,
        "hari_tanggal": "7 September 2026",
        "kegiatan": "Memulai Tahap Pembuatan Web SiParas: Setup Repositori Monorepo (Frontend & Backend), Instalasi Framework Laravel 11, Konfigurasi Environment (.env), dan Koneksi Basis Data MySQL 8.4 LTS.",
        "catatan": "-"
    },
    {
        "no": 5,
        "hari_tanggal": "8 September 2026",
        "kegiatan": "Pembuatan Database Migration untuk 13 Tabel Utama (Users, Units, Rooms, Vendors, Tickets, Ticket Items, Photos, Preventive Schedules, PO, BAST, Audit Logs), serta Database Seeder untuk 14 Unit dan Akun Uji Coba 7 Role Pengguna.",
        "catatan": "-"
    },
    {
        "no": 6,
        "hari_tanggal": "9 September 2026",
        "kegiatan": "Implementasi Modul Autentikasi REST API menggunakan Laravel Sanctum (Token-Based Auth) dan Pembuatan Middleware Role-Based Access Control (RoleMiddleware) untuk Membatasi Akses dan Mengisolasi Data Antar Unit Kerja.",
        "catatan": "-"
    },
    {
        "no": 7,
        "hari_tanggal": "10 September 2026",
        "kegiatan": "Inisialisasi Frontend Client menggunakan Vue.js 3 (Composition API) dan Vite, Konfigurasi Framework CSS Tailwind CSS, Pengaturan State Management Pinia (Auth, Ticket, Master), serta Axios Interceptor untuk Penanganan Bearer Token.",
        "catatan": "-"
    },
    {
        "no": 8,
        "hari_tanggal": "11 September 2026",
        "kegiatan": "Pembuatan Tata Letak Antarmuka Utama (DashboardLayout dan AppSidebar) dengan Menu Dinamis Berdasarkan Role Pengguna, serta Desain Halaman Login Responsif yang Menyesuaikan Proporsi Layar Desktop dan Mobile.",
        "catatan": "-"
    },
    {
        "no": 9,
        "hari_tanggal": "14 September 2026",
        "kegiatan": "Pengembangan RESTful API Master Data (MasterDataController) untuk Pengelolaan Data Unit, Ruangan Baku, dan Rekanan/Vendor, serta Pembuatan Komponen Dropdown Cascading Lokasi (SmartCascadingLocation) pada Frontend.",
        "catatan": "-"
    },
    {
        "no": 10,
        "hari_tanggal": "15 September 2026",
        "kegiatan": "Pengembangan Modul Pembuatan Tiket Sarpras (TicketCreateView): Form Input Dinamis untuk 5 Jenis Prosedur (Perbaikan, Pemeliharaan, Pengadaan, Mutasi, Pemutihan) dan Sub-komponen Tabel Multi-Item (ItemRepeaterTable).",
        "catatan": "-"
    },
    {
        "no": 11,
        "hari_tanggal": "16 September 2026",
        "kegiatan": "Pembuatan Komponen Kompresi Gambar Otomatis (ImageCompressorUploader) Berbasis HTML5 Canvas untuk Mereduksi Ukuran Foto Bukti Fisik di Sisi Klien, serta Integrasi Akses Kamera Ponsel Langsung untuk Petugas Lapangan.",
        "catatan": "-"
    },
    {
        "no": 12,
        "hari_tanggal": "17 September 2026",
        "kegiatan": "Pengembangan Logika Bisnis Alur Approval Berjenjang (ApprovalRoutingService) dan Pembuatan Halaman Antarmuka Verifikasi Lapangan untuk Petugas ASP Wilayah (AspReviewQueueView).",
        "catatan": "-"
    },
    {
        "no": 13,
        "hari_tanggal": "18 September 2026",
        "kegiatan": "Pembuatan Halaman Meja Persetujuan Kepala Departemen (KdApprovalDeskView) yang Mendukung Aksi One-Click Approve, Reject, dan Revision Required dengan Dialog Catatan Perbaikan.",
        "catatan": "-"
    },
    {
        "no": 14,
        "hari_tanggal": "21 September 2026",
        "kegiatan": "Pengembangan Komponen Linimasa Visual Status Tiket (TimelineTracker), Perekaman Riwayat Audit Otomatis (AuditLogService), dan Halaman Rincian Tiket Terpadu (TicketDetailView).",
        "catatan": "-"
    },
    {
        "no": 15,
        "hari_tanggal": "22 September 2026",
        "kegiatan": "Pengembangan Modul Manajemen Purchase Order (PO): Pembuatan PO Otomatis dari Tiket yang Disetujui, Perhitungan Total Biaya, dan Integrasi Helper Konversi Angka ke Kalimat Terbilang Rupiah Resmi (TerbilangHelper).",
        "catatan": "-"
    },
    {
        "no": 16,
        "hari_tanggal": "23 September 2026",
        "kegiatan": "Implementasi Generator Dokumen PDF Resmi (DomPDF) untuk Cetak Lembar PO dan 5 Format Berita Acara Serah Terima (BAST) Berkop Resmi Yayasan YGPKB, serta Fitur Ekspor Rekapitulasi Tiket ke Format Excel.",
        "catatan": "-"
    },
    {
        "no": 17,
        "hari_tanggal": "24 September 2026",
        "kegiatan": "Pengembangan Modul Kalender Pemeliharaan Rutin / Servis Preventif (PreventiveSchedule) untuk Memantau Jadwal Servis Berkala Aset Fasilitas (AC, Genset, Pompa Air) di 14 Unit Persekolahan dan Kampus.",
        "catatan": "-"
    },
    {
        "no": 18,
        "hari_tanggal": "25 September 2026",
        "kegiatan": "Pembuatan Antarmuka Laporan Eksekutif Sarpras (ExecutiveReportView), Manajemen Pengguna (UserManagementView), serta Komponen Interaktif Dialog Konfirmasi (ConfirmModal) dan Notifikasi Toast (ToastNotification).",
        "catatan": "-"
    },
    {
        "no": 19,
        "hari_tanggal": "28 September 2026",
        "kegiatan": "Pengujian Fungsional Sistem Secara Menyeluruh menggunakan Metode Blackbox Testing untuk 7 Peran Pengguna, Validasi Hak Akses Isolasi Data Unit, Pengujian Form Validation, dan Pengujian Integritas Alur Dokumen PO/BAST.",
        "catatan": "-"
    },
    {
        "no": 20,
        "hari_tanggal": "29 September 2026",
        "kegiatan": "Pengujian Responsivitas Antarmuka pada Berbagai Resolusi Perangkat Mobile/Tablet (Mobile Drawer Sidebar), Pengetesan Fitur Unggah Foto Kamera Lapangan, serta Perbaikan Bug UI/UX (Bug Fixing) dan Optimasi Query Backend.",
        "catatan": "-"
    },
    {
        "no": 21,
        "hari_tanggal": "30 September 2026",
        "kegiatan": "Konfigurasi Build Production dan Deployment Frontend ke Platform Cloud Vercel (https://siparas-frontend.vercel.app), Evaluasi Akhir Bersama Pembimbing Lapangan / Pihak Instansi YGPKB, dan Penyusunan Berita Acara Serah Terima Sistem.",
        "catatan": "-"
    }
]


def set_cell_margins(cell, top=120, bottom=120, left=140, right=140):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)


def create_docx(filepath):
    doc = docx.Document()
    
    # Page setup: Standard margins (Top/Bottom 2.54cm, Left/Right 2.54cm)
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    # Title matching university/KP style
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_title = p_title.add_run("LOGBOOK KEGIATAN HARIAN KERJA PRAKTEK")
    r_title.bold = True
    r_title.font.name = "Times New Roman"
    r_title.font.size = Pt(14)
    p_title.paragraph_format.space_after = Pt(2)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_sub = p_sub.add_run("PENGEMBANGAN SISTEM INFORMASI SARANA & PRASARANA (SIPARAS)\nYAYASAN GEREJA PROTESTAN KAMPUNG BALI (YGPKB)")
    r_sub.bold = True
    r_sub.font.name = "Times New Roman"
    r_sub.font.size = Pt(12)
    p_sub.paragraph_format.space_after = Pt(16)

    # Identitas Mahasiswa Singkat
    p_id = doc.add_paragraph()
    p_id.paragraph_format.space_after = Pt(10)
    r_id = p_id.add_run("Bulan / Periode : September 2026\nInstansi Mitra   : Yayasan Gereja Protestan Kampung Bali (YGPKB) - Sarpras")
    r_id.font.name = "Times New Roman"
    r_id.font.size = Pt(11)

    # Table with 4 columns exactly like screenshot
    tbl = doc.add_table(rows=len(LOGBOOK_ITEMS) + 1, cols=4)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    # Border XML: simple crisp black border exactly like screenshot
    tblPr = tbl._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>'
        f'<w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
        f'<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
        f'<w:left w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
        f'<w:right w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
        f'<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        f'<w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)

    col_widths = [Inches(0.6), Inches(1.8), Inches(3.6), Inches(0.8)]
    headers = ["No", "Hari/Tanggal", "Kegiatan", "Catatan"]

    # Header Row
    hdr_row = tbl.rows[0]
    for idx, text in enumerate(headers):
        cell = hdr_row.cells[idx]
        cell.width = col_widths[idx]
        set_cell_margins(cell, top=140, bottom=140, left=120, right=120)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        run = p.add_run(text)
        run.bold = True
        run.font.name = "Times New Roman"
        run.font.size = Pt(12)

    # Data Rows
    for r_idx, item in enumerate(LOGBOOK_ITEMS, start=1):
        row = tbl.rows[r_idx]
        
        # Col 0: No
        c0 = row.cells[0]
        c0.width = col_widths[0]
        set_cell_margins(c0, top=100, bottom=100, left=100, right=100)
        p0 = c0.paragraphs[0]
        p0.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r0 = p0.add_run(str(item["no"]))
        r0.font.name = "Times New Roman"
        r0.font.size = Pt(11)

        # Col 1: Hari/Tanggal
        c1 = row.cells[1]
        c1.width = col_widths[1]
        set_cell_margins(c1, top=100, bottom=100, left=100, right=100)
        p1 = c1.paragraphs[0]
        p1.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r1 = p1.add_run(item["hari_tanggal"])
        r1.font.name = "Times New Roman"
        r1.font.size = Pt(11)

        # Col 2: Kegiatan
        c2 = row.cells[2]
        c2.width = col_widths[2]
        set_cell_margins(c2, top=100, bottom=100, left=120, right=120)
        p2 = c2.paragraphs[0]
        p2.alignment = WD_ALIGN_PARAGRAPH.LEFT
        r2 = p2.add_run(item["kegiatan"])
        r2.font.name = "Times New Roman"
        r2.font.size = Pt(11)

        # Col 3: Catatan
        c3 = row.cells[3]
        c3.width = col_widths[3]
        set_cell_margins(c3, top=100, bottom=100, left=100, right=100)
        p3 = c3.paragraphs[0]
        p3.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r3 = p3.add_run(item["catatan"])
        r3.font.name = "Times New Roman"
        r3.font.size = Pt(11)

    # Tanda Tangan Pengesahan di Bawah Tabel
    doc.add_paragraph() # Spacing
    p_sign = doc.add_paragraph()
    p_sign.paragraph_format.space_before = Pt(12)
    p_sign.paragraph_format.space_after = Pt(6)

    tbl_ttd = doc.add_table(rows=1, cols=2)
    tbl_ttd.alignment = WD_TABLE_ALIGNMENT.CENTER
    col_w = Inches(3.2)
    tbl_ttd.rows[0].cells[0].width = col_w
    tbl_ttd.rows[0].cells[1].width = col_w

    p_mhs = tbl_ttd.rows[0].cells[0].paragraphs[0]
    p_mhs.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_mhs = p_mhs.add_run("Mengetahui,\nPembimbing Lapangan (YGPKB)\n\n\n\n\n( .................................................... )\nKD Sarpras YGPKB")
    r_mhs.font.name = "Times New Roman"
    r_mhs.font.size = Pt(11)

    p_lap = tbl_ttd.rows[0].cells[1].paragraphs[0]
    p_lap.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_lap = p_lap.add_run("Pontianak, 30 September 2026\nMahasiswa Pelaksana\n\n\n\n\n( .................................................... )\nNIM: .......................................")
    r_lap.font.name = "Times New Roman"
    r_lap.font.size = Pt(11)

    doc.save(filepath)
    print(f"Created Word logbook: {filepath}")


def create_xlsx(filepath):
    wb = openpyxl.Workbook()
    ws = wb.active
    ws.title = "Logbook September 2026"
    ws.views.sheetView[0].showGridLines = True

    # Styling
    font_title = Font(name="Times New Roman", size=14, bold=True)
    font_sub = Font(name="Times New Roman", size=11, bold=True)
    font_header = Font(name="Times New Roman", size=12, bold=True)
    font_data = Font(name="Times New Roman", size=11)
    font_bold = Font(name="Times New Roman", size=11, bold=True)

    border_all = Border(
        left=Side(style='thin', color="000000"),
        right=Side(style='thin', color="000000"),
        top=Side(style='thin', color="000000"),
        bottom=Side(style='thin', color="000000")
    )
    fill_header = PatternFill(start_color="F2F2F2", end_color="F2F2F2", fill_type="solid")

    ws.merge_cells("A1:D1")
    ws["A1"] = "LOGBOOK KEGIATAN HARIAN KERJA PRAKTEK"
    ws["A1"].font = font_title
    ws["A1"].alignment = Alignment(horizontal="center", vertical="center")
    ws.row_dimensions[1].height = 24

    ws.merge_cells("A2:D2")
    ws["A2"] = "SISTEM INFORMASI SARANA & PRASARANA (SIPARAS) - YGPKB"
    ws["A2"].font = font_sub
    ws["A2"].alignment = Alignment(horizontal="center", vertical="center")
    ws.row_dimensions[2].height = 20

    ws["A3"] = "Bulan: September 2026"
    ws["A3"].font = font_sub
    ws.row_dimensions[3].height = 18

    # Headers
    headers = ["No", "Hari/Tanggal", "Kegiatan", "Catatan"]
    row_hdr = 5
    ws.row_dimensions[row_hdr].height = 28

    for c_idx, h in enumerate(headers, start=1):
        cell = ws.cell(row=row_hdr, column=c_idx, value=h)
        cell.font = font_header
        cell.fill = fill_header
        cell.alignment = Alignment(horizontal="center", vertical="center")
        cell.border = border_all

    for idx, item in enumerate(LOGBOOK_ITEMS, start=6):
        ws.row_dimensions[idx].height = 45

        c1 = ws.cell(row=idx, column=1, value=item["no"])
        c1.alignment = Alignment(horizontal="center", vertical="center")
        c1.font = font_data
        c1.border = border_all

        c2 = ws.cell(row=idx, column=2, value=item["hari_tanggal"])
        c2.alignment = Alignment(horizontal="center", vertical="center")
        c2.font = font_data
        c2.border = border_all

        c3 = ws.cell(row=idx, column=3, value=item["kegiatan"])
        c3.alignment = Alignment(horizontal="left", vertical="center", wrap_text=True)
        c3.font = font_data
        c3.border = border_all

        c4 = ws.cell(row=idx, column=4, value=item["catatan"])
        c4.alignment = Alignment(horizontal="center", vertical="center")
        c4.font = font_data
        c4.border = border_all

    # Column widths
    ws.column_dimensions["A"].width = 8
    ws.column_dimensions["B"].width = 22
    ws.column_dimensions["C"].width = 75
    ws.column_dimensions["D"].width = 14

    wb.save(filepath)
    print(f"Created Excel logbook: {filepath}")


if __name__ == "__main__":
    doc_path = os.path.abspath("Logbook_Pengerjaan_SIPARAS.docx")
    xls_path = os.path.abspath("Logbook_Pengerjaan_SIPARAS.xlsx")
    create_docx(doc_path)
    create_xlsx(xls_path)
    print("All files updated to exact screenshot template!")
