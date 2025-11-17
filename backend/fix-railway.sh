#!/bin/bash
set -e

cd /app

echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Creating database directory and file..."
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

echo "Generating APP_KEY..."
APP_KEY=$(php artisan key:generate --show)
echo "Generated APP_KEY: $APP_KEY"
echo ""
echo "IMPORTANT: Copy the APP_KEY above and add it to Railway Variables!"
echo ""

echo "Running migrations..."
php artisan migrate --force

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Done! Your backend should be working now."
echo "Make sure to add the APP_KEY to Railway Variables if you haven't already."

