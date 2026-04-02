<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportHyperguestsStatusSeeder extends Seeder
{
    /**
     * Marca todos los reportes existentes sin status (null) o con status PENDING
     * que no tienen total_rows (i.e., importados antes del sistema async) como COMPLETED.
     */
    public function run()
    {
        $affected = DB::table('report_hyperguests')
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 'PENDING');
            })
            ->whereNull('total_rows') // registros pre-async (sin tracking de filas)
            ->update([
                'status' => 'COMPLETED',
            ]);

        $this->command->info("ReportHyperguestsStatusSeeder: {$affected} registros actualizados a COMPLETED.");
    }
}
