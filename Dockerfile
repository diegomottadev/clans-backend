FROM php:7.4-fpm

# php:7.4 usa Debian 11 (bullseye), que ya no tiene soporte:
# sus repos se movieron a archive.debian.org
RUN sed -i 's|deb.debian.org|archive.debian.org|g' /etc/apt/sources.list \
 && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99archive

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
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl opcache gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# php.ini de producción (display_errors Off, register_argc_argv Off) + endurecimiento
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY ./docker/php/security.ini /usr/local/etc/php/conf.d/zz-security.ini

# Configuracion de OPcache
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:2.8.9 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Script que se ejecuta al arrancar el contenedor
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
CMD ["php-fpm"]
