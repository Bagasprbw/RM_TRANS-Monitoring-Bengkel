# ============================================================
# STAGE 1: Install Composer Dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# Install tanpa dev dependencies untuk production
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-source \
    --optimize-autoloader \
    --ignore-platform-reqs

# ============================================================
# STAGE 2: Final Production Image
# ============================================================
FROM php:8.3-fpm-alpine

# Install ekstensi PHP yang dibutuhkan Laravel + MySQL
RUN apk add --no-cache \
    linux-headers \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS linux-headers

# Set working directory
WORKDIR /var/www/html

# Salin vendor dari stage 1
COPY --from=vendor /app/vendor ./vendor

# Salin seluruh kode aplikasi
COPY . .

# Buat direktori storage yang dibutuhkan & set permission
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Salin entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
