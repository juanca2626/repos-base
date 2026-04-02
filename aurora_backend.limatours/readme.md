<p align="center"><img src="http://www.limatours.com.pe/assets/online/img/logo.png"></p>

## Herramientas Básicas

El desarrollo esta basado en:

- [Laravel 5.8](https://laravel.com).
- [VueJS 2](https://vuejs.org).
- [CoreUI](http://coreui.io).
- [SaSS](https://sass-lang.com).
- [Stylus](http://stylus-lang.com).
- [FontAwesome](https://fontawesome.com).

El repositorio se encuentra en [Bitbucket](https://bitbucket.com).

## Estándares

Para la correcta integración con los distintos miembros del equipo de desarrollo se deberán usar las siguientes herramientas de desarrollo.

- [PHP FI: PSR2](https://www.php-fig.org/psr/psr-2).
- [StandardJS](https://standardjs.com).

En la carpeta _hooks se encuentra el archivo pre-commit que incluye un script para realizar las comprobaciones antes de subir el código al repositorio.

## Inicio

Para iniciar el desarrollo se deben seguir los siguientes pasos:

1. Instalar [Composer](https://getcomposer.org)
2. Instalar [Yarn](https://yarnpkg.com)
3. Instalar [Git](https://git-scm.com)
4. Clonar el repositorio
5. Ejecutar: `composer install`
6. Ejecutar: `yarn`
- 6.1. Ejecutar: `npm install --legacy-peer-deps # Debes tener instalado node v14.x`  
- 6.2. Ejecutar `npm run dev`
7. Configurar el archivo .env
8. Ejecutar: `php artisan key:generate`
9. Ejecutar: `php artisan jwt:secret`
10. Ejecutar `php artisan migrate --seed`

## Desarrollar

Las siguientes funciones están disponibles:

1. Ejecutar `php artisan serve`
2. Ejecutar `npm run watch`

El usuario por defecto es **admin@admin.com** y la clave **123456**.

## Base de Datos

Cualquier modificación de la base de datos se deberá hacer usando los archivos migrations y seed que Laravel provee. Si no conoce su funcionamiento puede ingresar a: [Laravel: Migrations](https://laravel.com/docs/5.8/migrations)

## Pruebas Unitarias

Todo controlador o función deberá tener como mínimo una prueba unitaria que contemple al menos el 80% del archivo.

Para evitar conflictos utilizar el phpunit de la carpeta vendor: `vendor/bin/phpunit`

## Git

**Master:** Ambiente de desarrollo.

**Staging:** Ambiente de integración para realizar las pruebas de integración.

**Releases** Ambiente de producción con el código listo para ser envíado a GitLab.

La descripción de los commits debe ser claro y expresar en lineas generales las tareas y modificaciones realizadas.

Si están usando PHPStorm activar las opciones:
* Reformat code
* Rearrange code
* Optimize imports

## Sugerencias

* Utilizar el PHPStorm de JetBrains como IDE.
* Realizar el desarrollo sobre GNU/Linux.
* Configurar su IDE para realizar las comprobaciones de calidad de código. De preferencia usar PHP CodeSniffer para PSR2 y StandardJS.
* Para "debuggear" el código se sugiere utilizar xDebug.
* En caso de usar Windows se sugiere utilizar [Laragon](https://laragon.org)
