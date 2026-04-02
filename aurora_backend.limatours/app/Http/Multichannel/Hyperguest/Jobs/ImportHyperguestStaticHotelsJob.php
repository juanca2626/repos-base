<?php

namespace App\Http\Multichannel\Hyperguest\Jobs;

use App\Http\Multichannel\Hyperguest\Services\HyperguestGatewayService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportHyperguestStaticHotelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $country;
    private $queueName;
    private $hotelIds;

    public function __construct(?string $country = null, ?string $queueName = null, ?array $hotelIds = null)
    {
        $this->country = $country;
        $this->queueName = $queueName;
        $this->hotelIds = $hotelIds;

        // Define the queue name
        if (config('queue.default') !== 'sync') {
            $this->queue = $queueName ?? 'hyperguest_static_import';
        }
    }

    public function handle(HyperguestGatewayService $service)
    {
        try {
            // Intentar primero con getHotelsFilter (endpoint principal)
            $hotels = [];
            $useStaticEndpoint = false;

            try {
                $countries = $this->country ? [$this->country] : [];
                $hotelIds = $this->hotelIds ?? [];

                $response = $service->getHotelsFilter([
                    'channelIntegration' => [
                        'channel' => 'hyperguest',
                        'type' => 'PULL',
                        'version' => 'v1',
                        'isActive' => true,
                    ],
                    'countries' => $countries,
                    'hotel_ids' => $hotelIds,
                ]);

                // Validar respuesta
                if (!isset($response['result']['data']) || !is_array($response['result']['data'])) {
                    throw new Exception('Respuesta inválida del API de Hyperguest: estructura de datos incorrecta.');
                }

                $hotels = $response['result']['data'];
            } catch (Exception $e) {
                $useStaticEndpoint = true;
            }

            // Si falla getHotelsFilter, usar el endpoint estático como respaldo
            if ($useStaticEndpoint || empty($hotels)) {
                try {
                    $hotels = $service->getStaticHotels($this->country, $this->hotelIds);
                    // Transformar para que coincidan con el formato del microservicio de hoteles
                    $hotels = array_map(function($hotel) {
                        return [
                            'hotelId' => $hotel['hotel_id'],
                            'name' => $hotel['name'],
                            'country' => $hotel['country_name'] ?? $hotel['country'] ?? null,
                            'city' => $hotel['city_name'] ?? $hotel['city'] ?? null,
                            'cityId' => $hotel['city_Id'] ?? $hotel['city_id'] ?? null,
                            'region' => $hotel['region'] ?? null,
                            'chainId' => $hotel['chain_id'] ?? null,
                            'chainName' => $hotel['chain_name'] ?? null,
                            'lastUpdated' => $hotel['last_updated'] ?? null,
                            'version' => $hotel['version'] ?? null,
                        ];
                    }, $hotels);
                } catch (Exception $e) {
                    throw new Exception('No se pudo obtener hoteles ni desde getHotelsFilter ni desde el endpoint estático: ' . $e->getMessage());
                }
            }

            $totalHotels = count($hotels);

            // Obtener todos los hotel_ids de los hoteles recibidos
            $hotelIdsFromApi = array_filter(array_map(function($hotel) {
                return $hotel['hotelId'] ?? null;
            }, $hotels));

            // Obtener todos los hoteles existentes en una sola consulta
            $existingHotels = [];
            if (!empty($hotelIdsFromApi)) {
                $existingHotelsRaw = DB::table('hyperguest_hotels')
                    ->whereIn('hotel_id', $hotelIdsFromApi)
                    ->get()
                    ->keyBy('hotel_id');

                foreach ($existingHotelsRaw as $hotel) {
                    $existingHotels[$hotel->hotel_id] = [
                        'id' => $hotel->id,
                        'hotelId' => $hotel->hotel_id,
                        'name' => $hotel->name,
                        'country' => $hotel->country,
                        'city' => $hotel->city,
                        'cityId' => $hotel->city_id,
                        'region' => $hotel->region,
                        'chainId' => $hotel->chain_id,
                        'chainName' => $hotel->chain_name,
                        'lastUpdated' => $hotel->last_updated ? Carbon::parse($hotel->last_updated) : null,
                        'version' => $hotel->version,
                        'status' => $hotel->status,
                    ];
                }
            }

            // Procesar cada hotel e insertar/actualizar inmediatamente
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = 0;
            $now = now();

            // Procesar cada hotel y preparar datos
            foreach ($hotels as $hotelData) {
                try {
                    $result = $this->prepareHotelData($hotelData, $service, $existingHotels, $now);

                    if ($result['action'] === 'insert') {
                        // Insertar inmediatamente
                        $insertedId = $this->insertHotel($result['data']);
                        if ($insertedId) {
                            $created++;
                            // Actualizar el array de existingHotels para evitar duplicados en el mismo proceso
                            $existingHotels[$result['data']['hotel_id']] = [
                                'id' => $insertedId,
                                'hotelId' => $result['data']['hotel_id'],
                                'name' => $result['data']['name'],
                                'country' => $result['data']['country'],
                                'city' => $result['data']['city'],
                                'cityId' => $result['data']['city_id'],
                                'region' => $result['data']['region'],
                                'chainId' => $result['data']['chain_id'],
                                'chainName' => $result['data']['chain_name'],
                                'lastUpdated' => $result['data']['last_updated'] ? Carbon::parse($result['data']['last_updated']) : null,
                                'version' => $result['data']['version'],
                                'status' => $result['data']['status'],
                            ];
                        }
                    } elseif ($result['action'] === 'update') {
                        // Actualizar inmediatamente
                        $updatedId = $this->updateHotel($result['data']);
                        if ($updatedId) {
                            $updated++;
                        }
                    } else {
                        $skipped++;
                    }
                } catch (Exception $e) {
                    $errors++;
                }
            }
        } catch (GuzzleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function prepareHotelData(array $hotelData, HyperguestGatewayService $service, array $existingHotels, Carbon $now): array
    {
        $hotelId = $hotelData['hotelId'] ?? null;

        if (!$hotelId) {
            throw new Exception('Hotel sin hotelId');
        }

        // Convertir lastUpdated del API a Carbon
        $apiLastUpdated = null;
        if (isset($hotelData['lastUpdated']) && $hotelData['lastUpdated']) {
            try {
                $apiLastUpdated = Carbon::parse($hotelData['lastUpdated']);
            } catch (Exception $e) {
            }
        }

        $existingHotel = $existingHotels[$hotelId] ?? null;

        // Si el hotel existe, validar last_updated ANTES de consultar el detalle
        if ($existingHotel) {
            $needsUpdate = false;

            if ($apiLastUpdated && $existingHotel['lastUpdated']) {
                // Normalizar ambas fechas al mismo formato para comparar
                $existingLastUpdated = $existingHotel['lastUpdated'];
                if ($existingLastUpdated instanceof Carbon) {
                    $existingLastUpdatedFormatted = $existingLastUpdated->format('Y-m-d H:i:s');
                } else {
                    $existingLastUpdatedFormatted = Carbon::parse($existingLastUpdated)->format('Y-m-d H:i:s');
                }

                $apiLastUpdatedFormatted = $apiLastUpdated->format('Y-m-d H:i:s');

                // Si son diferentes, necesita actualización
                if ($apiLastUpdatedFormatted !== $existingLastUpdatedFormatted) {
                    $needsUpdate = true;
                }
                // Si son iguales, no actualizar (omitir sin consultar detalle)
            } elseif ($apiLastUpdated && !$existingHotel['lastUpdated']) {
                // Si el API tiene fecha pero la BD no, actualizar
                $needsUpdate = true;
            } elseif (!$apiLastUpdated) {
                // Si el API no tiene fecha, no actualizar
                $needsUpdate = false;
            }

            // Si no necesita actualización, retornar skip sin consultar el detalle
            if (!$needsUpdate) {
                return ['action' => 'skip', 'data' => null];
            }
        }

        // Solo consultar el detalle si el hotel no existe o si necesita actualización
        // Obtener el detalle del hotel para extraer el status
        $status = 'Approved'; // Default
        try {
            // Intentar primero con getHotelDetail (método principal)
            try {
                $detailResponse = $service->getHotelDetail([
                    'channelIntegration' => [
                        'channel' => 'hyperguest',
                        'type' => 'PULL',
                        'version' => 'v1',
                        'isActive' => true
                    ],
                    'hotelId' => $hotelId
                ]);

                if (isset($detailResponse['result']['data']['status'])) {
                    $status = $detailResponse['result']['data']['status'];
                }
            } catch (Exception $e) {
                // Si falla getHotelDetail, usar el endpoint estático como respaldo
                $hotelDetail = $service->getStaticHotelDetail($hotelId);

                if ($hotelDetail !== null && isset($hotelDetail['status'])) {
                    $status = $hotelDetail['status'];
                }
            }
        } catch (Exception $e) {
            // Mantener el valor por defecto si falla
        }

        // Preparar datos base
        $data = [
            'hotel_id' => $hotelId,
            'name' => $hotelData['name'] ?? null,
            'country' => $hotelData['country'] ?? null,
            'city' => $hotelData['city'] ?? null,
            'city_id' => $hotelData['cityId'] ?? null,
            'region' => $hotelData['region'] ?? null,
            'chain_id' => $hotelData['chainId'] ?? null,
            'chain_name' => $hotelData['chainName'] ?? null,
            'last_updated' => $apiLastUpdated ? $apiLastUpdated->format('Y-m-d H:i:s') : null,
            'version' => $hotelData['version'] ?? null,
            'status' => $status,
        ];

        if ($existingHotel) {
            // Ya validamos que necesita actualización arriba
            $data['id'] = $existingHotel['id'];
            $data['updated_at'] = $now->format('Y-m-d H:i:s');
            return ['action' => 'update', 'data' => $data];
        } else {
            // Crear nuevo registro
            $data['created_at'] = $now->format('Y-m-d H:i:s');
            $data['updated_at'] = $now->format('Y-m-d H:i:s');
            return ['action' => 'insert', 'data' => $data];
        }
    }

    private function insertHotel(array $hotelData): ?int
    {
        try {
            $id = DB::table('hyperguest_hotels')->insertGetId($hotelData);

            return $id;
        } catch (Exception $e) {
            return $e;
        }
    }

    private function updateHotel(array $hotelData): ?int
    {
        try {
            $id = $hotelData['id'];
            unset($hotelData['id']); // Remover id del array de datos a actualizar

            $updated = DB::table('hyperguest_hotels')
                ->where('id', $id)
                ->update($hotelData);

            if ($updated) {
                return $id;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}

