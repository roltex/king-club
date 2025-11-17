FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies (skip scripts that require artisan)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Copy application files
COPY . .

# Run post-install scripts now that artisan exists
RUN composer dump-autoload --optimize --no-interaction

# Create database directory and file
RUN mkdir -p database && \
    touch database/database.sqlite && \
    chmod 664 database/database.sqlite

# Expose port
EXPOSE $PORT

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
cd /app\n\
# Clear any cached config that might have wrong values\n\
php artisan config:clear || true\n\
php artisan route:clear || true\n\
php artisan view:clear || true\n\
# Ensure database exists\n\
mkdir -p database\n\
touch database/database.sqlite\n\
chmod 664 database/database.sqlite\n\
# Cache config with correct values from environment\n\
php artisan config:cache\n\
php artisan route:cache\n\
# Start server\n\
exec php artisan serve --host=0.0.0.0 --port=$PORT\n\
' > /app/start.sh && chmod +x /app/start.sh

# Start server with startup script
CMD ["/app/start.sh"]

