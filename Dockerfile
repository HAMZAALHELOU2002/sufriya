FROM php:8.2-cli

WORKDIR /var/www/html

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# نسخ ملفات المشروع
COPY . .

# تثبيت الاعتماديات
RUN composer install --no-dev --optimize-autoloader

# ضبط الصلاحيات لمجلدات لارافيل
RUN chmod -R 775 storage bootstrap/cache

# تشغيل خادم لارافيل على البورت المطلوب من Render
CMD php artisan serve --host=0.0.0.0 --port=10000
