<p align="center"><img src="http://www.limatours.com.pe/assets/online/img/logo.png"></p>

# Microservicio (QUOTES_MS)

Este microservicio proporciona datos de las cotizaciones

## Tabla de Contenidos

- [Descripción](#descripción)
- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Documentación de la API](#documentación-de-la-api)
- [Desarrollo](#desarrollo)
- [Despliegue](#despliegue)

## Descripción

El Microservicio de cotizaciones es una aplicación de Laravel 10 diseñada para manejar varias tareas de
soporte para el proyecto de Aurora. Proporciona un conjunto de módulos que pueden integrarse fácilmente
en otras aplicaciones o microservicios dentro del proyecto.

## Características

- **Cotizaciones**: Gestiona y realiza cotizaciones a medida.
- **Reservas**: Puedes realizar reservas a partir de la aplicación.
- **Servicios**: Permite buscar los servicios disponibles y agregarlos a tu cotización.
- **Hoteles**: Permite buscar los hoteles disponibles y agregarlos a tu cotización.
- **Descarga de precios**: Permite descargar los precios de los servicios y hoteles seleccionados de tu cotización.

## Requisitos

- PHP 8.2 o superior
- MySQL 8.0 o superior
- Docker (para el desarrollo local)
- Make (para ejecutar comandos)

## Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/limatours/quotes_ms.git
```

2. Navega al directorio del proyecto:

```bash
cd quotes_ms
```

3. Copia el archivo de configuración:

```bash
cp .env.example .env
```

4. Construye y ejecuta los contenedores de desarrollo:

```bash
make fresh
```

Este comando construirá las imágenes Docker, iniciará los contenedores y ejecutará las migraciones y seeders necesarios.

## Configuración

1. Crea una red virtual personalizada para contenedores Docker.

```bash
docker network create lito_network
```
2. Construye una imagen Docker desde un Dockerfile.

```bash
docker build --no-cache -f local.Dockerfile .
```

3. Orquesta múltiples contenedores definidos en un archivo YAML.

```bash
docker compose -f docker-compose.local.yml up --force-recreate -d
```

4. Cambiar el tipo de lectura del archivo docker/entrypoint.local.sh  "CRLF" por "LF" 

```bash
dos2unix docker/entrypoint.local.sh
```

### Configuración de la base de datos

Para configurar la base de datos, debes crear un usuario con privilegios de acceso a la base de datos y un archivo de
configuración de conexión a la base de datos.

1. Crear un usuario con privilegios de acceso a la base de datos de tu laragon:

Abre una terminal y ejecuta los siguientes comandos:

```bash
mysql -u root -p
```
verás el prompt mysql> donde debes crear el usuario y asignarle los privilegios de acceso a la base de datos:
```bash
CREATE USER 'remote_user'@'%' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON aurora_prod.* TO 'remote_user'@'%';
FLUSH PRIVILEGES;
```

2. En el archivo .env, cambia el valor de la variable DB_USERNAME por el nombre de usuario que creaste en el paso anterior:

```
DB_CONNECTION=mysql
DB_HOST=<DIRECCION_IP_LARAGON> o "host.docker.internal" (local)
DB_PORT=3306
DB_DATABASE=aurora_prod
DB_USERNAME=remote_user
DB_PASSWORD=
```

## Uso

Una vez que la aplicación esté instalada y configurada, podrás acceder a ella en http://localhost:8080.

Para interactuar con los diferentes módulos, puedes utilizar los endpoints de la API proporcionados. Para obtener más
información, consulta la Documentación de la API.

## Documentación de la API

La documentación de la API está disponible
en [Postman](https://lively-crater-571073.postman.co/workspace/3fa54e6b-baa5-47c1-b54f-dd239d1a18fe). Proporciona
información detallada sobre los endpoints disponibles, formatos de solicitud/respuesta y requisitos de autenticación.

## Desarrollo

Para el desarrollo local, puedes utilizar los siguientes comandos para crear nuestros contenedores:

1. Muestra los contenedores

```bash
make ps
```

2. Construye los contenedores de desarrollo

```bash
make build
```

3. Iniciar el servidor de

```bash
make start
```

4. Detiene el servidor de desarrollo

```bash
make stop
```

5. Reinicia los contenedores

```bash
make restart
```

6. Destruye los contenedores

```bash
make destroy
```

7. Destruye y vuelve a crear los contenedores de desarrollo

```bash
make fresh
```

Para poder ejecutar dentro de nuestro contenedor donde se encuentra nuestro app, podemos usar estos comandos:

1. Ejecuta composer install

```bash
make install
```

2. Ejecuta las migraciones de laravel

```bash
make migrate
```

3. Borra la base de datos y vuelve a correr las migraciones

```bash
make migrate-fresh
```

4. Ejecuta composer dump-autoload

```bash
make dumpautoload
```

## Despliegue

Para desplegar la aplicación en un entorno de producción, puedes utilizar el siguiente comando:

```bash
make fresh-prod
```
