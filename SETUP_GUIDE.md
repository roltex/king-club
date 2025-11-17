# 🚀 Quick Setup Guide

This guide will help you get the Poker Tournament System up and running in minutes.

## Prerequisites Check

Before starting, ensure you have:

- [ ] PHP 8.2+ installed (`php -v`)
- [ ] Composer installed (`composer -v`)
- [ ] Node.js 18+ installed (`node -v`)
- [ ] npm installed (`npm -v`)
- [ ] SQLite support in PHP (usually built-in)
- [ ] A code editor (VS Code, PHPStorm, etc.)

## Step-by-Step Setup

### 1️⃣ Database Setup

**Great news!** SQLite requires no setup - the database file will be created automatically when you run migrations. No server installation needed!

### 2️⃣ Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database
# Open .env in your editor - SQLite is already configured!
# DB_CONNECTION=sqlite
# (MySQL settings are commented out if you need them later)

# Create the SQLite database file
touch database/database.sqlite

# Run database migrations
php artisan migrate

# Create an admin user for Filament
php artisan make:filament-user
# Follow the prompts to create your admin account

# Optional: Install QR Code package
composer require simplesoftwareio/simple-qrcode

# Start the development server
php artisan serve
```

✅ Backend should now be running at `http://localhost:8000`

### 3️⃣ Frontend Setup

Open a **new terminal** (keep backend running):

```bash
# Navigate to frontend directory
cd frontend

# Install npm dependencies
npm install

# Create environment file
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
```

✅ Frontend should now be running at `http://localhost:5173`

### 4️⃣ Verify Installation

#### Test Backend:
Visit `http://localhost:8000/api/health`
You should see: `{"status":"ok","timestamp":"..."}`

#### Test Frontend:
Visit `http://localhost:5173`
You should see the beautiful glassmorphic homepage

#### Test Admin Panel:
1. Visit `http://localhost:8000/admin`
2. Login with the credentials you created
3. You should see the Filament dashboard

## 🎯 First Test Reservation

1. Go to `http://localhost:5173`
2. Click "Reserve Your Seat"
3. Fill in the form:
   - First Name: John
   - Last Name: Doe
   - Phone: +995555123123
   - Email: john@test.com (optional)
4. Click "Reserve My Seat"
5. You should see your confirmation with QR code!

## 🔧 Common Issues & Solutions

### Issue: "Connection refused" on API calls

**Solution:**
- Ensure backend server is running (`php artisan serve`)
- Check that `VITE_API_URL` in frontend/.env matches your backend URL
- Verify CORS settings in `backend/app/Http/Middleware/Cors.php`

### Issue: "SQLSTATE[HY000] database is locked"

**Solution:**
- Close any other applications accessing the SQLite database
- Make sure only one process is writing to the database at a time
- Restart the Laravel server: `php artisan serve`

### Issue: "npm install" fails

**Solution:**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

### Issue: QR Scanner not working

**Solution:**
- Ensure you're using HTTPS or localhost
- Grant camera permissions in browser
- Try on mobile device for better experience

### Issue: "Class 'QrCode' not found"

**Solution:**
```bash
cd backend
composer require simplesoftwareio/simple-qrcode
php artisan config:clear
```

### Issue: "Database file not found"

**Solution:**
```bash
cd backend
touch database/database.sqlite
php artisan migrate
```

## 📱 Mobile Testing

For testing on mobile devices:

1. Find your local IP:
   - Windows: `ipconfig`
   - Mac/Linux: `ifconfig` or `ip addr`

2. Update backend URL:
```bash
# In backend/.env
APP_URL=http://YOUR_IP:8000
FRONTEND_URL=http://YOUR_IP:5173

# Restart backend
php artisan serve --host=0.0.0.0
```

3. Update frontend API:
```bash
# In frontend/.env
VITE_API_URL=http://YOUR_IP:8000/api

# Restart frontend
npm run dev -- --host
```

4. Access from mobile:
   - Frontend: `http://YOUR_IP:5173`
   - Backend: `http://YOUR_IP:8000`

## 🎨 Customization

### Change Tournament Settings

Edit `backend/config/tournament.php`:
```php
'total_tables' => 6,        // Change number of tables
'seats_per_table' => 9,     // Change seats per table
'total_seats' => 54,        // Total seats (tables × seats)
```

Edit `frontend/src/config.yaml`:
```yaml
tournament:
  totalTables: 6
  seatsPerTable: 9
  totalSeats: 54
```

### Change Colors

Edit `frontend/tailwind.config.js`:
```javascript
colors: {
  poker: {
    // Your custom color palette
    500: '#0ea5e9',  // Main color
  }
}
```

## 🚀 Production Deployment

### Backend (Laravel)

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set environment to production in .env
APP_ENV=production
APP_DEBUG=false
```

### Frontend (Vue)

```bash
# Build for production
npm run build

# Output will be in dist/
# Upload dist/ folder to your web server
```

## 📊 Testing the System

### 1. Create Multiple Reservations
- Make 3-4 test reservations
- Check admin panel to see them listed

### 2. Test QR Code Check-In
- Save a QR code from confirmation page
- Go to Scanner page
- Scan the QR code
- Verify check-in success

### 3. Test Waiting List
- Make reservations until all 54 seats are full
- Next reservation should go to waiting list
- Check waiting position in confirmation

### 4. Test Cancellation
- Cancel a reservation
- Verify waiting list person is promoted

## 🎓 Learning Resources

### Laravel
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)

### Vue 3
- [Vue 3 Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)

## ✅ Setup Complete!

You now have a fully functional poker tournament system running locally!

### Quick Links:
- 🏠 Frontend: http://localhost:5173
- 🔧 Admin Panel: http://localhost:8000/admin
- 📡 API Health: http://localhost:8000/api/health

### Next Steps:
1. Create test reservations
2. Try the QR scanner
3. Explore the admin panel
4. Customize colors and settings
5. Deploy to production when ready!

---

Need help? Check the main README.md for detailed documentation!

