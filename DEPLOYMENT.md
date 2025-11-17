# 🚀 Deployment Guide

Complete guide for deploying the Poker Tournament System to production.

## 📋 Pre-Deployment Checklist

- [ ] Domain name registered
- [ ] SSL certificate obtained
- [ ] Production server ready
- [ ] Database created
- [ ] Backups configured
- [ ] Monitoring setup

## 🖥️ Server Requirements

### Minimum Requirements
- **OS:** Ubuntu 20.04+ / CentOS 8+ / Windows Server
- **PHP:** 8.2+ with SQLite extension
- **Node.js:** 18+
- **Database:** SQLite (no server needed!)
- **RAM:** 1GB minimum (2GB recommended)
- **Storage:** 5GB minimum
- **SSL:** Required for QR scanner

### Recommended
- **RAM:** 4GB+
- **CPU:** 2+ cores
- **Storage:** 20GB+ SSD
- **CDN:** For static assets

## 🔧 Backend Deployment (Laravel)

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 with SQLite
sudo apt install -y php8.2-fpm php8.2-cli php8.2-sqlite3 php8.2-xml \
  php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Note: No MySQL server needed! SQLite is file-based.
```

### 2. Clone Repository

```bash
# Create directory
sudo mkdir -p /var/www/poker-tournament
cd /var/www/poker-tournament

# Clone or upload your code
git clone <your-repo-url> .
# OR upload via FTP/SFTP

# Set permissions
sudo chown -R www-data:www-data /var/www/poker-tournament
sudo chmod -R 755 /var/www/poker-tournament
```

### 3. Backend Configuration

```bash
cd /var/www/poker-tournament/backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Production .env settings:**
```env
APP_NAME="Poker Tournament"
APP_ENV=production
APP_KEY=base64:... # Generate with php artisan key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite
# SQLite database will be at: database/database.sqlite

FRONTEND_URL=https://yourdomain.com

# Email settings (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### 4. Database Setup

```bash
# Create SQLite database file
touch database/database.sqlite

# Set proper permissions
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
chown www-data:www-data database/

# Run migrations
php artisan migrate --force

# Create admin user
php artisan make:filament-user

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 5. Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/poker-tournament
```

**Nginx config:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/poker-tournament/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/poker-tournament /etc/nginx/sites-enabled/

# Test config
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 6. SSL Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
# Test renewal
sudo certbot renew --dry-run
```

## 🎨 Frontend Deployment (Vue 3)

### Option 1: Build and Serve with Nginx

```bash
cd /var/www/poker-tournament/frontend

# Install dependencies
npm install

# Create production .env
nano .env
```

```env
VITE_API_URL=https://yourdomain.com/api
```

```bash
# Build for production
npm run build

# The dist/ folder contains the built files
```

**Nginx config for frontend:**
```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/poker-tournament/frontend/dist;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # API proxy
    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Admin panel proxy
    location /admin {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Option 2: Serve with PM2 (Node.js)

```bash
# Install PM2
sudo npm install -g pm2

# Start Vite preview server
pm2 start npm --name "poker-frontend" -- run preview

# Save PM2 config
pm2 save

# Setup startup script
pm2 startup
```

### Option 3: Deploy to Netlify/Vercel

```bash
# Build locally
npm run build

# Upload dist/ folder to Netlify/Vercel
# Or connect your Git repository for automatic deployments
```

## 🔒 Security Hardening

### 1. Firewall Setup

```bash
# Install UFW
sudo apt install -y ufw

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 2. Laravel Security

```bash
# Set proper permissions
cd /var/www/poker-tournament/backend
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Disable directory listing
# Already handled in Nginx config
```

### 3. Database Security

```bash
# Secure SQLite database file
chmod 640 /var/www/poker-tournament/backend/database/database.sqlite
chown www-data:www-data /var/www/poker-tournament/backend/database/database.sqlite

# Ensure database directory is writable by web server
chmod 750 /var/www/poker-tournament/backend/database
chown www-data:www-data /var/www/poker-tournament/backend/database
```

### 4. Environment Security

