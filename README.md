# SIPARAS - Sistem Informasi Prasarana & Sarana YGPKB

SIPARAS adalah sistem informasi terpadu untuk manajemen Prasarana & Sarana (Sarpras) di lingkungan YGPKB. Aplikasi ini menggunakan arsitektur **Monorepo** yang menggabungkan Frontend dan Backend dalam satu repository agar lebih mudah dikelola.

## 🚀 Teknologi yang Digunakan

**Frontend:**
- **Vue.js 3** (Composition API)
- **Vite** (Build Tool yang sangat cepat)
- **Tailwind CSS** (Styling UI)
- **Pinia** (State Management)
- **Axios** (HTTP Client)

**Backend:**
- **Laravel 11** (Framework PHP modern)
- **PHP 8.3+**
- **MySQL** (Database utama)
- **Laravel Sanctum** (Autentikasi token API)

---

## 📁 Struktur Folder

```text
siparas_new/
├── backend/       # Source code API (Laravel)
├── frontend/      # Source code Antarmuka UI (Vue 3)
├── account.txt    # Kumpulan kredensial akun uji coba (Testing)
└── package.json   # Konfigurasi npm Monorepo untuk menjalankan kedua app
```

---

## 🛠️ Prasyarat (Prerequisites)

Sebelum menjalankan project ini, pastikan sistem kamu sudah terinstal aplikasi berikut:
- [Node.js](https://nodejs.org/) (Versi 18 atau terbaru)
- [PHP](https://www.php.net/) (Versi 8.2 / 8.3)
- [Composer](https://getcomposer.org/)
- Database Engine ([MySQL](https://www.mysql.com/) / MariaDB / XAMPP / Laragon)

---

## ⚙️ Cara Instalasi (Local Development)

### 1. Setup Backend (Laravel)
Buka terminal dan masuk ke folder `backend`:
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed   # Jalankan migrasi dan masukkan data awal (Seeder)
```
*(Pastikan konfigurasi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env` sudah sesuai dengan database lokal kamu).*

### 2. Setup Frontend (Vue 3)
Buka terminal baru dan masuk ke folder `frontend`:
```bash
cd frontend
npm install
```

---

## 🏃‍♂️ Cara Menjalankan Aplikasi

Project ini sudah dikonfigurasi menggunakan library `concurrently`, sehingga kamu **tidak perlu** membuka dua terminal terpisah. 

Cukup buka terminal di **folder utama (root directory `siparas_new`)**, lalu jalankan perintah:

```bash
npm install        # (Hanya perlu dijalankan sekali untuk menginstal concurrently)
npm run dev
```

Perintah di atas akan secara otomatis menjalankan:
1. **Frontend Vite** di `http://localhost:5173`
2. **Backend Artisan** di `http://localhost:8000`

---

## 👥 Daftar Hak Akses (Roles)

SIPARAS menerapkan *Role Based Access Control (RBAC)* yang sangat ketat. Terdapat berbagai tingkatan role, di antaranya:
- **ASP (Asisten Sarpras)** berdasarkan wilayah (Regu, Kubu Raya, IBC, ITBSS)
- **KD Sarpras** (Kepala Departemen Sarpras)
- **Admin Sarpras**
- **Koordinator** (Utama & SKI)
- **Dept Kantor Pusat** (YGPKB)

👉 *Untuk melihat kombinasi username dan password uji coba (testing), silakan cek file `account.txt`.*

---

## 🌐 Panduan Deployment (Live Testing)

Jika kamu ingin melakukan testing aplikasi ke perangkat lain (Mobile/Tablet/PC) menggunakan jaringan publik, kamu bisa memisahkan proses deployment:

1. **Backend (API):**
   * Paling direkomendasikan menggunakan layanan VPS / Railway.
   * Untuk testing sementara dari laptop (lokal), kamu bisa menggunakan [Ngrok](https://ngrok.com/) dengan perintah `ngrok http 8000`.
2. **Frontend (UI):**
   * Sangat direkomendasikan untuk di-deploy ke **Vercel** atau **Netlify** secara gratis.
   * Pada dashboard Vercel, pastikan kamu menambahkan *Environment Variable* `VITE_API_BASE_URL` yang diarahkan ke URL Backend kamu (misal: URL Ngrok Backend atau VPS).
   * *Catatan:* Konfigurasi Axios di aplikasi ini (`api.js`) sudah ditambahkan header bypass agar tidak terblokir oleh halaman peringatan dari Ngrok versi gratis.

---
*Dibuat untuk kebutuhan pengembangan Sistem Informasi Prasarana & Sarana YGPKB.*
