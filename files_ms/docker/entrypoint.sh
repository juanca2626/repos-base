#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
    echo "Ejecutando composer install..."
    composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
    case "$APP_ENV" in
    "local")
        echo "Copying .env.example ... "
        cp .env.example .env
    ;;
    "production")
        echo "Copying .env.prod ... "
        cp .env.prod .env
    ;;
    esac
else
    echo "env file exists."
fi

echo "|============= Permisos en la carpeta storage =============|"
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache


echo "|============= Depurando carpetas de laravel views/sessions =============|"
rm -rf /var/www/storage/framework/views/*
rm -rf /var/www/storage/framework/sessions/*

echo "|============= Ejecutando migraciones =============|"
php artisan migrate --force

echo "|============= Ejecutando seeders =============|"
php artisan db:seed

echo "|============= Ejecutando optimize:clear =============|"
php artisan optimize:clear

echo "|============= Ejecutando route:clear =============|"
php artisan route:clear

echo "|============= Ejecutando config:clear =============|"
php artisan config:clear

echo "|============= Ejecutando cache:clear =============|"
php artisan cache:clear

echo "|============= Ejecutando supervisord =============|"
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &

echo "|============= Ejecutando php-fpm -D & nginx -g =============|"
php-fpm -D
nginx -g "daemon off;"
