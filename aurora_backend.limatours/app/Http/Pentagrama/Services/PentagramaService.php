<?php

namespace App\Http\Pentagrama\Services;

use App\ChannelHotel;
use App\ExtensionPentagramaService;
use App\Hotel;
use App\Http\Pentagrama\Traits\PentagramaTrait;
use App\Service;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PentagramaService
{
    use PentagramaTrait;

    private function extractCodes($collection): array
    {
        return $collection
            ->flatMap(function ($svc) {
                $parent = $svc->aurora_code ? [$svc->aurora_code] : [];

                $details = $svc->details
                    ->pluck('external_service_id')
                    ->filter()
                    ->values()
                    ->all();

                return array_merge($parent, $details);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function buildReferenceMaps(array $codes): array
    {
        if (empty($codes)) {
            return [[], [], []];
        }

        $serviceMap = Service::whereIn('aurora_code', $codes)
            ->get()
            ->keyBy('aurora_code');

        $channelHotels = ChannelHotel::whereIn('code', $codes)->get();

        $hotelMap = Hotel::whereIn(
            'id',
            $channelHotels->pluck('hotel_id')->filter()->unique()
        )->get()->keyBy('id');

        $channelHotelMap = $channelHotels->keyBy('code');

        return [$serviceMap, $channelHotelMap, $hotelMap];
    }

    private function transformService(
        $svc,
        $serviceMap,
        $channelHotelMap,
        $hotelMap
    )
    {
        $svcArray = $svc->toArray();

        $svcArray['service_mapped'] = $this->mapParentService(
            $svc->aurora_code,
            $serviceMap,
            $channelHotelMap,
            $hotelMap
        );

        $svcArray['details'] = collect($svc->details)
            ->map(function ($detail) use (
                $serviceMap,
                $channelHotelMap,
                $hotelMap
            ) {
                return $this->transformDetail(
                    $detail,
                    $serviceMap,
                    $channelHotelMap,
                    $hotelMap
                );
            })
            ->values();

        return $svcArray;
    }

    private function mapParentService(
        ?string $code,
                $serviceMap,
                $channelHotelMap,
                $hotelMap
    )
    {
        if (!$code) {
            return null;
        }

        // Caso 1: Es un Service interno
        if (isset($serviceMap[$code])) {
            $service = $serviceMap[$code];

            return [
                'type' => 'service',
                'object' => $service->only(['id', 'name', 'aurora_code', 'type']),
            ];
        }

        // Caso 2: Es un ChannelHotel → Hotel
        if (isset($channelHotelMap[$code])) {
            $channelHotel = $channelHotelMap[$code];
            $hotel = $hotelMap[$channelHotel->hotel_id] ?? null;

            if ($hotel) {
                return [
                    'type' => 'hotel',
                    'object' => $hotel->only(['id', 'name']),
                ];
            }
        }

        return null;
    }

    private function transformDetail(
        $detail,
        $serviceMap,
        $channelHotelMap,
        $hotelMap
    )
    {
        $externalCode = $detail->external_service_id;

        $mappedObject = null;
        $mappedType = null;
        $isMapped = false;

        if ($externalCode) {

            // Caso Service
            if (isset($serviceMap[$externalCode])) {
                $mappedObject = $serviceMap[$externalCode]
                    ->only(['id', 'name', 'aurora_code', 'type']);

                $mappedType = 'service';
                $isMapped = true;
            } // Caso Hotel
            elseif (isset($channelHotelMap[$externalCode])) {
                $channelHotel = $channelHotelMap[$externalCode];
                $hotel = $hotelMap[$channelHotel->hotel_id] ?? null;

                if ($hotel) {
                    $mappedObject = $hotel->only(['id', 'name']);
                    $mappedType = 'hotel';
                    $isMapped = true;
                }
            }
        }

        return [
            'id' => $detail->id,
            'extension_pentagrama_service_id' => $detail->extension_pentagrama_service_id,
            'executive_name' => $detail->executive_name,
            'city' => $detail->city,
            'single_date' => $detail->single_date,
            'single_hour' => $detail->single_hour,
            'type_service' => $detail->type_service,
            'external_service_id' => $externalCode,
            'external_service_description' => $detail->external_service_description,
            'status_service' => $detail->status_service,
            'status_selected' => $detail->status_selected,
            'is_mapped' => $isMapped,
            'mapped_type' => $mappedType,
            'mapped_object' => $mappedObject,
            'created_at' => $detail->created_at,
            'updated_at' => $detail->updated_at,
        ];
    }

    public function list2(array $filters = [])
    {
        $userId = Auth::user()->id;
        $limit = $filters['limit'] ?? 15;

        $query = ExtensionPentagramaService::where('user_id', $userId)->with('details');

        $paginator = $query->paginate($limit);

        $collection = collect($paginator->items());

        /**
         * ===========================
         * PREFETCH DE CÓDIGOS
         * ===========================
         */
        $externalCodes = [];
        $parentCodes = [];

        foreach ($collection as $svc) {
            if (!empty($svc->aurora_code)) {
                $parentCodes[] = $svc->aurora_code;
            }

            foreach ($svc->details as $d) {
                if (!empty($d->external_service_id)) {
                    $externalCodes[] = $d->external_service_id;
                }
            }
        }

        $codes = collect(array_merge($externalCodes, $parentCodes))
            ->filter()
            ->unique()
            ->values()
            ->all();

        /**
         * ===========================
         * MAPS
         * ===========================
         */
        $serviceMap = [];
        $channelHotelMap = [];
        $hotelMap = [];

        if (!empty($codes)) {
            $serviceMap = Service::whereIn('aurora_code', $codes)
                ->get()
                ->keyBy('aurora_code');

            $channelHotels = ChannelHotel::whereIn('code', $codes)->get();
            $hotelIds = $channelHotels->pluck('hotel_id')->filter()->unique();

            if ($hotelIds->isNotEmpty()) {
                $hotelMap = Hotel::whereIn('id', $hotelIds)
                    ->get()
                    ->keyBy('id');
            }

            foreach ($channelHotels as $ch) {
                $channelHotelMap[$ch->code] = $ch;
            }
        }

        /**
         * ===========================
         * ENRICH DE LA PÁGINA ACTUAL
         * ===========================
         */
        $data = $collection->map(function ($svc) use ($serviceMap, $channelHotelMap, $hotelMap) {

            $svcArray = $svc->toArray();

            /** Mapping del servicio padre */
            $serviceMapped = null;
            if (!empty($svc->aurora_code)) {
                $code = $svc->aurora_code;

                if (isset($serviceMap[$code])) {
                    $s = $serviceMap[$code];
                    $serviceMapped = [
                        'type' => 'service',
                        'object' => $s->only(['id', 'name', 'aurora_code', 'type']),
                    ];
                } elseif (isset($channelHotelMap[$code])) {
                    $ch = $channelHotelMap[$code];
                    $hotel = $hotelMap[$ch->hotel_id] ?? null;

                    if ($hotel) {
                        $serviceMapped = [
                            'type' => 'hotel',
                            'object' => $hotel->only(['id', 'name']),
                        ];
                    }
                }
            }

            /** Details */
            $svcArray['details'] = collect($svc->details)->map(function ($d) use ($serviceMap, $channelHotelMap, $hotelMap) {

                $external = $d->external_service_id;
                $mapped = null;
                $mappedType = null;
                $isMapped = false;

                if ($external) {
                    if (isset($serviceMap[$external])) {
                        $mapped = $serviceMap[$external]->only(['id', 'name', 'aurora_code', 'type']);
                        $mappedType = 'service';
                        $isMapped = true;
                    } elseif (isset($channelHotelMap[$external])) {
                        $ch = $channelHotelMap[$external];
                        $hotel = $hotelMap[$ch->hotel_id] ?? null;
                        if ($hotel) {
                            $mapped = $hotel->only(['id', 'name']);
                            $mappedType = 'hotel';
                            $isMapped = true;
                        }
                    }
                }

                return [
                    'id' => $d->id,
                    'extension_pentagrama_service_id' => $d->extension_pentagrama_service_id,
                    'executive_name' => $d->executive_name,
                    'city' => $d->city,
                    'single_date' => $d->single_date,
                    'single_hour' => $d->single_hour,
                    'type_service' => $d->type_service,
                    'external_service_id' => $external,
                    'external_service_description' => $d->external_service_description,
                    'status_service' => $d->status_service,
                    'status_selected' => $d->status_selected,
                    'is_mapped' => $isMapped,
                    'mapped_type' => $mappedType,
                    'mapped_object' => $mapped,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                ];
            })->values();

            $svcArray['service_mapped'] = $serviceMapped;

            return $svcArray;
        })
            // Ordenar por fecha más reciente del detalle (o fecha de creación si no tiene detalles)
            ->sortByDesc('created_at')
            ->values();

        /**
         * ===========================
         * RESPUESTA FINAL
         * ===========================
         */
        return [
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function list(array $filters = [])
    {
        $userId = Auth::id();
        $limit = $filters['limit'] ?? 15;

        /**
         * ===========================
         * QUERY BASE (ORDEN CORRECTO)
         * ===========================
         */
        $paginator = ExtensionPentagramaService::query()
            ->where('user_id', $userId)
            ->with('details')
            ->orderByDesc('created_at') // 🔥 ORDEN EN BD
            ->paginate($limit);

        $collection = $paginator->getCollection();

        /**
         * ===========================
         * PREFETCH DE CÓDIGOS
         * ===========================
         */
        $codes = $this->extractCodes($collection);

        /**
         * ===========================
         * MAPS DE REFERENCIA
         * ===========================
         */
        [$serviceMap, $channelHotelMap, $hotelMap] = $this->buildReferenceMaps($codes);

        /**
         * ===========================
         * ENRICH DATA
         * ===========================
         */
        $transformed = $collection->map(function ($svc) use (
            $serviceMap,
            $channelHotelMap,
            $hotelMap
        ) {
            return $this->transformService(
                $svc,
                $serviceMap,
                $channelHotelMap,
                $hotelMap
            );
        });

        $paginator->setCollection($transformed);

        /**
         * ===========================
         * RESPONSE
         * ===========================
         */
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function detail(int $id, array $filters = [])
    {
        try {
            // Cargar servicio con detalles y encabezado opcional
            $service = ExtensionPentagramaService::find($id);

            if (!$service) {
                return [
                    'data' => null,
                    'message' => 'Servicio no encontrado',
                ];
            }

            // Verificar si el servicio padre está mapeado a un servicio u hotel en Aurora
            $serviceMapped = null;
            if (!empty($service->aurora_code)) {
                $svc = Service::where('aurora_code', $service->aurora_code)->first();
                if ($svc) {
                    $serviceMapped = [
                        'type' => 'service',
                        'object' => $svc->only(['id', 'name', 'aurora_code', 'type']),
                    ];
                } else {
                    $ch = ChannelHotel::where('code', $service->aurora_code)->first();
                    if ($ch) {
                        $hotel = Hotel::find($ch->hotel_id);
                        if ($hotel) {
                            $serviceMapped = [
                                'type' => 'hotel',
                                'object' => $hotel->only(['id', 'name']),
                            ];
                        }
                    }
                }
            }

            // Preparar el detalle de los servicios con info de mapeo
            $enrichedDetails = $service->details->map(function ($d) {
                $external = $d->external_service_id ?? null;

                $mapped = null;
                $mappedType = null;
                $isMapped = false;

                if (!empty($external)) {
                    $ms = Service::where('aurora_code', $external)->first();
                    if ($ms) {
                        $mapped = $ms->only(['id', 'name', 'aurora_code', 'type']);
                        $mappedType = 'service';
                        $isMapped = true;
                    } else {
                        $ch = ChannelHotel::where('code', $external)->first();
                        if ($ch) {
                            $hotel = Hotel::find($ch->hotel_id);
                            if ($hotel) {
                                $mapped = $hotel->only(['id', 'name']);
                                $mappedType = 'hotel';
                                $isMapped = true;
                            }
                        }
                    }
                }

                return [
                    'id' => $d->id,
                    'extension_pentagrama_service_id' => $d->extension_pentagrama_service_id,
                    'executive_name' => $d->executive_name,
                    'city' => $d->city,
                    'single_date' => $d->single_date,
                    'single_hour' => $d->single_hour,
                    'type_service' => $d->type_service,
                    'external_service_id' => $d->external_service_id,
                    'external_service_description' => $d->external_service_description,
                    'status_service' => $d->status_service ?? null,
                    'status_selected' => $d->status_selected ?? null,
                    'is_mapped' => $isMapped,
                    'mapped_type' => $mappedType,
                    'mapped_object' => $mapped,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                ];
            })->toArray();

            // Return base service (as array) plus enriched details and service-level mapping
            $serviceArray = $service->toArray();
            $serviceArray['details'] = $enrichedDetails;

            return [
                'data' => $serviceArray,
                'service_mapped' => $serviceMapped,
            ];
        } catch (Exception $ex) {
            return [
                'data' => null,
                'error' => $ex->getMessage(),
            ];
        }
    }

    public function create(array $data)
    {
        // Obtener datos cruzados
        $crossedData = $this->getCrossed($data);

        $serviceData = $crossedData['service'] ?? [];
        $detailItems = $crossedData['service_details'] ?? [];
        $userId = Auth::user()->id ?? null;

        // Crear transacción
        return DB::transaction(function () use ($serviceData, $detailItems, $userId) {
            // Crear el servicio padre
            $parent = ExtensionPentagramaService::create([
                'user_id' => $userId,
                'passenger' => $serviceData['passenger'] ?? null,
            ]);

            // Insertar masivamente los detalles del servicio padre
            if (!empty($detailItems)) {
                $parent->details()->createMany($detailItems);
            }

            return $parent;
        });
    }

    public function update(int $id, array $data)
    {
        $service = ExtensionPentagramaService::find($id);

        if (!$service) {
            throw new NotFoundHttpException('Servicio no encontrado');
        }

        $service->quote_number = $data['quote_number'] ?? $service->quote_number;
        $service->status = 'processed';
        $service->save();

        return $service;
    }
}
