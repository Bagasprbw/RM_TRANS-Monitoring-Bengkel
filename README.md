# RM Trans - Monitoring Bengkel & Kendaraan

## 📝 Deskripsi Project
**RM Trans** adalah sebuah aplikasi berbasis web (full-stack) yang berfungsi untuk melakukan pemantauan (monitoring) kendaraan dan operasional bengkel. Aplikasi ini mengelola data armada kendaraan, jenis dan merk armada, memantau kendaraan yang aktif, mencatat log kilometer, serta mengelola komponen kendaraan dan riwayat perawatannya.

## 🛠 Tech Stack

### Frontend
- **Framework:** Vue.js (v2.7.16)
- **Build Tool:** Vite (v5.0.0)
- **State Management:** Vuex (v3.6.2)
- **Routing:** Vue Router (v3.6.5)
- **HTTP Client:** Axios (v1.14.0)
- **Library Tambahan:** ExcelJS, FileSaver, SweetAlert2

### Backend
- **Framework:** Laravel (v13.0)
- **Bahasa Pemrograman:** PHP (v8.3 atau lebih baru)
- **Autentikasi:** Laravel Sanctum (v4.3)
- **Database:** MySQL
- **Lainnya:** Maatwebsite Excel (v3.1)

## 📂 Struktur Folder Utama
Berdasarkan hasil scan project, berikut adalah struktur folder utama dari aplikasi ini:

```
RM_TRANS-Monitoring-Bengkel/
├── backend/                  # Folder source code Laravel (API & Backend Logic)
│   ├── app/                  # Controller, Models, dll
│   ├── config/               # Konfigurasi aplikasi
│   ├── database/             # Migrations & Seeders
│   ├── routes/               # Routing aplikasi (api.php)
│   ├── composer.json         # Konfigurasi dependensi backend
│   └── .env.example          # Template environment backend
├── Frontend/                 # Folder source code Vue.js (User Interface)
│   ├── src/                  # Komponen Vue, Router, Store (Vuex), Views
│   ├── package.json          # Konfigurasi dependensi frontend
│   ├── vite.config.js        # Konfigurasi vite
│   └── .env.example          # Template environment frontend
├── .github/                  # Github workflows / actions
├── cloudflared/              # Konfigurasi cloudflare tunnel
├── webhook/                  # Konfigurasi webhook deployment
├── docker-compose.yml        # Konfigurasi docker (opsional)
├── Makefile                  # Script automasi terminal
└── deploy.sh                 # Script deployment
```

## ⚙️ Persyaratan Sistem (Prasyarat)
Sebelum menjalankan project ini, pastikan Anda telah menginstal:
- PHP (Min. versi 8.3)
- Composer
- Node.js & npm (Node Package Manager)
- MySQL Server

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Instalasi Backend (Laravel)
Jalankan perintah berikut di dalam terminal pada folder `backend`:

1. Buka terminal dan arahkan ke folder backend:
   ```bash
   cd backend
   ```
2. Install dependensi PHP dengan Composer:
   ```bash
   composer install
   ```
3. Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
4. Generate *application key*:
   ```bash
   php artisan key:generate
   ```
5. Sesuaikan konfigurasi database pada file `.env` (lihat bagian Environment Variables).
6. Jalankan migrasi database (beserta seeder jika ada):
   ```bash
   php artisan migrate
   ```
7. Jalankan local development server:
   ```bash
   php artisan serve
   ```
   *Secara default, backend akan berjalan di **http://localhost:8000**.*

### 2. Instalasi Frontend (Vue.js)
Jalankan perintah berikut di dalam terminal pada folder `Frontend`:

1. Buka terminal baru dan arahkan ke folder frontend:
   ```bash
   cd Frontend
   ```
2. Install dependensi Node:
   ```bash
   npm install
   ```
3. Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
4. Jalankan local development server:
   ```bash
   npm run dev
   ```
   *Secara default, frontend (Vite) akan berjalan di port **5173** (misal: http://localhost:5173).*

### 3. Build untuk Production (Frontend)
Untuk mem-build project frontend ke lingkungan production, jalankan perintah:
```bash
npm run build
```
File hasil *build* akan berada di dalam folder `Frontend/dist` yang siap untuk di-deploy ke web server (Nginx/Apache).

---

## 🖥️ Deployment Server Windows (Auto-Start dengan NSSM)

Untuk panduan lengkap deployment di **Server Windows** tanpa Docker/WSL, lengkap dengan fitur **Auto-Start Service** ketika komputer restart/mati lampu, silakan merujuk ke dokumen panduan:

👉 **[DEPLOYMENT_WINDOWS.md](file:///home/ronaltama/PROJECT/RM_TRANS-Monitoring-Bengkel/DEPLOYMENT_WINDOWS.md)**

### Ringkasan Perintah Mengelola Windows Services (NSSM):
Buka **CMD (Run as Administrator)** di Windows Server:

* **Cek Status Service:**
  ```cmd
  nssm status RMTransBackend
  nssm status RMTransFrontend
  ```
* **Restart Service (Sesudah Update Code):**
  ```cmd
  nssm restart RMTransBackend
  nssm restart RMTransFrontend
  ```
* **Stop Service:**
  ```cmd
  nssm stop RMTransBackend
  nssm stop RMTransFrontend
  ```
* **Edit Service (GUI):**
  ```cmd
  nssm edit RMTransBackend
  nssm edit RMTransFrontend
  ```
* **Hapus Service:**
  ```cmd
  nssm remove RMTransBackend confirm
  nssm remove RMTransFrontend confirm
  ```

---

## 🔐 Environment Variables (.env)

### Backend (`backend/.env`)
Berikut adalah daftar environment variable utama yang perlu disesuaikan (sesuai `backend/.env.example`):
```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=backend   # Ubah ke nama database Anda
DB_USERNAME=root      # Ubah ke username database Anda
DB_PASSWORD=          # Ubah ke password database Anda (jika ada)
```
*[Catatan: Variabel lain seperti REDIS, MAIL, AWS, dll tersedia di file .env dan dapat dikonfigurasi jika dibutuhkan]*

### Frontend (`Frontend/.env`)
Berikut adalah daftar environment variable utama untuk frontend (sesuai `Frontend/.env.example`):
```env
VITE_API_BASE_URL=http://localhost:8000
```
*Pastikan `VITE_API_BASE_URL` mengarah ke URL tempat backend Laravel Anda berjalan.*

---

## 🗄️ Struktur Database (Tabel Migrasi)
Sistem memiliki struktur tabel database sebagai berikut (diambil dari folder `backend/database/migrations/`):
- `users` & `personal_access_tokens` (Autentikasi & User Management)
- `merk_armadas` (Data Merk Kendaraan)
- `jenis_armadas` (Data Jenis Kendaraan)
- `armadas` (Data Master Kendaraan)
- `monitoring_armada_aktifs` (Data Monitoring Kendaraan yang Aktif)
- `log_kilometers` (Pencatatan Kilometer Kendaraan)
- `category_componens` (Kategori Komponen Bengkel)
- `komponen_armadas` & `detail_komponen_armadas` (Data Komponen Kendaraan)
- `riwayat_perawatan_komponens` (Catatan Riwayat Maintenance/Perawatan)

---

## 🌐 Arsitektur Routing

### Frontend Routes (`Frontend/src/router/index.js`)
Dilengkapi dengan sistem **Router Guard** untuk pengecekan Autentikasi (membutuhkan Token).
- `/login` : Halaman Login
- `/dashboard` : Halaman Dashboard Utama (Memerlukan Auth)
- `/kendaraan` : Manajemen Kendaraan (Memerlukan Auth)
- `/monitoring-kendaraan` : Monitoring Kendaraan Aktif (Memerlukan Auth)
- `/monitoring-kendaraan/:id` : Detail Kendaraan (Memerlukan Auth)
- `/profile` : Halaman Profil Pengguna (Memerlukan Auth)
- `/riwayat-aktivitas` : Halaman Riwayat Maintenance Kendaraan (Memerlukan Auth)

### Backend API Routes (`backend/routes/api.php`)
Sebagian besar endpoint dilindungi oleh middleware `auth:sanctum`.
- **Auth:**
  - `POST /login`
  - `POST /logout`
  - `POST /update-profile`
- **Master Data (CRUD):**
  - `/armada` (Resource)
  - `/jenis_armada` (Resource)
  - `/merk_armada` (Resource)
  - `/kategori_komponen` (GET, POST, DELETE)
- **Monitoring & Transaksi:**
  - `/monitoring_armada_aktif` (Resource & Update)
  - `GET /monitoring_armada_aktif/reminders`
  - `GET /monitoring_armada_aktif/available`
  - `/log_kilometer` (GET, POST)
- **Komponen & Perawatan:**
  - `GET /monitoring_armada_aktif/{monitoring_id}/komponen`
  - `POST /monitoring_armada_aktif/{monitoring_id}/komponen`
  - `POST /komponen_armada/{id}/reset`
  - `PUT /komponen_armada/{id}`
  - `DELETE /komponen_armada/{id}`
  - `GET /riwayat_perawatan`
