<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Multichannel\Hyperguest\Traits\HotelSyncTrait;
use App\Http\Multichannel\Hyperguest\Services\HyperguestGatewayService;
use App\Http\Multichannel\Hyperguest\Services\HotelSyncService;
use App\Http\Multichannel\Hyperguest\Jobs\ImportHyperguestHotelsJob;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;
use App\HyperguestHotelImportBatch;
use App\HyperguestHotel;
use App\ChannelHotel;
use App\Hotel;
use App\Country;
use Illuminate\Support\Facades\Auth;

class HyperguestHotelsController extends Controller
{
    use HotelSyncTrait;
    protected $hyperguestService;

    public function __construct(HyperguestGatewayService $hyperguestService)
    {
        $this->hyperguestService = $hyperguestService;
        // $this->middleware('permission:hotels.read')->only('search');
        // $this->middleware('permission:hotels.create')->only('importHotels');
    }

    public function search(Request $request)
    {

            $country_code = $request->input('country_code');

            // Buscar el país por código ISO para obtener su ID
            $country = Country::where('iso', $country_code)->first();
            $countryId = $country ? $country->id : null;

            // Cargar todos los hoteles del país para optimizar el matching
            // Excluir hoteles que ya tienen canal Hyperguest (channel_id=6, type=2)
            $countryHotels = [];
            if ($countryId) {
                $hotels = Hotel::select('id', 'name')
                    ->where('country_id', $countryId)
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->where('status', 1)
                    ->whereDoesntHave('channel', function($query) {
                        $query->where('channel_id', ChannelHyperguestConfig::CHANNEL_ID)
                              ->where('type', ChannelHyperguestConfig::TYPE_CHANNEL);
                    })
                    ->get();

                // Obtener todos los IDs de hoteles
                $hotelIds = $hotels->pluck('id')->toArray();

                // Cargar todos los canales de estos hoteles con un join
                $channelHotels = ChannelHotel::select('channel_hotel.hotel_id', 'channel_hotel.channel_id', 'channel_hotel.type', 'channels.name')
                    ->join('channels', 'channel_hotel.channel_id', '=', 'channels.id')
                    ->whereIn('channel_hotel.hotel_id', $hotelIds)
                    ->whereNull('channel_hotel.deleted_at')
                    ->whereNull('channels.deleted_at')
                    ->get()
                    ->groupBy('hotel_id');

                // Formatear hoteles con sus canales
                $countryHotels = $hotels->map(function($hotel) use ($channelHotels) {
                    $channels = [];
                    $hotelChannels = $channelHotels->get($hotel->id, collect());

                    foreach ($hotelChannels as $channelHotel) {
                        $channelName = $channelHotel->name;
                        $channelType = $channelHotel->type;

                        // Si es HYPERGUEST, agregar el tipo (PUSH o PULL)
                        if ($channelName === 'HYPERGUEST' && $channelType !== null) {
                            $channelName = ($channelType == 1 || $channelType == '1') ? 'HYPERGUEST PUSH' : 'HYPERGUEST PULL';
                        }

                        $channels[] = $channelName;
                    }

                    return [
                        'id' => $hotel->id,
                        'name' => $hotel->name,
                        'channels' => $channels
                    ];
                })->toArray();
            }

            // Obtener hoteles de la tabla hyperguest_hotels
            $query = HyperguestHotel::query();

            // Filtrar por país si se proporciona
            if ($country_code) {
                $query->where('country', $country_code);
            }

            $hyperguestHotels = $query->get();

            // Transformar hoteles de la BD al formato esperado
            $hotels = $hyperguestHotels->map(function ($hotel) {
                return [
                    'hotelId' => $hotel->hotel_id,
                    'name' => $hotel->name,
                    'country' => $hotel->country,
                    'city' => $hotel->city,
                    'cityId' => $hotel->city_id,
                    'region' => $hotel->region,
                    'chainId' => $hotel->chain_id,
                    'chainName' => $hotel->chain_name,
                    'lastUpdated' => $hotel->last_updated ? $hotel->last_updated->toIso8601String() : null,
                    'version' => $hotel->version,
                    'status' => $hotel->status,
                ];
            })->toArray();

            // Transformar hoteles para obtener los códigos
            $transformedHotels = $this->transformHotels($hotels);

            // Extraer todos los códigos (ids) de los hoteles y convertirlos a string
            // para que coincidan con cómo se guardan en channel_hotel.code
            $hotelCodes = array_filter(array_map(function ($hotel) {
                $code = $hotel['id'] ?? null;
                return $code !== null ? (string)$code : null;
            }, $transformedHotels));

            // Consulta optimizada: buscar todos los códigos que ya existen en channel_hotel
            $existingCodes = [];
            if (!empty($hotelCodes)) {
                $existingCodes = ChannelHotel::where('channel_id', ChannelHyperguestConfig::CHANNEL_ID) // CHANNEL 6
                    ->where('type', ChannelHyperguestConfig::TYPE_CHANNEL) // TYPE 2
                    ->whereIn('code', $hotelCodes)
                    ->pluck('code')
                    ->toArray();
            }

            // Los hoteles ya tienen el status desde la BD, no necesitamos obtenerlo del detalle
            $hotelsWithStatus = $transformedHotels;

            // Agregar campo exists_in_db a cada hotel
            $hotelsWithExistsFlag = array_map(function ($hotel) use ($existingCodes) {
                // Convertir el id a string para comparar con los códigos existentes
                $hotelCode = $hotel['id'] !== null ? (string)$hotel['id'] : null;
                $hotel['exists_in_db'] = $hotelCode !== null && in_array($hotelCode, $existingCodes);
                return $hotel;
            }, $hotelsWithStatus);

            // Filtrar solo los hoteles que NO existen en la BD (exists_in_db = false)
            $hotelsFiltered = array_values(array_filter($hotelsWithExistsFlag, function ($hotel) {
                return !($hotel['exists_in_db'] ?? false);
            }));

            // Agregar campo matches (coincidencias de nombres) a cada hotel
            // Pasar la lista de hoteles del país para optimizar el matching
            $hotelsWithMatches = array_map(function ($hotel) use ($countryHotels) {
                $hotelName = $hotel['name'] ?? '';
                $hotel['matches'] = $this->findNameMatches($hotelName, $countryHotels);
                return $hotel;
            }, $hotelsFiltered);

            $count = count($hotelsWithMatches);

            return Response::json([
                'success' => true,
                'data' => $hotelsWithMatches,
                'count' => $count
            ]);


    }

