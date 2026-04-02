<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\ReportHyperguestImport;
use App\ReportHyperguests;
use App\ReportHyperguestDetails;
use Maatwebsite\Excel\Facades\Excel;

class HyperguestImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Uso:
     * php artisan hyperguest:import {month} {year} {fee} {filename}
     */
    protected $signature = 'hyperguest:import {month} {year} {fee} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar archivo Hyperguest desde public/';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->argument('month');
        $year = $this->argument('year');
        $fee = $this->argument('fee');
        $filename = $this->argument('filename');

        $filePath = public_path($filename);

        if (!file_exists($filePath)) {
            $this->error("El archivo {$filename} no existe en /public");
            return 1; // error
        }

        // Si ya existe información para el mes/año, eliminarla (report + detalles)
        // Forzar eliminación para evitar conflictos con restricciones UNIQUE
        $existingReports = ReportHyperguests::withTrashed()->where('month', $month)->where('year', $year)->get();
        if ($existingReports->isNotEmpty()) {
            $ids = $existingReports->pluck('id')->toArray();
            if (!empty($ids)) {
                // Forzar eliminación de detalles relacionados (si falla, borrar normalmente)
                try {
                    ReportHyperguestDetails::withTrashed()->whereIn('report_hyperguest_id', $ids)->forceDelete();
                } catch (\BadMethodCallException $e) {
                    ReportHyperguestDetails::whereIn('report_hyperguest_id', $ids)->delete();
                }

                // Forzar eliminación de reportes (con fallback)
                foreach ($existingReports as $report) {
                    try {
                        $report->forceDelete();
                    } catch (\BadMethodCallException $e) {
                        $report->delete();
                    }
                }
                $this->info("Registros eliminados permanentemente para {$month}/{$year} (ids: " . implode(',', $ids) . ")");
            }
        }

        try {
            $import = new ReportHyperguestImport($month, $year, $fee);
            $import->import($filePath);

            if (method_exists($import, 'failures') && $import->failures()->count() > 0) {
                $this->error("Error en la importación: " . $import->failures()[0]->errors()[0]);
                return 1;
            }

            $this->info("✅ Importación ejecutada correctamente.");
            return 0;

        } catch (\Exception $ex) {
            $this->error("❌ Error: " . $ex->getMessage());
            return 1;
        }
    }
}
