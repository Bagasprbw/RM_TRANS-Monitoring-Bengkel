# Panduan Deployment Server Windows (Native + Laragon + NSSM)

Dokumen ini berisi panduan lengkap deployment aplikasi **RM Trans** pada **Server Windows** tanpa menggunakan Docker/WSL, lengkap dengan konfigurasi **Auto-Start Service** agar aplikasi otomatis berjalan kembali saat komputer menyala/restart (misalnya saat mati lampu).

---

## 📋 Prasyarat & Software yang Dibutuhkan

1. **Laragon 5** (untuk Database MySQL).
2. **PHP 8.3 (VS16 x64 Thread Safe)** — Ekstrak ke `C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64`.
3. **Node.js v20 LTS / v22 LTS** (Windows Installer `.msi` dari [nodejs.org](https://nodejs.org/)).
4. **NSSM (Non-Sucking Service Manager)** — Version `2.24-101` dari [nssm.cc](https://nssm.cc/release/nssm-2.24-101-g897c7ad.zip). Copy file `nssm.exe` (folder `win64`) ke `C:\Windows\System32\`.

---

## 🛠️ Langkah Instalasi & Konfigurasi Server

### 1. Konfigurasi Laragon & Database MySQL
* Aktifkan PHP 8.3 di Laragon: Klik kanan Laragon -> **PHP** -> **Version** -> pilih `php-8.3.32...`.
* Aktifkan extension PHP `zip`, `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd` pada `php.ini`.
* *(Opsional)* Jika tidak menggunakan Apache Laragon, matikan Apache: Klik Roda Gigi (Preferences) -> **Services & Ports** -> hilangkan centang **Apache** (biarkan MySQL tetap tercentang).
* Centang **Run Laragon when Windows starts** dan **Start all automatically** di menu Preferences Laragon.
* Buka HeidiSQL (tombol Database di Laragon):
  * Buat Database baru bernama: **`rm-trans`**.
  * Buat user MySQL `rmtrans` dengan password `rmtrans123` dan beri akses ke database `rm-trans`.

### 2. Konfigurasi Backend (Laravel - Port 8000)
Buka file `backend/.env` di server Anda dan sesuaikan:
```ini
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:jZevFWO2HZDgKgVxsMxOvcCIOrGhwjwldIbvn096/UY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rm-trans
DB_USERNAME=rmtrans
DB_PASSWORD=rmtrans123

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Jalankan perintah berikut di Terminal Laragon / CMD (folder `backend`):
```cmd
php C:\laragon\bin\composer\composer.phar update --ignore-platform-req=ext-zip
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed
```

### 3. Konfigurasi Frontend (Vue.js - Port 8086)
Di file `Frontend/vite.config.js`, pastikan konfigurasi `server` sudah listen ke `0.0.0.0` port `8086` dan mem-proxy `/api` ke `localhost:8000`:
```javascript
server: {
  host: '0.0.0.0',
  port: 8086,
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
    },
  },
}
```

Jalankan perintah instalasi di folder `Frontend`:
```cmd
npm install
```

### 4. Buka Port di Windows Firewall
Buka **PowerShell (Run as Administrator)** dan jalankan:
```powershell
netsh advfirewall firewall add rule name="RM TRANS Frontend (8086)" dir=in action=allow protocol=TCP localport=8086
netsh advfirewall firewall add rule name="RM TRANS Backend (8000)" dir=in action=allow protocol=TCP localport=8000
```

---

## 🔄 Mengelola Auto-Start Service (NSSM)

NSSM digunakan agar Laravel dan Vue otomatis berjalan di background begitu komputer menyala tanpa memerlukan user log in.

### 1. Pendaftaran Service Pertama Kali
Buka **CMD (Run as Administrator)**, jalankan baris demi baris:

**Registrasi Backend (Laravel):**
```cmd
nssm install RMTransBackend "C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64\php.exe" "artisan serve --host=0.0.0.0 --port=8000"
nssm set RMTransBackend AppDirectory "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\backend"
nssm start RMTransBackend
```

**Registrasi Frontend (Vue):**
```cmd
nssm install RMTransFrontend "C:\Program Files\nodejs\npm.cmd" "run dev"
nssm set RMTransFrontend AppDirectory "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\Frontend"
nssm start RMTransFrontend
```

---

### 2. Perintah Pengelolaan Service (Cheat Sheet)

Jalankan perintah ini di **CMD (Run as Administrator)**:

#### 🟢 Cek Status Service
```cmd
nssm status RMTransBackend
nssm status RMTransFrontend
```
*(Atau buka Windows Services via `Win + R` -> ketik `services.msc`)*

#### 🔄 Restart Service (Misal setelah ada perubahan kodingan backend/frontend)
```cmd
nssm restart RMTransBackend
nssm restart RMTransFrontend
```

#### ⏸️ Memberhentikan Service (Stop)
```cmd
nssm stop RMTransBackend
nssm stop RMTransFrontend
```

#### ▶️ Menyalakan Service (Start)
```cmd
nssm start RMTransBackend
nssm start RMTransFrontend
```

#### ✏️ Mengubah Konfigurasi Service (GUI Editor)
Jika ingin mengubah path, port, atau argumen service via tampilan GUI:
```cmd
nssm edit RMTransBackend
nssm edit RMTransFrontend
```

#### ❌ Menghapus Service (Remove)
Jika ingin menghapus service secara permanen:
```cmd
nssm remove RMTransBackend confirm
nssm remove RMTransFrontend confirm
```

---

## 🪵 Troubleshooting & Cek Log Error Service

Jika service tiba-tiba berhenti atau tidak bisa diakses, aktifkan pengalihan log NSSM ke file teks:

```cmd
nssm set RMTransBackend AppStdout "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\backend\nssm_stdout.log"
nssm set RMTransBackend AppStderr "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\backend\nssm_stderr.log"

nssm set RMTransFrontend AppStdout "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\Frontend\nssm_stdout.log"
nssm set RMTransFrontend AppStderr "C:\laragon\www\RM-TRANS\RM_TRANS-Monitoring-Bengkel\Frontend\nssm_stderr.log"
```

Buka file **`nssm_stderr.log`** di folder backend atau frontend untuk melihat rincian pesan error-nya.