    private function transformHotels(array $hotelsData): array
    {
        return array_values(array_map(function ($hotel) {
            return [
                'id' => $hotel['hotelId'] ?? null,
                'name' => $hotel['name'] ?? '',
                'country' => $hotel['country'] ?? $hotel['country_name'] ?? $hotel['country_iso'] ?? null,
                'city' => $hotel['city'] ?? $hotel['city_name'] ?? null,
                'region' => $hotel['region'] ?? null,
                'chain_name' => $hotel['chainName'] ?? null,
                'status' => $hotel['status'] ?? null, // Intentar obtener status de la respuesta inicial
            ];
        }, $hotelsData));
    }

    public function importHotels(Request $request)
    {
        try {
            $hotels = $request->input('hotels', []);
            $user_id = Auth::id();

            if (empty($hotels) || !is_array($hotels)) {
                return Response::json([
                    'success' => false,
                    'message' => 'No se proporcionaron hoteles para importar'
                ], 400);
            }

            // Extraer property_ids y determinar el país principal del lote
            $propertyIds = [];
            $countries = [];

            foreach ($hotels as $hotelData) {
                $propertyId = $hotelData['property_id'] ?? null;
                if ($propertyId) {
                    $propertyIds[] = $propertyId;
                }

                $country = $hotelData['country'] ?? null;
                if ($country) {
                    $countries[$country] = ($countries[$country] ?? 0) + 1;
                }
            }

            if (empty($propertyIds)) {
                return Response::json([
                    'success' => false,
                    'message' => 'No se encontraron property_ids válidos en los hoteles seleccionados'
                ], 400);
            }

            // Determinar el país principal (el que más se repite)
            $mainCountry = !empty($countries) ? array_keys($countries, max($countries))[0] : null;

            // Procesar canales de hoteles asociados antes de crear el batch
            foreach ($hotels as $hotelData) {
                $propertyId = $hotelData['property_id'] ?? null;
                $associatedHotels = $hotelData['associated_hotels'] ?? [];

                if ($propertyId && !empty($associatedHotels)) {
                    foreach ($associatedHotels as $dbHotelId) {
                        // Buscar si existe un channel con channel_id = 6 y type = 1
                        $channelHotel = ChannelHotel::where('hotel_id', $dbHotelId)
                            ->where('channel_id', ChannelHyperguestConfig::CHANNEL_ID)
                            ->where('type', 1)
                            ->first();

                        if ($channelHotel) {
                            // Si existe con type = 1, cambiar a type = 2
                            $channelHotel->type = ChannelHyperguestConfig::TYPE_CHANNEL; // 2
                            $channelHotel->code = (string)$propertyId; // Usar el property_id como código
                            $channelHotel->save();
                        } else {
                            // Si no existe, verificar si existe con otro type
                            $existingChannel = ChannelHotel::where('hotel_id', $dbHotelId)
                                ->where('channel_id', ChannelHyperguestConfig::CHANNEL_ID)
                                ->first();

                            if (!$existingChannel) {
                                // Crear nuevo registro con channel_id = 6, type = 2
                                $newChannelHotel = new ChannelHotel();
                                $newChannelHotel->hotel_id = $dbHotelId;
                                $newChannelHotel->channel_id = ChannelHyperguestConfig::CHANNEL_ID; // 6
                                $newChannelHotel->type = ChannelHyperguestConfig::TYPE_CHANNEL; // 2
                                $newChannelHotel->code = (string)$propertyId; // Usar el property_id como código
                                $newChannelHotel->state = 1; // Estado activo por defecto
                                $newChannelHotel->save();
                            }
                        }
                    }
                }
            }

            // Crear el batch de importación
            $batch = new HyperguestHotelImportBatch();
            $batch->user_id = $user_id;
            $batch->country = $mainCountry;
            $batch->hotel_ids = $propertyIds;
            $batch->status = 'pending';
            $batch->total_hotels = count($propertyIds);
            $batch->completed_hotels = 0;
            $batch->failed_hotels = 0;
            $batch->save();

            // Disparar el job de importación
            try {
                $queueConnection = config('queue.default');

                // Si es sync, ejecutar directamente para mejor manejo de errores
                if ($queueConnection === 'sync') {
                    $job = new ImportHyperguestHotelsJob($batch->id, $propertyIds);
                    $job->handle(app(HotelSyncService::class));
                } else {
                    ImportHyperguestHotelsJob::dispatch($batch->id, $propertyIds);
                }

            } catch (\Exception $e) {
                // Actualizar estado a 'failed' si no se pudo despachar el job
                $batch->status = 'failed';
                $batch->error_message = 'Error al iniciar el proceso de importación: ' . $e->getMessage();
                $batch->save();

                return Response::json([
                    'success' => false,
                    'message' => 'Error al iniciar el proceso de importación: ' . $e->getMessage()
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => "Se creó un lote de importación con {$batch->total_hotels} hotel(es) y se inició el proceso de sincronización",
                'batch_id' => $batch->id,
                'total_hotels' => $batch->total_hotels
            ]);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error al procesar la importación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los batches de importación no vistos del usuario actual
     * Solo retorna completados y fallidos
     */
    public function getBatches(Request $request)
    {
        try {
            $user_id = Auth::id();

            $batches = HyperguestHotelImportBatch::with('countryRelation.translations')->where('user_id', $user_id)
                ->whereIn('status', ['completed', 'failed', 'pending', 'processing'])
                ->where('viewed', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($batch) {
                    // Si tiene failed_hotels > 0, considerar como fallido
                    $effectiveStatus = $batch->status;
                    if ($batch->failed_hotels > 0 && $batch->status === 'completed') {
                        $effectiveStatus = 'failed';
                    }

                    // Obtener el primer registro de translations del país
                    $countryName = $batch->country; // Código ISO por defecto
                    if ($batch->countryRelation && $batch->countryRelation->translations && $batch->countryRelation->translations->isNotEmpty()) {
                        $firstTranslation = $batch->countryRelation->translations->first();
                        $countryName = $firstTranslation->value ?? $batch->country;
                    }

                    // Obtener lista de hoteles importados desde hotel_results
                    $importedHotels = [];
                    if ($batch->hotel_results && is_array($batch->hotel_results)) {
                        // Obtener todos los hotel_ids de Hyperguest
                        $hyperguestHotelIds = array_keys($batch->hotel_results);

                        if (!empty($hyperguestHotelIds)) {
                            // Buscar los hoteles en la tabla hyperguest_hotels
                            $hyperguestHotels = HyperguestHotel::whereIn('hotel_id', $hyperguestHotelIds)
                                ->get()
                                ->keyBy('hotel_id');

                            // Construir la lista de hoteles importados
                            $errorMessages = $batch->error_message ?? [];
                            foreach ($batch->hotel_results as $hyperguestHotelId => $auroraHotelId) {
                                $hotel = $hyperguestHotels->get($hyperguestHotelId);
                                $status = $auroraHotelId !== null ? 'success' : 'failed';

                                // Obtener el error si existe para este hotel
                                $error = $errorMessages[$hyperguestHotelId] ?? null;

                                $importedHotels[] = [
                                    'hotel_id' => $hyperguestHotelId,
                                    'name' => $hotel ? $hotel->name : 'Hotel no encontrado',
                                    'status' => $status,
                                    'aurora_hotel_id' => $auroraHotelId,
                                    'error' => $error
                                ];
                            }
                        }
                    }

                    return [
                        'id' => $batch->id,
                        'status' => $batch->status,
                        'effective_status' => $effectiveStatus, // Status efectivo para mostrar
                        'total_hotels' => $batch->total_hotels,
                        'completed_hotels' => $batch->completed_hotels,
                        'failed_hotels' => $batch->failed_hotels,
                        'country' => $countryName,
                        'error_message' => $batch->error_message,
                        'created_at' => $batch->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $batch->updated_at->format('Y-m-d H:i:s'),
                        'completion_percentage' => $batch->completion_percentage,
                        'viewed' => $batch->viewed,
                        'message' => $this->getBatchMessage($batch),
                        'imported_hotels' => $importedHotels
                    ];
                });

            return Response::json([
                'success' => true,
                'data' => $batches
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error al obtener los batches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar un batch como visto
     */
    public function markAsViewed(Request $request, $id)
    {
        try {
            $user_id = Auth::id();

            $batch = HyperguestHotelImportBatch::where('id', $id)
                ->where('user_id', $user_id)
                ->first();

            if (!$batch) {
                return Response::json([
                    'success' => false,
                    'message' => 'Batch no encontrado'
                ], 404);
            }

            $batch->viewed = true;
            $batch->save();

            return Response::json([
                'success' => true,
                'message' => 'Batch marcado como visto'
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error al marcar como visto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el estado actualizado de un batch
     */
    public function getBatchStatus($id)
    {
        try {
            $user_id = Auth::id();

            $batch = HyperguestHotelImportBatch::where('id', $id)
                ->where('user_id', $user_id)
                ->first();

            if (!$batch) {
                return Response::json([
                    'success' => false,
                    'message' => 'Batch no encontrado'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'data' => [
                    'id' => $batch->id,
                    'status' => $batch->status,
                    'total_hotels' => $batch->total_hotels,
                    'completed_hotels' => $batch->completed_hotels,
                    'failed_hotels' => $batch->failed_hotels,
                    'country' => $batch->country,
                    'error_message' => $batch->error_message,
                    'created_at' => $batch->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $batch->updated_at->format('Y-m-d H:i:s'),
                    'completion_percentage' => $batch->completion_percentage,
                    'viewed' => $batch->viewed,
                    'message' => $this->getBatchMessage($batch)
                ]
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error al obtener el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar mensaje descriptivo para el batch
     */
    private function getBatchMessage($batch): string
    {
        switch ($batch->status) {
            case 'pending':
                return "Importación pendiente: {$batch->total_hotels} hotel(es) en cola";
            case 'processing':
                $percentage = $batch->completion_percentage;
                return "Importación en proceso: {$batch->completed_hotels}/{$batch->total_hotels} hotel(es) completados ({$percentage}%)";
            case 'completed':
                $message = "Importación completada: {$batch->completed_hotels}/{$batch->total_hotels} hotel(es) importados exitosamente";
                if ($batch->failed_hotels > 0) {
                    $message .= ", {$batch->failed_hotels} hotel(es) fallaron";
                }
                return $message;
            case 'failed':
                $errorMsg = $batch->error_message ? ": {$batch->error_message}" : "";
                return "Importación fallida{$errorMsg}";
            default:
                return "Estado desconocido";
        }
    }

    private function findNameMatches(string $hotelName, array $countryHotels = []): array
    {
        if (empty($hotelName)) {
            return [];
        }

        // Si no hay hoteles del país, no hay coincidencias posibles
        if (empty($countryHotels)) {
            return [];
        }

        // Normalizar el nombre: minúsculas, remover acentos, sin caracteres especiales
        $normalizedName = $this->normalizeString($hotelName);

        // Extraer pares de palabras consecutivas (mínimo 2 palabras juntas)
        $wordPairs = $this->extractWordPairs($normalizedName);

        if (count($wordPairs) < 1) {
            // Si no hay al menos un par de palabras, no podemos buscar coincidencias
            return [];
        }

        // Buscar coincidencias en los hoteles del país (ya cargados en memoria)
        $matches = [];
        $foundHotelIds = [];

        foreach ($wordPairs as $wordPair) {
            if (strlen($wordPair) < 3) {
                continue;
            }

            // Normalizar el par de palabras para comparar
            $searchTerm = $this->normalizeStringForSearch($wordPair);

            // Buscar en los hoteles del país que ya están en memoria
            foreach ($countryHotels as $dbHotel) {
                $hotelId = $dbHotel['id'];

                if (in_array($hotelId, $foundHotelIds)) {
                    continue;
                }

                // Normalizar el nombre del hotel de BD
                $dbNormalizedName = $this->normalizeString($dbHotel['name']);

                // Verificar si contiene el par de palabras
                if (strpos($dbNormalizedName, $searchTerm) !== false) {
                    // Verificar que realmente tenga al menos 2 palabras consecutivas coincidentes
                    $dbWordPairs = $this->extractWordPairs($dbNormalizedName);
                    $commonPairs = array_intersect($wordPairs, $dbWordPairs);

                    if (count($commonPairs) >= 1) {
                        $matches[] = [
                            'id' => $dbHotel['id'],
                            'name' => $dbHotel['name'],
                            'channels' => $dbHotel['channels'] ?? [],
                        ];
                        $foundHotelIds[] = $hotelId;
                    }
                }
            }
        }

        return $matches;
    }

    private function normalizeString(string $str): string
    {
        // Convertir a minúsculas
        $str = mb_strtolower($str, 'UTF-8');

        // Remover acentos
        $str = $this->removeAccents($str);

        // Remover caracteres especiales, mantener solo letras, números y espacios
        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);

        // Normalizar espacios múltiples
        $str = preg_replace('/\s+/', ' ', $str);
        $str = trim($str);

        return $str;
    }

    private function removeAccents(string $str): string
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N',
            'ü' => 'u', 'Ü' => 'U',
        ];

        return strtr($str, $accents);
    }

    private function extractWordPairs(string $str): array
    {
        // Dividir en palabras
        $words = explode(' ', $str);

        // Filtrar palabras vacías y muy cortas (mínimo 2 caracteres)
        $words = array_filter($words, function($word) {
            return strlen(trim($word)) >= 2;
        });

        // Reindexar el array
        $words = array_values($words);

        $wordPairs = [];

        // Si hay al menos 2 palabras, crear pares consecutivos
        if (count($words) >= 2) {
            for ($i = 0; $i < count($words) - 1; $i++) {
                // Crear par de palabras consecutivas
                $pair = trim($words[$i]) . ' ' . trim($words[$i + 1]);
                if (strlen($pair) >= 3) {
                    $wordPairs[] = $pair;
                }
            }
        }

        // Remover duplicados y retornar
        return array_unique($wordPairs);
    }

    private function normalizeStringForSearch(string $str): string
    {
        // Convertir a minúsculas
        $str = mb_strtolower($str, 'UTF-8');

        // Remover acentos
        $str = $this->removeAccents($str);

        return $str;
    }
}

