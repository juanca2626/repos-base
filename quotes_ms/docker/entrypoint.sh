#!/bin/bash

# Determinar si estamos en un entorno de desarrollo
IS_DEV_ENV=false
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    IS_DEV_ENV=true
fi

# Instalar dependencias
if [ ! -f "vendor/autoload.php" ]; then
    echo "Ejecutando composer install..."
    if [ "$IS_DEV_ENV" = true ]; then
        composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader
    else
        composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
    fi
fi

# Configuración del archivo .env
if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    case "$APP_ENV" in
    "local")
        echo "Copying .env.example ... "
        cp .env.example .env
    ;;
    "production")
        echo "Copying .env.prod ... "
        cp .env.production .env
    ;;
    esac
else
    echo "env file exists."
fi

echo "|============= Permisos en la carpeta storage =============|"
chmod -R 775 /var/www/storage
chown -R www-data:www-data /var/www/storage

echo "|============= Depurando carpetas de laravel views/sessions =============|"
rm -rf /var/www/storage/framework/views/*
rm -rf /var/www/storage/framework/sessions/*

echo "|============= Ejecutando key:generate =============|"
php artisan key:generate

echo "|============= Ejecutando cache:clear =============|"
php artisan cache:clear

echo "|============= Ejecutando php-fpm -D & nginx -g =============|"
php-fpm -D
nginx -g "daemon off;"
