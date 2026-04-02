<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PermissionDetailsSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = database_path('data/permission_details.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("❌ No se encontró el archivo: {$jsonPath}");
            return;
        }

        $records = json_decode(File::get($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('❌ JSON inválido: ' . json_last_error_msg());
            return;
        }

        if (empty($records)) {
            $this->command->warn('⚠️ El JSON no contiene registros.');
            return;
        }

        $this->command->info('🚀 Insertando permission_details desde JSON...');

        $now = now();
        $inserted = 0;

        foreach ($records as $row) {
            if (!isset($row['permission_id'], $row['permission_module_id'])) {
                $this->command->warn('Fila inválida: ' . json_encode($row));
                continue;
            }

            // evita duplicados por permission_id (ajusta si quieres permitir varios)
            $exists = DB::table('permission_details')
                ->where('permission_id', (int)$row['permission_id'])
                ->exists();

            if ($exists) {
                $this->command->warn("⏩ Ya existe permission_id {$row['permission_id']}, omitido.");
                continue;
            }

            DB::table('permission_details')->insert([
                'permission_id'        => (int)$row['permission_id'],
                'permission_module_id' => (int)$row['permission_module_id'],
                'created_at'           => $row['created_at'] ?? $now,
                'updated_at'           => $row['updated_at'] ?? $now,
            ]);

            $inserted++;
        }

        $this->command->info("✅ Seeder completado. Insertados: {$inserted}");
    }
}
