#!/bin/bash
set -e

# Ensure SQLite database file exists
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "Created database/database.sqlite"
fi

# Set proper permissions
chmod 664 database/database.sqlite || true

# Run migrations if needed
php artisan migrate --force || true

# Start the server
exec php artisan serve --host=0.0.0.0 --port=$PORT

