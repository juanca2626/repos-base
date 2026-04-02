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

class ProcessInventoryPushHyperguest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BulkInsertUpdateTrait;

    /**
     * Datos a procesar
     */
    protected $arrayUpdates;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $arrayUpdates)
    {
        $this->arrayUpdates = $arrayUpdates;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Insertar inventarios
            if (!empty($this->arrayUpdates["inventories"]["inserts"])) {
                $inserts = $this->arrayUpdates["inventories"]["inserts"];
                $fields = [
                    'rate_plan_rooms_id',
                    'date',
                    'day',
                    'inventory_num',
                    'locked'
                ];
                $this->bulkInsertRaw(
                    'inventories',
                    $fields,
                    $inserts,
                    500
                );
            }

            // Actualizar inventarios
            if (!empty($this->arrayUpdates["inventories"]["updates"])) {
                $updates = $this->arrayUpdates["inventories"]["updates"];
                $fields = [
                    'inventory_num',
                    'locked'
                ];
                $this->bulkUpdateCase(
                    'inventories',
                    $updates,
                    $fields,
                    500
                );
            }

            // Insertar calendarios
            if (!empty($this->arrayUpdates["calendarys"]["inserts"])) {
                $rawInserts = $this->arrayUpdates["calendarys"]["inserts"];
                $unique = [];

                // Ordenar
                usort($rawInserts, function ($a, $b) {
                    $dateComparison = strcmp($a['date'] ?? '', $b['date'] ?? '');
                    if ($dateComparison === 0) {
                        return strcmp($a['rates_plans_room_id'] ?? '', $b['rates_plans_room_id'] ?? '');
                    }
                    return $dateComparison;
                });

                // Eliminar duplicados
                foreach ($rawInserts as $item) {
                    $key = ($item['date'] ?? '') . '|' . ($item['rates_plans_room_id'] ?? '');

                    if (isset($unique[$key])) {
                        $existing = $unique[$key]; // Entrada existente

                        // Fusionar campos, priorizando valores no nulos de $item
                        $merged = array_merge($existing, array_filter($item, function ($value) {
                            return $value !== null;  // Retain non-null values only
                        }));

                        // Verificar si alguno de los estados tiene ?1? y priorizarlo en la última entrada
                        $this->prioritizeRestrictions($merged, $existing); // Prioriza restricciones si ya existen

                        $unique[$key] = $merged;  // Asignar el resultado mergeado de vuelta al array único
                    } else {
                        // Si no hay entrada existente, simplemente insertar la nueva fila
                        $unique[$key] = $item;
                    }
                }

                $inserts = array_values($unique); // Reindexar el array de inserciones
                $fields = [
                    'rates_plans_room_id',
                    'date',
                    'min_length_stay',
                    'max_length_stay',
                    'min_ab_offset',
                    'max_ab_offset',
                    'restriction_status',
                    'restriction_arrival',
                    'restriction_departure'
                ];
                $this->bulkInsertRaw(
                    'rates_plans_calendarys',
                    $fields,
                    $inserts,
                    500
                );
            }

            // Actualizar calendarios
            if (!empty($this->arrayUpdates["calendarys"]["updates"])) {
                $updates = $this->arrayUpdates["calendarys"]["updates"];
                $fields = [
                    'min_length_stay',
                    'max_length_stay',
                    'min_ab_offset',
                    'max_ab_offset',
                    'restriction_status',
                    'restriction_arrival',
                    'restriction_departure'
                ];
                $this->bulkUpdateCase(
                    'rates_plans_calendarys',
                    $updates,
                    $fields,
                    500
                );
            }

        } catch (Exception $e) {
            throw $e; // Laravel reintentará el Job si está configurado
        }
    }


    // Método auxiliar para priorizar restricciones
    private function prioritizeRestrictions(&$merged, $existing)
    {
        if (isset($existing['restriction_status']) && $existing['restriction_status'] == 1) {
            $merged['restriction_status'] = 1;
        }
        if (isset($existing['restriction_arrival']) && $existing['restriction_arrival'] == 1) {
            $merged['restriction_arrival'] = 1;
        }
        if (isset($existing['restriction_departure']) && $existing['restriction_departure'] == 1) {
            $merged['restriction_departure'] = 1;
        }
    }
}
