#!/bin/sh

# Esperar 5 segundos para asegurar que todo esté listo
sleep 5

# Instalar dependencias si no existen
if [ ! -f vendor/autoload.php ]; then
  composer install
fi

# Copiar .env si no existe
if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate
fi

# Compilar config, rutas y servicios en cache
php artisan optimize

# Iniciar PHP-FPM
exec php-fpm
