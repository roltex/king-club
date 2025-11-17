# Stop any existing PHP processes
Get-Process php -ErrorAction SilentlyContinue | Where-Object {$_.MainWindowTitle -eq ""} | Stop-Process -Force

# Navigate to backend
Set-Location "C:\Users\My Computer\poker\backend"

# Clear all caches
Write-Host "Clearing caches..." -ForegroundColor Yellow
php artisan config:clear 2>$null
php artisan route:clear 2>$null
php artisan view:clear 2>$null

# Start server
Write-Host "`nStarting Laravel server..." -ForegroundColor Green
Write-Host "Backend will be available at: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "Admin Panel: http://127.0.0.1:8000/admin" -ForegroundColor Cyan
Write-Host "API: http://127.0.0.1:8000/api" -ForegroundColor Cyan
Write-Host "`nPress Ctrl+C to stop the server`n" -ForegroundColor Yellow

php artisan serve

