
# Backend API-only: tidak butuh build aset Vite (frontend deploy terpisah),
# jadi langsung ke image PHP agar build Railway lebih cepat.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Gagalkan build kalau driver MySQL tidak terinstall (biar ketahuan di Build Logs)
RUN php -m | grep -i pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Migrate + seed otomatis tiap boot (aman diulang: seeder pakai updateOrCreate),
# lalu jalankan server. PORT diisi otomatis oleh Railway.
# Baris php -m sengaja: bukti di Deploy Logs driver apa saja yang aktif.
CMD sh -c "php -v && php -m | grep -i -E 'pdo|mysql' ; php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
