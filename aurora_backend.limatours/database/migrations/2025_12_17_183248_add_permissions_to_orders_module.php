<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsToOrdersModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // CONFIGURACIÓN:
        $modulePrefix = 'mfmyorders'; // Prefijo para el slug (ej: orders.manage_templates)
        $moduleName   = 'Orders'; // Prefijo para el nombre (ej: Orders: Gestión de Plantillas)
        $moduleId     = 27;       // ID fijo para permission_details

        // Definimos los grupos de roles para facilitar la asignación
        $allAdmins = ['admin', 'km', 'reg', 'ots']; // Admin, KAM, Regional, OTS
        $jefes     = ['js', 'jso', 'jope', 'jnp'];  // Jefes
        $spec      = ['ej'];                        // Especialista

        // LISTA DE PERMISOS Y ASIGNACIÓN DE ROLES
        $permissionsList = [
            [
                'slug' => 'manage_templates',
                'desc' => 'Gestión de Plantillas',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'filter_advanced',
                'desc' => 'Ver filtros de Mercado y Equipo/Todos',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'bulk_actions',
                'desc' => 'Ver barra de acciones masivas',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'bulk_status',
                'desc' => 'Acción masiva: Cambiar estado',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'bulk_followup',
                'desc' => 'Acción masiva: Enviar seguimiento',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'export_excel',
                'desc' => 'Botón inferior Descargar Listado',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'send_template',
                'desc' => 'Ícono Avión (Enviar plantilla rápido)',
                'roles' => $allAdmins // Solo admins/ots
            ],
            [
                'slug' => 'view_history',
                'desc' => 'Ícono Reloj (Historial de seguimientos)',
                'roles' => $allAdmins // Solo admins/ots
            ],
            [
                'slug' => 'view_mail',
                'desc' => 'Opción Ver último correo',
                'roles' => array_merge($allAdmins, $jefes, $spec) // TODOS
            ],
            [
                'slug' => 'change_received_at',
                'desc' => 'Opción Cambiar fecha, última comunicación',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'change_status',
                'desc' => 'Opción Cambiar estado (de Seguimiento)',
                'roles' => $allAdmins // Solo admins/ots
            ],
            [
                'slug' => 'view_cancellation',
                'desc' => 'Opción Razón de cancelación',
                'roles' => $allAdmins // Solo admins/ots
            ],
            [
                'slug' => 'reassign',
                'desc' => 'Opción Reasignar pedido',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'edit_order',
                'desc' => 'Opción Editar pedido (Datos básicos)',
                'roles' => array_merge($allAdmins, $spec) // Admins + EJ, No jefes
            ],
            [
                'slug' => 'edit_response',
                'desc' => 'Opción Editar respuesta (Fechas SLA)',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'edit_observation',
                'desc' => 'Opción Observaciones',
                'roles' => array_merge($allAdmins, $spec) // Admins + EJ, No jefes
            ],
            [
                'slug' => 'cancel',
                'desc' => 'Opción Anular pedido',
                'roles' => array_merge($allAdmins, $jefes)
            ],
            [
                'slug' => 'bds',
                'desc' => 'Visualización de la opc. Concretado BDS',
                'roles' => ['admin', 'km', 'reg'] // Solo estos 3, OTS NO
            ],
        ];

        // PROCESO DE CREACIÓN
        foreach ($permissionsList as $item) {
            $fullSlug = $modulePrefix . '.' . $item['slug'];

            // Verificar si ya existe para evitar duplicados
            if (Permission::where('slug', $fullSlug)->first() === null) {

                // 1. Crear el Permiso
                $permission = Permission::create([
                    'name'        => $moduleName . ': ' . $item['desc'],
                    'slug'        => $fullSlug,
                    'description' => $item['desc'],
                ]);

                // 2. Insertar en tabla intermedia 'permission_details'
                // Usamos DB::table directo porque es una tabla pivote custom sin modelo aparente
                DB::table('permission_details')->insert([
                    'permission_id'        => $permission->id,
                    'permission_module_id' => $moduleId
                ]);

                // 3. Asignar a Roles correspondientes
                if (!empty($item['roles'])) {
                    $rolesToAttach = Role::whereIn('slug', $item['roles'])->get();
                    foreach ($rolesToAttach as $role) {
                        $role->attachPermission($permission);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modulePrefix = 'mfmyorders';

        // Buscamos todos los permisos que empiecen con el prefijo
        $permissions = Permission::where('slug', 'LIKE', $modulePrefix . '.%')->get();

        foreach ($permissions as $permission) {
            // 1. Eliminar relación con permission_details
            DB::table('permission_details')->where('permission_id', $permission->id)->delete();

            // 2. Desvincular de roles (LaravelRoles suele manejar esto en cascade, pero por seguridad)
            $permission->roles()->sync([]);

            // 3. Eliminar permiso
            $permission->delete();
        }
    }
}
