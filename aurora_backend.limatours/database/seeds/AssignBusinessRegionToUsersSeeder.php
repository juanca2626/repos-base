<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignBusinessRegionToUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener IDs de todos los usuarios
        $userIds = User::pluck('id')->toArray();

        // Preparar datos para inserción masiva
        $data = array_map(function($userId) {
            return [
                'business_region_id' => 1,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $userIds);

        // Insertar en lote (ignorando duplicados si existen)
        DB::table('business_region_user')->insertOrIgnore($data);

        $this->command->info('Asignación masiva completada para '.count($userIds).' usuarios.');
    }
}
