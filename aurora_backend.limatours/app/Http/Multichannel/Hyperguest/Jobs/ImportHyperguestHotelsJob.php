<?php

namespace App\Http\Multichannel\Hyperguest\Jobs;

use App\HyperguestHotelImportBatch;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;
use App\Http\Multichannel\Hyperguest\Services\HotelSyncService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportHyperguestHotelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $batchId;
    private $propertyIds = [];

    public function __construct(int $batchId, array $propertyIds)
    {
        $this->batchId = $batchId;
        $this->propertyIds = $propertyIds;

        // Define the queue name solo si no es sync
        // Con sync, los jobs se ejecutan inmediatamente y no necesitan cola
        if (config('queue.default') !== 'sync') {
            $this->queue = 'sync_hyperguest_pull';
        }
    }

    public function handle(HotelSyncService $service)
    {
        $batch = HyperguestHotelImportBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        try {
            // Actualizar estado a 'processing'
            $batch->status = 'processing';
            $batch->save();

            // Convertir property_ids a enteros si son strings numéricos
            // El HotelSyncService espera que hotel_ids coincidan con hotelId de Hyperguest
            $hotelIds = array_map(function ($id) {
                // Asegurar que sean enteros o strings según lo que espera Hyperguest
                return is_numeric($id) ? (int)$id : $id;
            }, $this->propertyIds);

            // Ejecutar sincronización con los property_ids
            $service->sync(
                0, // limit: 0 = sin límite
                [], // countries: vacío, ya filtramos por hotel_ids
                0, // from: 0
                $hotelIds, // hotel_ids: los property_ids seleccionados
                $this->batchId // batch_id: el ID del batch
            );

            // Buscar los hoteles creados/actualizados y actualizar el batch
            $this->updateBatchAfterSync($batch);

            // Actualizar estado a 'completed'
            $batch->status = 'completed';
            $batch->save();

        } catch (Exception $e) {
            $this->handleError($batch, $e, 'Exception');
        } catch (GuzzleException $e) {
            $this->handleError($batch, $e, 'GuzzleException');
        } catch (\Throwable $e) {
            $this->handleError($batch, $e, 'Throwable');
        }
    }

    private function updateBatchAfterSync(HyperguestHotelImportBatch $batch): void
    {
        $hotelResults = [];
        $completedCount = 0;
        $failedCount = 0;

        foreach ($this->propertyIds as $propertyId) {
            // Buscar el hotel creado/actualizado por el código del canal
            $hotelId = DB::table('channel_hotel')
                ->where('code', $propertyId)
                ->where('channel_id', ChannelHyperguestConfig::CHANNEL_ID) // CHANNEL_ID = 6
                ->value('hotel_id');

            if ($hotelId) {
                $hotelResults[$propertyId] = $hotelId;
                $completedCount++;
            } else {
                $hotelResults[$propertyId] = null;
                $failedCount++;
            }
        }

        // Actualizar el batch con los resultados
        $batch->hotel_results = $hotelResults;
        $batch->completed_hotels = $completedCount;
        $batch->failed_hotels = $failedCount;
        $batch->save();
    }

    private function handleError(HyperguestHotelImportBatch $batch, \Throwable $e, string $errorType): void
    {
        $errorMessage = $e->getMessage();
        // Actualizar estado a 'failed' y guardar el mensaje de error
        $batch->status = 'failed';
        $batch->error_message = substr($errorMessage, 0, 500); // Limitar tamaño del mensaje
        $batch->save();
    }
}