```bash
# Secure .env file
chmod 600 /var/www/poker-tournament/backend/.env

# Prevent public access
# .env should NEVER be in public directory
```

## 📊 Monitoring & Logging

### 1. Laravel Logging

```bash
# View logs
tail -f /var/www/poker-tournament/backend/storage/logs/laravel.log

# Rotate logs (add to crontab)
0 0 * * * cd /var/www/poker-tournament/backend && php artisan log:clear
```

### 2. Nginx Logging

```bash
# Access logs
tail -f /var/log/nginx/access.log

# Error logs
tail -f /var/log/nginx/error.log
```

### 3. System Monitoring

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Check system resources
htop

# Check disk usage
df -h

# Check memory
free -h
```

## 🔄 Backup Strategy

### 1. Database Backups

```bash
# Create backup script
sudo nano /usr/local/bin/backup-poker-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/poker-tournament"
DATE=$(date +%Y%m%d_%H%M%S)
DB_PATH="/var/www/poker-tournament/backend/database/database.sqlite"
mkdir -p $BACKUP_DIR

# Copy SQLite database file
cp $DB_PATH $BACKUP_DIR/database_$DATE.sqlite

# Keep only last 7 days
find $BACKUP_DIR -name "database_*.sqlite" -mtime +7 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-poker-db.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/backup-poker-db.sh
```

### 2. File Backups

```bash
# Backup application files
tar -czf /var/backups/poker-app-$(date +%Y%m%d).tar.gz \
  /var/www/poker-tournament

# Upload to S3 (optional)
aws s3 cp /var/backups/poker-app-$(date +%Y%m%d).tar.gz \
  s3://your-bucket/backups/
```

## 🚦 Health Monitoring

### 1. Setup Health Check Cron

```bash
# Create health check script
nano /usr/local/bin/health-check.sh
```

```bash
#!/bin/bash
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com/api/health)

if [ $RESPONSE -ne 200 ]; then
    echo "API health check failed! Status: $RESPONSE" | \
      mail -s "Poker Tournament Alert" admin@yourdomain.com
fi
```

```bash
chmod +x /usr/local/bin/health-check.sh

# Run every 5 minutes
*/5 * * * * /usr/local/bin/health-check.sh
```

## 📈 Performance Optimization

### 1. Enable OPcache

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. SQLite Optimization

SQLite is already optimized for small to medium applications. For better performance:

```bash
# Ensure WAL mode is enabled (Write-Ahead Logging)
# Add to your Laravel config or run once:
sqlite3 database/database.sqlite "PRAGMA journal_mode=WAL;"
```

**Note:** SQLite is perfect for:
- Single-server deployments
- Up to ~100,000 requests/day
- Concurrent reads (unlimited)
- For larger scale, consider PostgreSQL or MySQL

### 3. Nginx Caching

```nginx
# Add to Nginx config
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 🔄 Updates & Maintenance

### Updating the Application

```bash
# Backup first!
cd /var/www/poker-tournament

# Pull latest changes
git pull origin main

# Backend updates
cd backend
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend updates
cd ../frontend
npm install
npm run build

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## ✅ Post-Deployment Checklist

- [ ] Test homepage loads
- [ ] Test reservation creation
- [ ] Test QR code generation
- [ ] Test check-in process
- [ ] Test admin panel access
- [ ] Verify SSL certificate
- [ ] Test on mobile device
- [ ] Check error logs
- [ ] Verify backups working
- [ ] Test health endpoints
- [ ] Monitor performance
- [ ] Document access credentials

## 🆘 Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f backend/storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/error.log

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Database Connection Issues
```bash
# Check if database file exists
ls -la backend/database/database.sqlite

# Check permissions
ls -la backend/database/

# Recreate if needed
touch backend/database/database.sqlite
chmod 664 backend/database/database.sqlite
php artisan migrate --force
```

### Permission Issues
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/poker-tournament/backend
sudo chmod -R 775 /var/www/poker-tournament/backend/storage
```

---

**Congratulations! Your Poker Tournament System is now live! 🎉**

