# Script ini dijalankan di PowerShell Windows sebagai Administrator
# Tujuan: Membuat port 8085 dan 8005 di Windows diteruskan ke dalam WSL
# Sehingga bisa diakses dari PC lain di jaringan yang sama

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " RM TRANS - Setup Port Forward WSL" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# Ambil IP address WSL secara otomatis
$wslIP = (wsl hostname -I).Trim().Split(" ")[0]
Write-Host "IP WSL terdeteksi: $wslIP" -ForegroundColor Green

# Hapus rule lama jika ada (bersih dulu)
Write-Host "Membersihkan rule lama..." -ForegroundColor Yellow
netsh interface portproxy delete v4tov4 listenport=8085 listenaddress=0.0.0.0 2>$null
netsh interface portproxy delete v4tov4 listenport=8005 listenaddress=0.0.0.0 2>$null
netsh interface portproxy delete v4tov4 listenport=9000 listenaddress=0.0.0.0 2>$null

# Tambahkan port forwarding: Windows Port -> WSL IP
Write-Host "Menambahkan port forwarding..." -ForegroundColor Yellow

# Port 8085 (Frontend)
netsh interface portproxy add v4tov4 listenport=8085 listenaddress=0.0.0.0 connectport=8085 connectaddress=$wslIP
# Port 8005 (Backend API)
netsh interface portproxy add v4tov4 listenport=8005 listenaddress=0.0.0.0 connectport=8005 connectaddress=$wslIP
# Port 9000 (Webhook CI/CD)
netsh interface portproxy add v4tov4 listenport=9000 listenaddress=0.0.0.0 connectport=9000 connectaddress=$wslIP

# Buka port di Windows Firewall
Write-Host "Membuka port di Windows Firewall..." -ForegroundColor Yellow
netsh advfirewall firewall delete rule name="RM TRANS - Frontend" 2>$null
netsh advfirewall firewall delete rule name="RM TRANS - Backend API" 2>$null
netsh advfirewall firewall add rule name="RM TRANS - Frontend (8085)" dir=in action=allow protocol=TCP localport=8085
netsh advfirewall firewall add rule name="RM TRANS - Backend API (8005)" dir=in action=allow protocol=TCP localport=8005
netsh advfirewall firewall add rule name="RM TRANS - Webhook (9000)" dir=in action=allow protocol=TCP localport=9000

Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host " Selesai! Port forwarding sudah aktif." -ForegroundColor Green
Write-Host " IP Windows kamu: $(Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias Ethernet* | Select-Object -First 1 -ExpandProperty IPAddress)" -ForegroundColor Green
Write-Host " Akses dari browser: http://<IP-Windows-kamu>:8085" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Daftar port yang aktif saat ini:" -ForegroundColor Cyan
netsh interface portproxy show all
