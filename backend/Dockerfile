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

# Cache configuration (only if .env exists, otherwise skip)
RUN php artisan config:cache || true && \
    php artisan route:cache || true && \
    php artisan view:cache || true

# Expose port
EXPOSE $PORT

# Start server
CMD php artisan serve --host=0.0.0.0 --port=$PORT

