# 🚀 Start Backend Server

## Quick Start

Run this command in PowerShell from the backend directory:

```powershell
cd "C:\Users\My Computer\poker\backend"
php artisan serve
```

The server will start on: **http://127.0.0.1:8000**

## ✅ Backend is Fully Ready!

All components are installed and configured:

### ✅ Core Setup
- Laravel 11 framework
- PHP 8.2 with GD extension
- SQLite database
- All dependencies installed

### ✅ Database
- SQLite database created: `database/database.sqlite`
- Migrations run:
  - ✅ users table
  - ✅ reservations table  
  - ✅ sessions table
  - ✅ cache tables

### ✅ Admin Panel
- Filament 3 installed
- Admin user created:
  - Email: `admin@admin.com`
  - Password: `password`

### ✅ API Endpoints
All 9 API endpoints are configured:
1. `POST /api/reserve` - Create reservation
2. `GET /api/reservation/{id}` - Get by ID
3. `GET /api/reservation/phone/{phone}` - Get by phone
4. `POST /api/checkin` - Check-in
5. `POST /api/reservation/{id}/cancel` - Cancel
6. `GET /api/statistics` - Get stats
7. `GET /api/tables` - Table layout
8. `GET /api/waiting-list` - Waiting list
9. `GET /api/health` - Health check

### ✅ Custom Features
- Reservation service with smart seat allocation
- QR code generation
- Waiting list management
- Filament resources and widgets
- CORS configured for frontend
- Rate limiting (60 req/min)

## 🌐 Access Points

Once running:
- **Admin Panel:** http://127.0.0.1:8000/admin
- **API:** http://127.0.0.1:8000/api
- **Health Check:** http://127.0.0.1:8000/api/health

## 🔄 If You Need to Restart

Press `Ctrl+C` to stop the server, then run:
```powershell
php artisan serve
```

## 🛠️ Useful Commands

```powershell
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# View routes
php artisan route:list

# Check database
php artisan db:show

# Create another admin user
php artisan tinker
> App\Models\User::create(['name'=>'Admin2','email'=>'admin2@admin.com','password'=>bcrypt('password')]);
```

## ✅ Everything is Ready!

Your backend is fully configured with:
- ✅ All Laravel core files
- ✅ Custom reservation system
- ✅ Filament admin panel
- ✅ QR code generation
- ✅ API endpoints
- ✅ SQLite database
- ✅ Admin user

Just restart the server with `php artisan serve` and you're good to go! 🎉

