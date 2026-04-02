📌 Regionalización - Guía de Instalación y Uso

Este documento describe los pasos necesarios para configurar y trabajar con el módulo de Regionalización en el proyecto.

⚙️ Instalación inicial

1. Ejecutar las migraciones:

php artisan migrate

2. Compilar los assets:

npm run dev (dev)
npm run build (prod)

3. Regenerar el autoload de Composer:

composer dump-autoload

4. Cargar datos iniciales de regiones:

php artisan db:seed --class=DataBusinessRegionSeeder

🌎 Configuración de Regiones

- Cada cliente tiene asignada por defecto la región Perú.
- En caso de añadir una nueva región: 
    - Se deben asignar markups.
    - Asignar las regiones correspondientes a cada cliente.
    - Para listar ejecutivos por región, primero es necesario asignarles tanto la región como los mercados que les correspondan.

🔄 Sincronización de Hoteles
Para iniciar la sincronización de hoteles con HyperGuest:
1. Ejecutar el comando indicando los países (ISOs separados por comas):

php artisan sync:hotels --countries=EC,CO,AR

⚠️ Este proceso se envía a una cola en segundo plano.

2. Procesar la cola: 

php artisan queue:work --queue=sync_hyperguest_pull --once

✅ Con estos pasos, el módulo de Regionalización quedará correctamente configurado y en funcionamiento.

VARIABLES DE ENTORNO DE HYPERGUEST 
1. MULTICHANNEL_BASE_URL=
2. MULTICHANNEL_API_KEY=
3. MULTICHANNEL_ENABLED=


#AGREGAR TRADUCCIONES PARA EL MODULO DE PAQUETES

URL_FRONT  + /modules

1. label.continue_without | Continuar sin número
2. label.save_and_continue | Guardar y continuar

CONFIGURACION DE WORKER

[program:work-queue-sync-hyperguest-pull]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/backend.limatours.com.pe/artisan queue:work --queue=sync_hyperguest_pull
autostart=true
autorestart=true
user=ubuntu
numprocs=1
redirect_stderr=true
stderr_logfile=/var/log/supervisor/sync_hyperguest_pull.err.log
stdout_logfile=/var/log/supervisor/sync_hyperguest_pull.out.log


[program:work-queue-hyperguest-static-import]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/backend.limatours.com.pe/artisan queue:work --queue=hyperguest_static_import
autostart=true
autorestart=true
user=ubuntu
numprocs=1
redirect_stderr=true
stderr_logfile=/var/log/supervisor/hyperguest_static_import.err.log
stdout_logfile=/var/log/supervisor/hyperguest_static_import.out.log

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart
