# syntax=docker/dockerfile:1
FROM php:8.4-apache

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql gd zip mbstring bcmath intl opcache exif

# Apache rewrite + document root
RUN a2enmod rewrite \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Install composer deps
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Installer marker (safiull/laravel-installer) + storage symlink
RUN echo '{"marker":"installer"}' > vendor/autoload1.php \
    && mkdir -p storage/app/public storage/framework/{cache,sessions,views,testing} storage/logs bootstrap/cache \
    && touch storage/installed \
    && php artisan storage:link || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Runtime: migrate + cache + seed (idempotent) + serve
CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && php artisan view:cache && php artisan db:seed --force && apache2-foreground"]