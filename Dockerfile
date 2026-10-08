FROM php:8.2-cli

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

# ضبط الصلاحيات
RUN chmod -R 777 storage bootstrap/cache

# منفذ التشغيل الذي سيتعامل معه Render
EXPOSE 10000

# أمر التشغيل لخادم لارافيل المدمج
CMD php artisan serve --host=0.0.0.0 --port=10000
