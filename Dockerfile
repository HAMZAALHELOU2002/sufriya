FROM php:8.2-apache

# تثبيت الحزم المطلوبة للارافيل
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# تفعيل وحدات أباتشي المطلوبة
RUN a2enmod rewrite

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديد مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# تثبيت الاعتماديات الخاصة بالإنتاج
RUN composer install --no-dev --optimize-autoloader

# تغيير مسار الـ DocumentRoot في أباتشي ليشير إلى مجلد public الخاص بلارافيل
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# ضبط الصلاحيات للمجلدات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ضبط البورت ليتوافق مع متطلبات Render (البورت 10000)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

# تشغيل أباتشي
CMD ["apache2-foreground"]
