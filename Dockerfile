FROM php:7.4-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg 2>/dev/null; \
    docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl opcache gd

# Configuracion de OPcache
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Script que se ejecuta al arrancar el contenedor
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
CMD ["php-fpm"]
