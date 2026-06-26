# Script ini dijalankan di PowerShell Windows sebagai Administrator
# Tujuan: Membuat port 80 dan 8000 di Windows diteruskan ke dalam WSL
# Sehingga bisa diakses dari PC lain di jaringan yang sama

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " RM TRANS - Setup Port Forward WSL" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# Ambil IP address WSL secara otomatis
$wslIP = (wsl hostname -I).Trim().Split(" ")[0]
Write-Host "IP WSL terdeteksi: $wslIP" -ForegroundColor Green

# Tambahkan port forwarding: Windows Port -> WSL IP
Write-Host "Menambahkan port forwarding..." -ForegroundColor Yellow

# Port 8080 (Frontend)
netsh interface portproxy add v4tov4 listenport=8080 listenaddress=0.0.0.0 connectport=8080 connectaddress=$wslIP
# Port 8000 (Backend API)
netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=$wslIP
# Port 9000 (Webhook CI/CD)
netsh interface portproxy add v4tov4 listenport=9000 listenaddress=0.0.0.0 connectport=9000 connectaddress=$wslIP

# Buka port di Windows Firewall
Write-Host "Membuka port di Windows Firewall..." -ForegroundColor Yellow
netsh advfirewall firewall add rule name="RM TRANS - Frontend (8080)" dir=in action=allow protocol=TCP localport=8080
netsh advfirewall firewall add rule name="RM TRANS - Backend API (8000)" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="RM TRANS - Webhook (9000)" dir=in action=allow protocol=TCP localport=9000

Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host " Selesai! Port forwarding sudah aktif." -ForegroundColor Green
Write-Host " IP Windows kamu: $(Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias Ethernet* | Select-Object -First 1 -ExpandProperty IPAddress)" -ForegroundColor Green
Write-Host " Akses dari browser: http://<IP-Windows-kamu>:8080" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Daftar port yang aktif saat ini:" -ForegroundColor Cyan
netsh interface portproxy show all
