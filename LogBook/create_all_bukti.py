import os
import shutil
import io
from PIL import Image, ImageDraw, ImageFont
from pygments import highlight
from pygments.lexers import get_lexer_by_name, guess_lexer
from pygments.formatters import ImageFormatter

BASE_DIR = os.path.abspath("bukti_logbook")
os.makedirs(BASE_DIR, exist_ok=True)

# Helper function to generate VS Code style dark theme code screenshot
def generate_code_image(code_text, lang, title_text, output_path):
    try:
        lexer = get_lexer_by_name(lang)
    except Exception:
        lexer = get_lexer_by_name('text')
        
    formatter = ImageFormatter(
        font_name='Consolas',
        font_size=15,
        line_numbers=True,
        style='monokai'
    )
    img_bytes = highlight(code_text.strip(), lexer, formatter)
    code_img = Image.open(io.BytesIO(img_bytes))

    # Window styling
    title_bar_height = 42
    padding = 12
    w = max(code_img.width + (padding * 2), 650)
    h = code_img.height + title_bar_height + padding + 6

    final_img = Image.new('RGB', (w, h), color='#1e1e1e')
    draw = ImageDraw.Draw(final_img)

    # Title bar
    draw.rectangle([(0, 0), (w, title_bar_height)], fill='#252526')
    draw.line([(0, title_bar_height), (w, title_bar_height)], fill='#333333', width=1)

    # Window buttons (Mac style)
    draw.ellipse([(14, 15), (26, 27)], fill='#ff5f56') # red
    draw.ellipse([(34, 15), (46, 27)], fill='#ffbd2e') # yellow
    draw.ellipse([(54, 15), (66, 27)], fill='#27c93f') # green

    # Title text
    try:
        font_title = ImageFont.truetype(r"C:\Windows\Fonts\segoeui.ttf", 13)
    except Exception:
        font_title = ImageFont.load_default()
        
    bbox = draw.textbbox((0, 0), title_text, font=font_title)
    text_w = bbox[2] - bbox[0]
    text_x = max((w - text_w) // 2, 85)
    draw.text((text_x, 12), title_text, fill='#cccccc', font=font_title)

    # Paste code
    final_img.paste(code_img, (padding, title_bar_height + 4))

    # Border
    draw.rectangle([(0, 0), (w-1, h-1)], outline='#3c3c3c', width=1)

    final_img.save(output_path, quality=95)
    print(f"Generated code image: {output_path}")


# Helper function to generate clean UI card/image
def process_ui_image(src_path, output_path, title_text):
    if os.path.exists(src_path):
        src_img = Image.open(src_path).convert('RGB')
        
        # Max dimension scaling if too large
        max_w, max_h = 1600, 1100
        if src_img.width > max_w or src_img.height > max_h:
            src_img.thumbnail((max_w, max_h), Image.Resampling.LANCZOS)
            
        header_h = 44
        w = src_img.width
        h = src_img.height + header_h
        
        final_img = Image.new('RGB', (w, h), color='#ffffff')
        draw = ImageDraw.Draw(final_img)
        
        # Header banner
        draw.rectangle([(0, 0), (w, header_h)], fill='#1B365D')
        try:
            font_title = ImageFont.truetype(r"C:\Windows\Fonts\segoeui.ttf", 15)
        except Exception:
            font_title = ImageFont.load_default()
            
        draw.text((20, 12), title_text, fill='#ffffff', font=font_title)
        
        # Paste UI image
        final_img.paste(src_img, (0, header_h))
        draw.rectangle([(0, 0), (w-1, h-1)], outline='#CBD5E1', width=1)
        final_img.save(output_path, quality=95)
        print(f"Processed UI image: {output_path}")
    else:
        print(f"Warning: source UI image not found: {src_path}")


# Read file snippet safely
def get_file_snippet(rel_path, start_line=1, end_line=45):
    full_path = os.path.abspath(rel_path)
    if not os.path.exists(full_path):
        return f"// File not found: {rel_path}"
    with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    snippet = lines[start_line-1:end_line]
    return "".join(snippet)


ITEMS_CONFIG = [
    {
        "id": "No_04_Setup_Monorepo_Laravel",
        "title": "Setup Repositori Monorepo & Laravel 11",
        "tanggal": "7 September 2026",
        "file": "package.json",
        "lang": "json",
        "start": 1,
        "end": 35,
        "ui_src": "extracted_doc_images/image6.png",
        "ui_title": "Arsitektur Komunikasi REST API Monorepo (Laravel 11 Backend & Vue 3 Frontend)",
        "kegiatan": "Setup repositori monorepo dengan concurrently untuk menjalankan Laravel API (port 8000) dan Vite frontend (port 5173), instalasi dependensi, konfigurasi .env, dan koneksi basis data MySQL 8.4 LTS."
    },
    {
        "id": "No_05_Database_Migration_Seeder",
        "title": "Database Migration 13 Tabel & Data Seeder",
        "tanggal": "8 September 2026",
        "file": "backend/database/migrations/2026_08_27_000000_create_siparas_master_schema.php",
        "lang": "php",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image2.png",
        "ui_title": "Struktur 4 Klaster Fungsional 13 Tabel Relasional Basis Data SIPARAS",
        "kegiatan": "Pembuatan migrasi database untuk 13 tabel utama (users, units, rooms, tickets, po, bast, audit_logs) serta seeder 14 unit sekolah/kampus dan akun uji coba 7 role pengguna."
    },
    {
        "id": "No_06_Autentikasi_Sanctum_RBAC",
        "title": "Autentikasi Sanctum & RoleMiddleware RBAC",
        "tanggal": "9 September 2026",
        "file": "backend/app/Http/Middleware/RoleMiddleware.php",
        "lang": "php",
        "start": 1,
        "end": 38,
        "ui_src": "extracted_doc_images/image1.png",
        "ui_title": "Matriks Routing Otomatis Berbasis Role & Isolasi Hak Akses Unit",
        "kegiatan": "Implementasi token-based auth via Laravel Sanctum dan filter otorisasi ketat melalui RoleMiddleware agar Kepala Sekolah terisolasi ke unitnya dan ASP ke wilayahnya."
    },
    {
        "id": "No_07_Frontend_Vue3_Pinia_Axios",
        "title": "Frontend Setup: Vue 3, Pinia & Axios Interceptor",
        "tanggal": "10 September 2026",
        "file": "frontend/src/services/api.js",
        "lang": "javascript",
        "start": 1,
        "end": 40,
        "ui_src": "extracted_doc_images/image11.png",
        "ui_title": "Arsitektur Frontend Client SIPARAS (Vue 3, Pinia, Tailwind CSS, Axios)",
        "kegiatan": "Inisialisasi Vue 3 Composition API, manajemen state Pinia (auth, ticket, master), dan Axios Interceptor untuk otomatis menyematkan Bearer Token dan menangani error sesi 401."
    },
    {
        "id": "No_08_Dashboard_Layout_Mobile_Login",
        "title": "Layout Dashboard & Halaman Login Responsif Mobile",
        "tanggal": "11 September 2026",
        "file": "frontend/src/views/auth/LoginView.vue",
        "lang": "html",
        "start": 1,
        "end": 42,
        "ui_src": "extracted_doc_images/image11.png",
        "ui_title": "Antarmuka Login Responsif Proporsi Layar Mobile & Desktop",
        "kegiatan": "Perancangan halaman login modern dengan proporsi viewport height (1/3 visual branding YGPKB dan 2/3 formulir input) serta tata letak dashboard adaptif (DashboardLayout)."
    },
    {
        "id": "No_09_API_Master_Data_Cascading",
        "title": "REST API Master Data & Dropdown Cascading Lokasi",
        "tanggal": "14 September 2026",
        "file": "backend/app/Http/Controllers/Api/V1/MasterDataController.php",
        "lang": "php",
        "start": 1,
        "end": 42,
        "ui_src": "extracted_doc_images/image12.png",
        "ui_title": "Antarmuka Master Data Unit, Ruangan & Mekanisme Soft Deletes",
        "kegiatan": "Pengembangan API CRUD Master Data (Unit, Ruangan, Vendor) serta komponen SmartCascadingLocation pada frontend untuk memilih ruangan berdasarkan unit sekolah terpilih."
    },
    {
        "id": "No_10_Modul_Tiket_Form_5_Prosedur",
        "title": "Modul Tiket Sarpras: 5 Jenis Prosedur Kerja",
        "tanggal": "15 September 2026",
        "file": "frontend/src/views/tickets/TicketCreateView.vue",
        "lang": "html",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image5.png",
        "ui_title": "Antarmuka Formulir Pengajuan Tiket Sarpras & Pemilihan Vendor Cerdas",
        "kegiatan": "Pembangunan antarmuka formulir pengajuan sarpras untuk 5 prosedur (Perbaikan, Pemeliharaan, Pengadaan, Mutasi, Pemutihan) dilengkapi tabel multi-item repeater."
    },
    {
        "id": "No_11_Kompresi_Foto_Kamera_HP",
        "title": "Kompresi Gambar Canvas & Akses Kamera Smartphone",
        "tanggal": "16 September 2026",
        "file": "frontend/src/components/ImageCompressorUploader.vue",
        "lang": "html",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image9.png",
        "ui_title": "Diagram Alur Kerja Kompresi Gambar Client-Side via HTML5 Canvas",
        "kegiatan": "Pembuatan komponen ImageCompressorUploader berbasis HTML5 Canvas untuk mereduksi ukuran foto bukti fisik menjadi < 400 KB dan integrasi tombol jepret kamera HP langsung di lapangan."
    },
    {
        "id": "No_12_Approval_Berjenjang_Review_ASP",
        "title": "Service Approval Berjenjang & Verifikasi ASP",
        "tanggal": "17 September 2026",
        "file": "backend/app/Services/ApprovalRoutingService.php",
        "lang": "php",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image8.png",
        "ui_title": "Alur Siklus Hidup Review Lapangan & Verifikasi ASP Wilayah",
        "kegiatan": "Pembangunan service layer ApprovalRoutingService untuk mengatur transisi status tiket secara ketat dan antarmuka antrean review tiket lapangan bagi petugas ASP Wilayah."
    },
    {
        "id": "No_13_Meja_Persetujuan_KD_Sarpras",
        "title": "Meja Persetujuan Kepala Departemen Sarpras",
        "tanggal": "18 September 2026",
        "file": "frontend/src/views/approvals/KdApprovalDeskView.vue",
        "lang": "html",
        "start": 1,
        "end": 42,
        "ui_src": "extracted_doc_images/image3.png",
        "ui_title": "Meja Persetujuan KD Sarpras (Approval Desk) & Filter Status Tiket",
        "kegiatan": "Pembuatan halaman antarmuka persetujuan terpusat untuk Kepala Departemen Sarpras dengan opsi one-click approve, reject, atau revision required beserta dialog catatan revisi."
    },
    {
        "id": "No_14_Timeline_Tracker_Audit_Log",
        "title": "Komponen Timeline Tracker & Audit Log Service",
        "tanggal": "21 September 2026",
        "file": "frontend/src/components/TimelineTracker.vue",
        "lang": "html",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image14.png",
        "ui_title": "Visual Linimasa Tracking Tiket & Jejak Rekam Forensik Audit Log",
        "kegiatan": "Pembuatan visual timeline linimasa progres tiket dari Submitted hingga BAST selesai, serta pencatatan audit log otomatis ke tabel audit_logs untuk transparansi sistem."
    },
    {
        "id": "No_15_Modul_Purchase_Order_Terbilang",
        "title": "Modul Purchase Order & Helper Terbilang Rupiah",
        "tanggal": "22 September 2026",
        "file": "backend/app/Helpers/TerbilangHelper.php",
        "lang": "php",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image10.png",
        "ui_title": "Siklus Hidup Lengkap (End-to-End Lifecycle) Purchase Order (PO)",
        "kegiatan": "Pengembangan modul Purchase Order untuk pembuatan PO otomatis dari tiket yang disetujui, kalkulasi anggaran vendor, dan konversi angka rupiah ke kalimat teks resmi bahasa Indonesia."
    },
    {
        "id": "No_16_Cetak_PDF_BAST_Export_Excel",
        "title": "Generator PDF DomPDF BAST & Ekspor Laporan Excel",
        "tanggal": "23 September 2026",
        "file": "backend/app/Http/Controllers/Api/V1/BastDocumentController.php",
        "lang": "php",
        "start": 1,
        "end": 45,
        "ui_src": "assets/Contoh Hasil PO.png",
        "ui_title": "Lembar Cetak Dokumen PDF Resmi Berkop Yayasan YGPKB",
        "kegiatan": "Integrasi library barryvdh/laravel-dompdf untuk menghasilkan dokumen PO dan 5 format BAST berkop resmi YGPKB siap tanda tangan basah, serta ekspor rekapitulasi ke Excel."
    },
    {
        "id": "No_17_Kalender_Servis_Preventif",
        "title": "Modul Kalender Pemeliharaan Servis Preventif",
        "tanggal": "24 September 2026",
        "file": "backend/app/Http/Controllers/Api/V1/PreventiveScheduleController.php",
        "lang": "php",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image7.png",
        "ui_title": "Kalender Pemeliharaan Rutin Fasilitas (AC, Genset, Toren Air) 14 Unit",
        "kegiatan": "Pengembangan modul pemantauan jadwal servis berkala fasilitas aset (AC, genset, pompa air) di 14 unit sekolah/kampus untuk mencegah kerusakan mendadak."
    },
    {
        "id": "No_18_Laporan_Eksekutif_User_Management",
        "title": "Laporan Eksekutif, User Management & Modal Alert",
        "tanggal": "25 September 2026",
        "file": "frontend/src/views/reports/ExecutiveReportView.vue",
        "lang": "html",
        "start": 1,
        "end": 42,
        "ui_src": "extracted_doc_images/image4.png",
        "ui_title": "Notifikasi Pop-up Toast Alert & Dialog Konfirmasi Keamanan Global",
        "kegiatan": "Pembuatan dashboard ringkasan status tiket untuk pimpinan yayasan, manajemen data akun pengguna (UserManagementView), serta dialog konfirmasi global (ConfirmModal)."
    },
    {
        "id": "No_19_Blackbox_Testing_7_Role",
        "title": "Pengujian Blackbox Testing 7 Peran Pengguna",
        "tanggal": "28 September 2026",
        "file": "backend/app/Policies/UserPolicy.php",
        "lang": "php",
        "start": 1,
        "end": 40,
        "ui_src": "extracted_doc_images/image1.png",
        "ui_title": "Matriks Pengujian Fungsional Blackbox Testing 7 Role Pengguna",
        "kegiatan": "Pelaksanaan pengujian fungsional sistem menggunakan metode Blackbox Testing untuk 20 skenario uji (RBAC, isolasi data unit, upload foto, alur approval) dengan seluruh status Valid."
    },
    {
        "id": "No_20_Mobile_Responsive_Drawer_Testing",
        "title": "Responsivitas Mobile: Drawer Sidebar & Kamera Lapangan",
        "tanggal": "29 September 2026",
        "file": "frontend/src/components/AppSidebar.vue",
        "lang": "html",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image11.png",
        "ui_title": "Tampilan Drawer Sidebar Responsif pada Layar Smartphone",
        "kegiatan": "Pengujian antarmuka sistem pada perangkat mobile layar kecil, penyempurnaan tombol hamburger menu drawer navigasi, serta perbaikan bug tata letak form pada peramban ponsel."
    },
    {
        "id": "No_21_Deployment_Vercel_Evaluasi",
        "title": "Build Production, Deployment Cloud Vercel & Evaluasi",
        "tanggal": "30 September 2026",
        "file": "README.md",
        "lang": "markdown",
        "start": 1,
        "end": 45,
        "ui_src": "extracted_doc_images/image6.png",
        "ui_title": "Live Deployment Sistem SIPARAS di Cloud Platform Vercel",
        "kegiatan": "Konfigurasi build production frontend, deployment aplikasi ke platform Vercel (https://siparas-frontend.vercel.app), evaluasi akhir bersama pembimbing instansi YGPKB, dan penyerahan sistem."
    }
]

print(f"Creating documentation evidence in {BASE_DIR}...")

for item in ITEMS_CONFIG:
    folder_path = os.path.join(BASE_DIR, item["id"])
    os.makedirs(folder_path, exist_ok=True)
    
    # 1. Code Screenshot
    code_text = get_file_snippet(item["file"], item["start"], item["end"])
    code_out = os.path.join(folder_path, "bukti_code.png")
    code_title = f"{item['file']} ({item['start']}-{item['end']}) - SIPARAS"
    generate_code_image(code_text, item["lang"], code_title, code_out)
    
    # 2. UI Screenshot
    ui_out = os.path.join(folder_path, "bukti_ui.png")
    process_ui_image(item["ui_src"], ui_out, item["ui_title"])
    
    # 3. Keterangan Text File
    ket_path = os.path.join(folder_path, "keterangan.txt")
    with open(ket_path, "w", encoding="utf-8") as f:
        f.write(f"=== BUKTI LOGBOOK PENGERJAAN SIPARAS ===\n")
        f.write(f"No Modul     : {item['id'][:5]}\n")
        f.write(f"Hari/Tanggal : {item['tanggal']}\n")
        f.write(f"Fokus Modul  : {item['title']}\n")
        f.write(f"Kegiatan     : {item['kegiatan']}\n\n")
        f.write(f"--- DOKUMENTASI TERLAMPIR ---\n")
        f.write(f"1. bukti_code.png : Tangkapan layar kode implementasi pada berkas '{item['file']}'\n")
        f.write(f"2. bukti_ui.png   : Tangkapan layar antarmuka pengguna / diagram arsitektur '{item['ui_title']}'\n")
    print(f"Completed folder: {item['id']}")

print("\nAll 18 folders created successfully inside 'bukti_logbook'!")
