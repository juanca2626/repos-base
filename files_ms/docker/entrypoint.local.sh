#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
else
    echo "env file exists."
fi


echo "|============= Permisos en la carpeta storage =============|"
chmod -R 775 /var/www/storage
chown -R www-data:www-data /var/www/storage

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

