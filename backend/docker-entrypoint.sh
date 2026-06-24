#!/bin/sh
set -e

echo "========================================"
echo " RM TRANS - Backend Startup"
echo "========================================"

# Tunggu MySQL siap
echo "[1/5] Menunggu koneksi database..."
until php artisan db:monitor --databases=mysql 2>/dev/null || \
      php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
  echo "  -> Database belum siap, coba lagi dalam 3 detik..."
  sleep 3
done
echo "  -> Database terhubung!"

# Generate APP_KEY jika belum ada
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "[2/5] Men-generate APP_KEY..."
    php artisan key:generate --force
else
    echo "[2/5] APP_KEY sudah ada, skip."
fi

# Jalankan migrasi
echo "[3/5] Menjalankan migrasi database..."
php artisan migrate --force

# Clear & cache konfigurasi untuk production
echo "[4/5] Optimisasi konfigurasi..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set ulang permission storage (jaga-jaga)
echo "[5/5] Setting permission storage..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "========================================"
echo " Backend siap berjalan!"
echo "========================================"

exec "$@"
