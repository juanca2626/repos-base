<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://aurora.limatours.com.pe/images/logo/logo-aurora.png" width="400" alt="Laravel Logo"></a></p>

## Acerca de Aurora - Files

Aurora files esta desarrollado con el Framework Laravel 10.* utilizando las buenas practicas

Documentacion:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

## Pasos para levantar en un entorno de desarrollo utilizando Docker

1. Clonar el proyecto

   ```powershell
   git clone https://git-codecommit.us-east-1.amazonaws.com/v1/repos/files_ms
   ```
2. Copiar tu archivo .env.example y renombrar a .env
3. En tu archivo .env agregar/modificar los siguientes parametros de entorno:

   ```apache
   APP_PORT=8000
   LOG_CHANNEL=stdout

   DB_CONNECTION=mysql
   DB_HOST=aurora_mysql
   DB_READING_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=file_ms
   DB_USERNAME=root
   DB_PASSWORD=password

   BROADCAST_DRIVER=redis
   CACHE_DRIVER=redis
   FILESYSTEM_DISK=local
   QUEUE_CONNECTION=database
   SESSION_DRIVER=redis
   SESSION_LIFETIME=120
   ```
4. Construir tus contenedores a partir del archivo **docker-compose.override.yml** con el comando:

   ```powershell
   docker-compose up
   ```
5. Instalar las dependencias de laravel desde tu contedor de php creado:

   ```powershell
   docker-compose run composer composer install
   ```
6. Correr las migraciones

   ```powershell
   docker exec <nombre de tu contenedor de php> php artisan migrate
   ```
7. Ingresar al proyecto: **http://localhost:8000/**

8. Fin
