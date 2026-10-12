########################################
# MaisuApp — Render.com Dockerfile
# Laravel 8 + PHP 8.1 + Nginx
########################################

# ── Stage 1: Composer dependencies ──
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ── Stage 2: Runtime ──
FROM php:8.1-fpm-alpine

# Install system packages & PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    gettext \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    curl \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && rm -rf /var/cache/apk/*

# Create required directories
RUN mkdir -p /var/www/html \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy composer vendor from build stage
COPY --from=composer-deps /app/vendor ./vendor

# Install composer & generate autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache \
    && mkdir -p storage/logs \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port (Render uses $PORT, default 10000)
EXPOSE 10000

# Entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
