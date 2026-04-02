<?php

namespace App\Jobs;

use App\Http\Hyperguest\Traits\BulkInsertUpdateTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRatePushHyperguest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BulkInsertUpdateTrait;

    /**
     * Datos a procesar
     */
    protected $arrayUpdates;

    /**
     * Crea una nueva instancia del Job.
     */
    public function __construct(array $arrayUpdates)
    {
        $this->arrayUpdates = $arrayUpdates;
    }

    /**
     * Ejecuta el Job.
     */
    public function handle()
    {
        try {
            // Inserciones
            if (!empty($this->arrayUpdates["rates"]["inserts"])) {
                $inserts = $this->arrayUpdates["rates"]["inserts"];
                $fields = [
                    'rates_plans_calendarys_id',
                    'num_adult',
                    'num_child',
                    'num_infant',
                    'price_adult',
                    'price_child',
                    'price_infant',
                    'price_extra',
                    'price_total'
                ];
                $this->bulkInsertRaw(
                    'rates',
                    $fields,
                    $inserts,
                    500
                );
            }

            // Actualizaciones
            if (!empty($this->arrayUpdates["rates"]["updates"])) {
                $updates = $this->arrayUpdates["rates"]["updates"];
                $fields = [
                    'num_adult',
                    'num_child',
                    'num_infant',
                    'price_adult',
                    'price_child',
                    'price_infant',
                    'price_extra',
                    'price_total'
                ];
                $this->bulkUpdateCase(
                    'rates',
                    $updates,
                    $fields,
                    500
                );
            }

        } catch (Exception $e) {
            throw $e; // Laravel reintentará el Job si está configurado
        }
    }
}
