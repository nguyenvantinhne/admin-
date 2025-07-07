FROM php:8.2-apache

# Cài các extension cần thiết
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy source vào container
COPY . /var/www/html/

# Phân quyền
RUN chown -R www-data:www-data /var/www/html/

# Bật mod_rewrite nếu cần
RUN a2enmod rewrite

EXPOSE 80
