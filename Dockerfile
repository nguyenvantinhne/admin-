FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install zip

# Cài composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy code
COPY . /var/www/html
WORKDIR /var/www/html

# Cài thư viện PHP
RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite
