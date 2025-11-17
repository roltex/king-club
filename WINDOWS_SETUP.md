# 🪟 Windows Setup Guide

Quick guide for setting up the Poker Tournament System on Windows.

## Enable PHP GD Extension

The GD extension is required for QR code generation.

### Step 1: Locate your php.ini file

```powershell
# Find your php.ini location
php --ini
```

This will show something like:
```
Configuration File (php.ini) Path: C:\php
Loaded Configuration File:         C:\php\php.ini
```

### Step 2: Edit php.ini

1. Open `C:\php\php.ini` in a text editor (as Administrator)
2. Search for `;extension=gd` (note the semicolon at the start)
3. Remove the semicolon to uncomment it:
   ```ini
   ; Change this:
   ;extension=gd
   
   ; To this:
   extension=gd
   ```
4. Save the file

### Step 3: Verify the extension is enabled

```powershell
# Check if GD is now loaded
php -m | findstr gd
```

If it shows `gd`, you're good to go!

### Step 4: Try composer install again

```powershell
cd C:\Users\My Computer\poker\backend
composer install
```

## Alternative: Temporary Skip (For Testing Only)

If you want to test the system without QR codes temporarily:

```powershell
composer install --ignore-platform-req=ext-gd
```

⚠️ **Warning:** QR code generation won't work until you enable GD extension!

## Common Windows Issues

### Issue: Can't find php.ini

**Solution:**
```powershell
# Create php.ini from the template
cd C:\php
copy php.ini-development php.ini
```

### Issue: Multiple PHP versions

**Solution:**
```powershell
# Check which PHP you're using
php -v
where php

# Make sure you edit the correct php.ini
php --ini
```

### Issue: Changes not taking effect

**Solution:**
1. Close all PowerShell/CMD windows
2. Open a new PowerShell window
3. Verify: `php -m | findstr gd`

## Full Windows Setup Steps

### 1. Enable Required Extensions

Edit `C:\php\php.ini` and uncomment (remove `;` from):

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

### 2. Verify Extensions

```powershell
php -m
```

Should show: curl, fileinfo, gd, mbstring, openssl, PDO, pdo_sqlite, sqlite3, zip

### 3. Install Backend

```powershell
cd C:\Users\My Computer\poker\backend

# Install dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate key
php artisan key:generate

# Create SQLite database
type nul > database\database.sqlite

# Run migrations
php artisan migrate

# Create admin user
php artisan make:filament-user

# Start server
php artisan serve
```

### 4. Install Frontend

Open a **new PowerShell window**:

```powershell
cd C:\Users\My Computer\poker\frontend

# Install dependencies
npm install

# Create environment file
echo VITE_API_URL=http://localhost:8000/api > .env

# Start dev server
npm run dev
```

## Windows-Specific Tips

### Use Windows Terminal
- Better than CMD
- Supports multiple tabs
- Download from Microsoft Store

### Path Separators
- Windows uses `\` instead of `/`
- PowerShell accepts both
- Git Bash uses `/`

### File Permissions
- SQLite database: No special permissions needed on Windows
- Just make sure the database folder exists

### Running Multiple Servers
Open two PowerShell windows:
1. **Window 1:** `php artisan serve` (Backend)
2. **Window 2:** `npm run dev` (Frontend)

## Troubleshooting

### Port Already in Use

```powershell
# Backend on different port
php artisan serve --port=8001

# Frontend on different port
npm run dev -- --port 5174
```

### SQLite Database Lock

If you get "database is locked":
1. Close all applications accessing the database
2. Delete `database\database.sqlite-journal` if it exists
3. Restart the Laravel server

### Node/npm Issues

```powershell
# Clear npm cache
npm cache clean --force

# Delete and reinstall
rmdir /s /q node_modules
del package-lock.json
npm install
```

## Quick Reference

### Backend Commands
```powershell
php artisan serve          # Start server
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Reset database
php artisan cache:clear    # Clear cache
```

### Frontend Commands
```powershell
npm run dev        # Start dev server
npm run build      # Build for production
npm run preview    # Preview production build
```

## Next Steps

After setup:
1. Visit http://localhost:5173 (Frontend)
2. Visit http://localhost:8000/admin (Admin Panel)
3. Create a test reservation
4. Try the QR scanner

---

**Windows setup complete! 🎉**

