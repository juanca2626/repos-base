<?php

use Illuminate\Database\Seeder;
use App\HotelBackup;
use App\Hotel;
use Illuminate\Support\Facades\DB;

class HotelBackupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Años a procesar
        $years = ['2025', '2026'];

        // Obtener todos los IDs de hoteles existentes
        $hotelIds = Hotel::pluck('id')->toArray();

        if (empty($hotelIds)) {
            $this->command->error('No hay hoteles registrados en la base de datos!');
            return;
        }

        // Desactivar revisión de claves foráneas temporalmente para mejor performance
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Contadores para estadísticas (por año)
        $stats = [
            '2025' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            '2026' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        ];

        foreach ($years as $year) {
            $this->command->info("Procesando datos para el año {$year}...");

            foreach ($hotelIds as $hotelId) {
                // Verificar si ya existe un registro para este hotel y año
                $existing = HotelBackup::where([
                    'hotel_id' => $hotelId,
                    'year' => $year,
                ])->first();

                if ($existing) {
                    if ($existing->value != 0) {
                        // Actualizar solo si el valor es diferente de 0
                        $existing->update(['value' => 0]);
                        $stats[$year]['updated']++;
                    } else {
                        $stats[$year]['skipped']++;
                    }
                    continue;
                }

                // Crear nuevo registro
                HotelBackup::create([
                    'year' => $year,
                    'value' => 0,
                    'hotel_id' => $hotelId,
                ]);
                $stats[$year]['created']++;
            }
        }

        // Reactivar revisión de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Mostrar resumen
        foreach ($years as $year) {
            $this->command->info("Resumen para el año {$year}:");
            $this->command->info(" - Registros creados: " . $stats[$year]['created']);
            $this->command->info(" - Registros actualizados: " . $stats[$year]['updated']);
            $this->command->info(" - Registros sin cambios: " . $stats[$year]['skipped']);
            $this->command->info("Total procesados: " .
                ($stats[$year]['created'] + $stats[$year]['updated'] + $stats[$year]['skipped']));
            $this->command->line('');
        }
    }
}
