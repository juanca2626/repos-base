<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDataToAppMenuSubmenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppEcommerce = DB::table('apps')->insertGetId(
            ['name' => 'ECOMMERCE']  //1
        );

        $idAppAdmin = DB::table('apps')->insertGetId(
            ['name' => 'ADMINISTRACIÓN'] //2

        );

        $idAppManagement = DB::table('apps')->insertGetId(
            ['name' => 'GESTIÓN'] //3

        );

        $idAppAux = DB::table('apps')->insertGetId(
            ['name' => 'AUXILIARES'] //4

        );

        $idAppOperations = DB::table('apps')->insertGetId(
            ['name' => 'OPERACIONES'] //5
        );

        $idAppNegotiations = DB::table('apps')->insertGetId(
            ['name' => 'NEGOCIACIONES'] //5
        );

        //NEGOCIACIONES
        DB::table('menus')->insert([
            ['name' => 'Bases Tarifarias', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/dashboard', 'icon' => 'fa-globe'],
            ['name' => 'Tipos Unidad', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/unit-type-list', 'icon' => 'fa-globe'],
            ['name' => 'Tipos Alimentacion', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/alimentation-type-list', 'icon' => 'fa-globe'],
            ['name' => 'Tipos Pasajeros', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/passenger-type-list', 'icon' => 'fa-globe'],
            ['name' => 'Origen Pasajeros', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/passenger-origin-list', 'icon' => 'fa-globe'],
            ['name' => 'Tipos de Productos', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/types-product-list', 'icon' => 'fa-globe'],
            ['name' => 'Series', 'app_id' => $idAppNegotiations, 'path' => 'negotiations/series-list', 'icon' => 'fa-globe'],
        ]);

        //OPERACIONES
        DB::table('menus')->insert([
            ['name' => 'Pautas operativas', 'app_id' => $idAppOperations, 'path' => 'operations/guidelines', 'icon' => 'fa-globe'],
            ['name' => 'Perfiles', 'app_id' => $idAppOperations, 'path' => 'operation/profiles', 'icon' => 'fa-globe'],
        ]);


        //ECOMMERCE
        $idMenuEecomerce = DB::table('menus')->insertGetId(
            ['name' => 'Información General', 'app_id' => $idAppEcommerce, 'icon' => 'fa-globe']
        );

        //ADMINISTRACIÓN
        $uno = DB::table('menus')->insertGetId(
            ['name' => 'Servicios Maestros', 'app_id' => $idAppAdmin, 'icon' => 'fa-globe-americas', 'slug' => 'masterservices'] //1
        );

        DB::table('sub_menus')->insertGetId(
            ['name' => 'Lista', 'menu_id' => $uno, 'icon' => 'fa-bars']);

        $dos = DB::table('menus')->insertGetId(
            ['name' => 'Servicios', 'app_id' => $idAppAdmin, 'icon' => 'fa-globe-americas', 'slug' => 'services']//2
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Lista', 'menu_id' => $dos, 'icon' => 'fa-bars'],
            ['name' => 'Clasifaciones', 'menu_id' => $dos, 'icon' => 'fa-boxes'],
            ['name' => 'Incluyentes', 'menu_id' => $dos, 'icon' => 'fa-tasks'],
            ['name' => 'Experiencias', 'menu_id' => $dos, 'icon' => 'fa-hiking'],
            ['name' => 'Requisitos', 'menu_id' => $dos, 'icon' => 'fa-check-double'],
            ['name' => 'Reestricciones', 'menu_id' => $dos, 'icon' => 'fa-ban'],
            ['name' => 'Tipo de Operatividad', 'menu_id' => $dos, 'icon' => 'fa-list-alt'],
            ['name' => 'Tipo de Servicios', 'menu_id' => $dos, 'icon' => 'fa-th-list'],
            ['name' => 'Categorías', 'menu_id' => $dos, 'icon' => 'fa-th-list'],
            ['name' => 'Políticas de cancelación', 'menu_id' => $dos, 'icon' => 'fa-business-time'],
            ['name' => 'Tags', 'menu_id' => $dos, 'icon' => 'fa-th-list'],
            ['name' => 'Destacados', 'menu_id' => $dos, 'icon' => 'fa-check-double'],
            ['name' => 'Orden Tarifario', 'menu_id' => $dos, 'icon' => 'fa-check-double'],
            ['name' => 'Grupos', 'menu_id' => $dos, 'icon' => 'fa-boxes'],
            ['name' => 'Configuración Markups', 'menu_id' => $dos, 'icon' => 'fa-cogs'],

        ]);

        $tres = DB::table('menus')->insertGetId(
            ['name' => 'Paquetes', 'app_id' => $idAppAdmin, 'icon' => 'fa-globe-americas', 'slug' => 'packages'] //3
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Lista', 'menu_id' => $tres, 'icon' => 'fa-bars'],
            ['name' => 'Grupos', 'menu_id' => $tres, 'icon' => 'fa-bars'],
            ['name' => 'Exportar Tarifas', 'menu_id' => $tres, 'icon' => 'fa-file-excel'],
            ['name' => 'Permisos', 'menu_id' => $tres, 'icon' => 'fa-unlock-alt'],
            ['name' => 'Highlights', 'menu_id' => $tres, 'icon' => 'fa-images'],
            ['name' => 'Políticas de cancelación', 'menu_id' => $tres, 'icon' => 'fa-business-time'],
        ]);

        $cuatro = DB::table('menus')->insertGetId(
            ['name' => 'Hoteles', 'app_id' => $idAppAdmin, 'icon' => 'fa-hotel', 'slug' => 'hotels'] //4
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Lista', 'menu_id' => $cuatro, 'icon' => 'fa-bars'],
            ['name' => 'Cadenas', 'menu_id' => $cuatro, 'icon' => 'fa-link'],
            ['name' => 'Amenidades', 'menu_id' => $cuatro, 'icon' => 'fa-futbol'],
            ['name' => 'Meals', 'menu_id' => $cuatro, 'icon' => 'fa-drumstick-bite'],
            ['name' => 'Tipos de Hotel', 'menu_id' => $cuatro, 'icon' => 'fa-donate'],
            ['name' => 'Tipos de Habitación', 'menu_id' => $cuatro, 'icon' => 'fa-bed'],
            ['name' => 'Categoria Hotel', 'menu_id' => $cuatro, 'icon' => 'fa-share-alt'],
            ['name' => 'Channel', 'menu_id' => $cuatro, 'icon' => 'fa-list-alt'],
            ['name' => 'Suplementos', 'menu_id' => $cuatro, 'icon' => 'fa-list-alt'],
            ['name' => 'Orden Tarifario', 'menu_id' => $cuatro, 'icon' => 'fa-check-double'],
            ['name' => 'Configuración Markups', 'menu_id' => $cuatro, 'icon' => 'fa-cogs'],
            ['name' => 'Suscripcion Hyperguest', 'menu_id' => $cuatro, 'icon' => 'fa-cogs'],
        ]);

        $cinco = DB::table('menus')->insertGetId(
            ['name' => 'Trenes', 'app_id' => $idAppAdmin, 'icon' => 'fa-train', 'slug' => 'trains'] //5
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Lista', 'menu_id' => $cinco, 'icon' => 'fa-bars'],
            ['name' => 'Rutas', 'menu_id' => $cinco, 'icon' => 'fa-subway'],
            ['name' => 'Clases', 'menu_id' => $cinco, 'icon' => 'fa-external-link-alt'],
            ['name' => 'Usuarios', 'menu_id' => $cinco, 'icon' => 'fa-user'],
            ['name' => 'Politicas de cancelación', 'menu_id' => $cinco, 'icon' => 'fa-business-time'],
        ]);


        $seis = DB::table('menus')->insertGetId(
            ['name' => 'Clientes', 'app_id' => $idAppAdmin, 'icon' => 'fa-globe', 'slug' => 'clients'] //6
        );

        $siete = DB::table('menus')->insertGetId(
            ['name' => 'Fotos', 'app_id' => $idAppAdmin, 'icon' => 'fa-images', 'slug' => 'photos'] //7
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Lista', 'menu_id' => $siete, 'icon' => 'fa-bars'],
        ]);


        $ocho = DB::table('menus')->insertGetId(
            ['name' => 'Tickets', 'app_id' => $idAppAdmin, 'icon' => 'fa-ticket-alt', 'slug' => 'tickets'] //8
        );

        $nueve = DB::table('menus')->insertGetId(
            ['name' => 'Auditoria', 'app_id' => $idAppAdmin, 'icon' => 'fa-file-signature'] //9
        );

        DB::table('sub_menus')->insert([
            ['name' => 'Paquetes', 'menu_id' => $nueve, 'icon' => 'fa-folder'],
            ['name' => 'Clientes', 'menu_id' => $nueve, 'icon' => 'fa-folder'],
            ['name' => 'Servicios', 'menu_id' => $nueve, 'icon' => 'fa-folder'],
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('apps')->whereNotNull('name')->delete();
    }
}
