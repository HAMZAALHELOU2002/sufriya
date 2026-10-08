FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# إنشاء ملف قاعدة بيانات sqlite فارغ وضبط الصلاحيات
RUN mkdir -p database && touch database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache

# تعديل مسار أباتشي ليشير إلى مجلد public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# تعديل البورت ليتوافق مع Render (10000)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD php artisan config:clear && php artisan cache:clear && apache2-foreground
