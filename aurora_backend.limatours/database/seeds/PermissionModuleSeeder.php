<?php
// database/seeders/ModuleSeeder.php
use Illuminate\Database\Seeder;
use App\PermissionModule;

class PermissionModuleSeeder extends Seeder
{
    public function run()
    {
        // Top-level principales
        PermissionModule::firstOrCreate(
            ['slug' => 'services'],
            ['name' => 'Servicios', 'kind' => 'primary', 'sort_order' => 1]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'hotels'],
            ['name' => 'Hoteles', 'kind' => 'primary', 'sort_order' => 2]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'packages'],
            ['name' => 'Paquetes', 'kind' => 'primary', 'sort_order' => 3]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'users'],
            ['name' => 'Usuarios', 'kind' => 'primary', 'sort_order' => 4]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'clients'],
            ['name' => 'Clientes', 'kind' => 'primary', 'sort_order' => 5]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'photos'],
            ['name' => 'Fotos', 'kind' => 'primary', 'sort_order' => 6]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'tickets'],
            ['name' => 'Tickets', 'kind' => 'primary', 'sort_order' => 7]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'audit'],
            ['name' => 'Auditoria', 'kind' => 'primary', 'sort_order' => 8]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'organizational-management'],
            ['name' => 'Gestión Organizacional', 'kind' => 'primary', 'sort_order' => 9]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'suppliers'],
            ['name' => 'Proveedores', 'kind' => 'primary', 'sort_order' => 10]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'bookings'],
            ['name' => 'Reservas', 'kind' => 'primary', 'sort_order' => 11]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'masi'],
            ['name' => 'Masi', 'kind' => 'primary', 'sort_order' => 12]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'master-services'],
            ['name' => 'Servicios maestros', 'kind' => 'primary', 'sort_order' => 13]
        );

        // Hijos del contenedor 'auxiliares'
        $children = [
            ['slug' => 'languages', 'name' => 'Idiomas', 'sort_order' => 14],
            ['slug' => 'ubigeo', 'name' => 'Ubigeo', 'sort_order' => 15],
            ['slug' => 'gallery', 'name' => 'Galería', 'sort_order' => 16],
            ['slug' => 'coins', 'name' => 'Monedas', 'sort_order' => 17],
            ['slug' => 'translate', 'name' => 'Traducciones', 'sort_order' => 18],
            ['slug' => 'units', 'name' => 'Unidades de medida', 'sort_order' => 19],
            ['slug' => 'units-duration', 'name' => 'Unidades de duración', 'sort_order' => 20],
            ['slug' => 'markets', 'name' => 'Mercados', 'sort_order' => 21],
            ['slug' => 'class', 'name' => 'Clases', 'sort_order' => 22],
            ['slug' => 'class-virtual', 'name' => 'Clases virtuales', 'sort_order' => 23],
            ['slug' => 'physical-intensity', 'name' => 'Intensidad fisica', 'sort_order' => 24],
            ['slug' => 'asked-questions', 'name' => 'Preguntas frecuentes', 'sort_order' => 25],
        ];

        foreach ($children as $c) {
            PermissionModule::firstOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'kind' => 'auxiliary', 'sort_order' => $c['sort_order']]
            );
        }

        PermissionModule::firstOrCreate(
            ['slug' => 'permission_roles'],
            ['name' => 'Permisos & Roles', 'kind' => 'primary', 'sort_order' => 26]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'aurora_front'],
            ['name' => 'Aurora - frontend', 'kind' => 'primary', 'sort_order' => 27]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'trains'],
            ['name' => 'Trenes', 'kind' => 'primary', 'sort_order' => 28]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'aurorax'],
            ['name' => 'Aurora X - Extension', 'kind' => 'primary', 'sort_order' => 29]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'operation'],
            ['name' => 'Operaciones', 'kind' => 'primary', 'sort_order' => 30]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'negotiations'],
            ['name' => 'Negociaciones', 'kind' => 'primary', 'sort_order' => 31]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'files'],
            ['name' => 'Files', 'kind' => 'primary', 'sort_order' => 32]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'regions'],
            ['name' => 'Regionalización', 'kind' => 'primary', 'sort_order' => 33]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'customer-service'],
            ['name' => 'Customer Service', 'kind' => 'primary', 'sort_order' => 35]
        );

        PermissionModule::firstOrCreate(
            ['slug' => 'adventure'],
            ['name' => 'Aventura', 'kind' => 'primary', 'sort_order' => 36]
        );
    }
}
