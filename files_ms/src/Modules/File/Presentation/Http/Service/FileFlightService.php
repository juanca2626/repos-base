<?php

namespace Src\Modules\File\Presentation\Http\Service;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryFlightEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;

class FileFlightService
{
    /**
     * @var FileRepositoryInterface|mixed
     */
    private FileRepositoryInterface $fileRepository;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * Calcula las horas de servicio para un archivo y un itinerario de vuelo específico.
     *
     * @param int $idFile ID del archivo.
     * @param int $itineraryFlightIdProcess ID del itinerario de vuelo a procesar.
     * @throws \DateInvalidOperationException
     * @throws \DateMalformedIntervalStringException
     * @throws \DateMalformedStringException
     * @throws \Exception
     */
    public function calculateHoursService(int $idFile, int $itineraryFlightIdProcess,string $dateIn, bool $debug, string $type = 'service')
    {
        // Obtiene los datos del archivo
        $files = $this->fileRepository->queryFileDate($idFile, $dateIn);
        // traer los itinearios del dia + con

        $result = [
            'associated_services' => [],
            'unassociated_services' => [],
            'unassociated_flights' => [],
            'update_itineraries' => [],
            'update_hotels'     => [],
            'master_services' => []
        ];

        // Convertir a colección si es un array
        $data = $files['itineraries'];

        // return $data;

        $flightQuery = $data->where('entity', 'flight');

        if ($type !== 'service') {
            $flightQuery = $flightQuery->where('id', $itineraryFlightIdProcess);
        }

        // Separar vuelos y servicios
        $flights = $flightQuery->map(function($value) {
            return collect($value)->only(['id','entity','name','object_code','city_in_iso','city_out_iso','date_in','date_in','date_out','hotel_origin','hotel_destination','flights'])->map(function ($item, $key) {
                if ($key === 'flights' && is_array($item)) {
                    return collect($item)->map(function ($flight) {
                        return collect($flight)->only([
                            'id', 'file_itinerary_id', 'departure_time', 'arrival_time','nro_pax','accommodations'
                        ])
                        ->map(function ($value, $flightKey) {
                            if ($flightKey === 'accommodations' && is_array($value)) {
                                return collect($value)->map(function ($accommodation) {
                                    return collect($accommodation)->only([
                                        'id', 'file_passenger_id', 'file_itinerary_flight_id'
                                    ]);
                                });
                            }
                            return $value;
                        });
                    });
                }
                return $item;
            });
        });

        $services = $data->where('entity', 'service')->where('status','=','1')->map(function($value) {
            return collect($value)->only(['id','entity','name','city_in_iso','city_out_iso','date_in','date_out','start_time','departure_time','hotel_origin','hotel_destination','services','accommodations'])->map(function ($item,$key) {
                if ($key === 'accommodations' && is_array($item)) {
                    return collect($item)->map(function ($flight) {
                        return collect($flight)->only([
                        'id', 'file_passenger_id', 'file_itinerary_flight_id'
                        ]);
                    });
                }
                if($key === 'services' && is_array($item)){
                    return collect($item)->map(function ($service){
                        return collect($service)->only([
                            'id' , 'file_itinerary_id','code','master_service_id','name','start_time','departure_time','frecuency_code','type_ifx','date_in','compositions'
                        ])->map(function ($value, $serviceKey){
                            if($serviceKey === 'compositions' && is_array($value)){
                                return collect($value)->map(function ($composition){
                                    return collect($composition)->only([
                                        'id','file_service_id','file_classification_id','type_composition_id','type_component_service_id','composition_id','code','name','date_in','start_time','departure_time'
                                    ]);
                                });
                            }
                            return $value;
                        });
                    });
                }
                return $item;
            });
        });

        $finalResult = collect($services)
        ->sortBy(['id', 'asc'])
        ->flatMap(function (Collection $itinerary) {
            return $itinerary->get('services', [])
                ->sortBy(['id', 'asc'])
                ->map(function ($service) use ($itinerary) {
                    // Primero extraemos y procesamos TODOS los compositions
                    $allCompositions = collect($service->get('compositions', []))
                        ->sortBy('id')
                        ->groupBy(fn ($comp) => "{$comp['code']}_{$comp['date_in']}")
                        ->flatMap(function (Collection $group, $key) {
                            return $group->map(function ($comp, $index) {
                                return [
                                    ...$comp,
                                    'order' => $index + 1, // Orden consecutivo por grupo
                                ];
                            });
                        })
                        ->sortBy('id') // Mantener orden original por id
                        ->values();

                    return [
                        ...$service->toArray(),
                        'itinerary_id' => $itinerary->get('id'),
                        'compositions' => $allCompositions,
                    ];
                });
        })
        ->groupBy(fn ($service) => "{$service['date_in']}_{$service['code']}")
        ->flatMap(function (Collection $group) {
            return $group->map(fn ($service, $index) => [
                ...$service,
                'order' => $group->count() > 1 ? $index + 1 : 1,
            ]);
        })
        ->all();

        $allComposition = collect($finalResult)->flatMap(function($service) {
            return $service['compositions'];
        });

        $processedCompositions = $allComposition
            ->sortBy('id') // Ordenamos primero por id
            ->groupBy(fn($comp) => "{$comp['code']}_{$comp['date_in']}") // Agrupamos por code y date_in
            ->flatMap(function (Collection $group, $key) {
                return $group->map(function ($comp, $index) {
                    return [
                        ...$comp,
                        'order' => $index + 1, // Asignamos orden consecutivo
                    ];
                });
            })
            ->sortBy('id') // Volvemos a ordenar por id para mantener el orden original
            ->values(); // Reindexamos las claves

        $finalResult = collect($finalResult)
            ->map(function ($service) use ($processedCompositions) {
                // Filtramos los compositions que pertenecen a este servicio
                $serviceCompositions = $processedCompositions
                    ->filter(fn($comp) => $comp['file_service_id'] === $service['id'])
                    ->values()
                    ->toArray();

                return [
                    ...$service,
                    'compositions' => $serviceCompositions,
                ];
            })
            ->toArray();

        // return $finalResult;
        // return $allComposition->toArray();
        // Resultado final
        $hotels = $data->where('entity','hotel')->where('status','=','1')->map(function ($value){
            return collect($value)->only(['id','entity','name','city_in_iso','city_out_iso','date_in','date_out','start_time','departure_time','total_adults','rooms','object_code']);
        });

        // return $hotels;

        $finalResultHotel = collect($hotels)
        ->sortBy(['id','asc'])
        ->flatMap(function (Collection $itinerary) {
            return collect($itinerary->get('rooms', []))
                ->sortBy(['id','asc'])
                ->map(function (array $room) use ($itinerary) {
                    return [
                        ...$room,
                        'itinerary_id' => $itinerary->get('id'),
                    ];
                });
        })
        ->groupBy('rate_plan_code') // Más simple que fn()
        ->flatMap(function (Collection $group, string $ratePlanCode) {
            return $group->map(function (array $room, int $index) use($group){
                return [
                    ...$room,
                    'order' => $group->count() > 1 ? $index + 1 : 1,
                ];
            });
        })
        ->values() // Reindexa las claves numéricas
        ->all();
        // Procesar cada vuelo
        foreach ($flights as $flight) {
            // solo busca por el itinerario que necesito
            // if($itineraryFlightIdProcess === $flight['id']){
            $isInternational = $flight['object_code'] === 'AEIFLT';
            $isNational = $flight['object_code'] === 'AECFLT';

            // Obtener todos los pasajeros del vuelo (de todos los subvuelos)
            $flightPassengers = $flight['flights'] instanceof Collection ? $flight['flights'] : collect($flight['flights']);
            $flightPassengers = $flightPassengers
                ->pluck('accommodations')
                ->flatten(1) //aplana un nivel
                ->pluck('file_passenger_id')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            // [5784, 5785]

            $subflightWithMaxArrival = collect($flight['flights'])
                ->filter(function ($subflight) {
                    return !is_null($subflight['arrival_time']); // Filtrar nulos
                })
                ->sortByDesc('arrival_time') // Ordenar descendente por arrival_time
                ->first(); // Tomar el primero (el de mayor arrival_time)
            // 10:00:00

            if ($subflightWithMaxArrival) {
                $maxArrivalTime = $subflightWithMaxArrival['arrival_time'];
                $departureTime = $subflightWithMaxArrival['departure_time'];
            } else {
                $maxArrivalTime = null;
                $departureTime = null;
            }

            $previousService = null;
            $nextService = null;

            // Buscar servicios asociados por fecha y pasajeros
            $possibleServices = $this->searchServiceAsociatedForPassengers($services, $flight, $flightPassengers);

            // Buscar servicio anterior válido
            if ($isNational || ($isInternational && !is_null($flight['city_in_iso']))) {
                $previousService = $possibleServices->first(function($service) use ($flight) {
                    return $service['hotel_origin'] == 1 &&
                        $service['hotel_destination'] == 0 &&
                        $service['city_out_iso'] == $flight['city_in_iso'];

                    // return $service['hotel_origin'] == 1 &&
                    //     $service['hotel_destination'] == 0 &&
                    //     $service['city_in_iso'] == $flight['city_in_iso'] &&
                    //     $service['city_out_iso'] == $flight['city_in_iso'];
                });
            }

            // Buscar servicio posterior válido
            if ($isNational || ($isInternational && !is_null($flight['city_out_iso']))) {
                $nextService = $possibleServices->first(function($service) use ($flight) {
                    return $service['hotel_origin'] == 0 &&
                        $service['hotel_destination'] == 1 &&
                        $service['city_in_iso'] == $flight['city_out_iso'];

                    // return $service['hotel_origin'] == 0 &&
                    //     $service['hotel_destination'] == 1 &&
                    //     $service['city_in_iso'] == $flight['city_out_iso'] &&
                    //     $service['city_out_iso'] == $flight['city_out_iso'];
                });
            }

            $entry = [
                'entity' => $flight,
                'departure_time' => $departureTime,
                'max_arrival_time' => $maxArrivalTime
            ];

            $flightDate = $flight['date_in'];
            // Calcular before_arrival_time (restar 2.5h nacional, 3.5h internacional)
            if ($departureTime && ($isNational || $isInternational)) {
                $hoursToSubtract = $isNational ? 2.5 : 3.5;
                $beforeDepartureTime = (new DateTime($flightDate." ".$departureTime))
                    ->sub(new DateInterval('PT' . (int)$hoursToSubtract . 'H'))
                    ->sub(new DateInterval('PT' . (($hoursToSubtract - (int)$hoursToSubtract) * 60) . 'M'))
                    ->format('Y-m-d H:i:s');

                $entry['before_departure_time'] = $this->normalizeTime($beforeDepartureTime,$flightDate." ".$departureTime);

                if($entry['before_departure_time'] != "00:00:00"){
                    $beforeStartTime = (new DateTime($beforeDepartureTime))
                        ->sub(new DateInterval('PT1H'))
                        ->format('Y-m-d H:i:s');

                    $entry['before_start_time'] = $this->normalizeTime($beforeStartTime,$flightDate." ".$departureTime);
                }else{
                    $entry['before_start_time'] = "00:00:00";
                }
            } else {
                $entry['before_departure_time'] = null;
                $entry['before_start_time'] = null;
            }

            // Agregar last_start_time (que es igual a arrival_time_max)
            $entry['last_start_time'] = $maxArrivalTime;
            $entry['last_departure_time'] = $this->addTime($flightDate." ".$maxArrivalTime,1);

            if ($previousService) {
                $previousService['start_time'] = $entry['before_start_time'];
                $previousService['departure_time'] = $entry['before_departure_time'];
                $entry['previous_service'] = $previousService;

                // ACTUALIZAR SIEMPRE Y CUANDO ENVIE EL ID DEL ITINERARIO SEA IGUAL A $itineraryFlightIdProcess;
                if($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $previousService['id']){
                    // ACTUALIZACION DEL ITINERARIO DE TIPO SERVICIO DE TRASLADO
                    $result['update_itineraries'][] = $previousService['id'];
                    FileItineraryEloquentModel::query()
                            ->where('id',$previousService['id'])
                            ->update([
                                'start_time'        => $previousService['start_time'],
                                'departure_time'    => $previousService['departure_time'],
                            ]);

                    // ACTUALIZACION DE LOS SERVICES
                    FileServiceEloquentModel::where('file_itinerary_id', $previousService['id'])
                        ->update([
                            'start_time'        => $previousService['start_time'],
                            'departure_time'    => $previousService['departure_time'],
                        ]);

                    if(!empty($previousService['services'] ?? [])){
                        foreach($previousService['services'] as $service){
                            $components = [];

                            if($service['type_ifx'] == 'package' && !empty($service['compositions'] ?? []) ){
                                foreach($service['compositions'] as $composition){
                                    $components[] = [
                                        'code' => $composition['code'],
                                        'auto_order' => $this->findOrder($finalResult, $previousService['id'], $service['id'], $composition['id']),
                                        'type_ifx' => $service['type_ifx'],
                                        'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                        'start_time_current' => substr($composition['start_time'], 0, 5),
                                        'start_time' => substr($previousService['start_time'], 0, 5),
                                        'departure_time' => substr($previousService['departure_time'], 0, 5)
                                    ];
                                }
                            }

                            $result['master_services'][] = [
                                'code'                  => $service['code'],
                                'auto_order'            => $this->findOrder($finalResult, $previousService['id'], $service['id']),
                                'type_ifx'              => $service['type_ifx'],
                                'date_in'               => Carbon::parse($service['date_in'])->format('d/m/Y'),
                                'start_time_current'    => substr($service['start_time'],0,5),
                                'start_time'            => substr($previousService['start_time'],0,5),
                                'departure_time'        => substr($previousService['departure_time'],0,5),
                                'components'            => $components,
                            ];
                        }
                    }

                    // ACTUALIZACION DE LOS COMPOSITIONS
                    $serviceIds = collect($previousService['services'])->pluck('id');
                    if(count($serviceIds) > 0){
                        FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                            ->update([
                                'start_time' => $previousService['start_time'],
                                'departure_time' => $previousService['departure_time'],
                            ]);
                    }
                }
            }

            if ($nextService) {
                $nextService['start_time'] = $entry['max_arrival_time'];
                $entry['next_service'] = $nextService;

                // ACTUALIZAR SIEMPRE Y CUANDO ENVIE EL ID DEL ITINERARIO SEA IGUAL A $itineraryFlightIdProcess;
                if($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $nextService['id']){
                    // ACTUALIZACION DEL ITINERARIO - TIPO SERVICIO DE TRASLADO
                    $result['update_itineraries'][] = $nextService['id'];
                    FileItineraryEloquentModel::query()
                        ->where('id',$nextService['id'])
                        ->update([
                            'start_time'        => $nextService['start_time'],
                            'departure_time'    => $entry['last_departure_time']
                        ]);

                    // ACTUALIZACION DEL SERVICES
                    FileServiceEloquentModel::where('file_itinerary_id', $nextService['id'])
                        ->update([
                            'start_time'        => $nextService['start_time'],
                            'departure_time'    => $entry['last_departure_time'],
                        ]);

                    if(!empty($nextService['services'] ?? [])){
                        foreach($nextService['services'] as $service){
                            $components = [];

                            if($service['type_ifx'] == 'package' && !empty($service['compositions'] ?? []) ){
                                foreach($service['compositions'] as $composition){
                                    $components[] = [
                                        'code' => $composition['code'],
                                        'auto_order' => $this->findOrder($finalResult, $nextService['id'], $service['id'], $composition['id']),
                                        'type_ifx' => $service['type_ifx'],
                                        'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                        'start_time_current' => substr($composition['start_time'], 0, 5),
                                        'start_time' => substr($entry['max_arrival_time'], 0, 5),
                                        'departure_time' => substr($entry['last_departure_time'], 0, 5),
                                    ];
                                }
                            }

                            $result['master_services'][] = [
                                'code'                  => $service['code'],
                                'auto_order'            => $this->findOrder($finalResult, $nextService['id'], $service['id']),
                                'type_ifx'              => $service['type_ifx'],
                                'date_in'               => Carbon::parse($service['date_in'])->format('d/m/Y'),
                                'start_time_current'    => substr($service['start_time'],0,5),
                                'start_time'            => substr($entry['max_arrival_time'],0,5),
                                'departure_time'        => substr($entry['last_departure_time'],0,5),
                                'components'            => $components,
                            ];
                        }
                    }

                    // ACTUALIZACION DE LOS COMPOSITIONS
                    $serviceIds = collect($nextService['services'])->pluck('id');
                    if(count($serviceIds) > 0){
                        FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                            ->update([
                                'start_time'        => $nextService['start_time'],
                                'departure_time'    => $entry['last_departure_time']
                            ]);
                    }

                    // ACTUALIZAR EL HOTEL CON LA ULTIMA
                    // if(!empty($hotels)){
                    //     foreach($hotels as $hotel){
                    //         $result['update_hotels'][] = $hotel;
                    //         $result['update_itineraries'][] = $hotel['id'];
                    //         if(!empty($hotel['rooms'] ?? [])){
                    //             foreach($hotel['rooms'] as $key => $serv){
                    //                 $result['master_services'][] = [
                    //                     'code'                  => $hotel['object_code'],
                    //                     'auto_order'            => $this->findOrderHotel($finalResultHotel,$hotel['id'],$serv['id']),
                    //                     'type_ifx'              => 'direct',
                    //                     'date_in'               => Carbon::parse($hotel['date_in'])->format('d/m/Y'),
                    //                     'start_time_current'    => substr($hotel['start_time'],0,5),
                    //                     'start_time'            => substr($entry['last_departure_time'],0,5),
                    //                     'departure_time'        => substr($hotel['departure_time'],0,5),
                    //                     'components'            => [],
                    //                 ];
                    //             }
                    //         }

                    //         FileItineraryEloquentModel::query()
                    //         ->where('id',$hotel['id'])
                    //         ->update([
                    //             'start_time'        => $entry['last_departure_time']
                    //         ]);
                    //     }
                    // }
                }
            }

            // Determinar si el vuelo tiene servicios asociados
            if ($previousService || $nextService) {
                $result['update_itineraries'][] = $flight['id'];
                $result['associated_services'][] = $entry;
            } else {
                $result['unassociated_flights'][] = $flight;
            }
            // }
        }

        // Identificar servicios no asociados
        $associatedServices = collect($result['associated_services'])
            ->flatMap(function($item) {
                return [
                    $item['previous_service'] ?? null,
                    $item['next_service'] ?? null
                ];
            })
            ->filter()
            ->toArray();

        $result['unassociated_services'] = $services->filter(function($service) use ($associatedServices) {
            return !in_array($service, $associatedServices);
        })->values()->toArray();

        // Procesar por subvuelo
        foreach ($flights as $flight){
            $isInternational = $flight['object_code'] === 'AEIFLT';
            $isNational = $flight['object_code'] === 'AECFLT';

            foreach ($flight['flights'] as $subflight) {
                // Obtener pasajeros de este subvuelo específico
                $subflightPassengers = collect($subflight['accommodations'])
                    ->pluck('file_passenger_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

                // return $subflightPassengers;

                // Buscar servicios no asociados que coincidan con este subvuelo
                $possibleSubServices = collect($result['unassociated_services'])
                    ->filter(function($service) use ($flight, $subflightPassengers) {
                        // Misma fecha
                        if ($service['date_in'] != $flight['date_in']) {
                            return false;
                        }

                        // Mismos pasajeros
                        $servicePassengers = collect($service['accommodations'])
                            ->pluck('file_passenger_id')
                            ->sort()
                            ->values()
                            ->toArray();

                        return array_intersect($servicePassengers, $subflightPassengers);
                    });

                $subPreviousService = null;
                $subNextService = null;

                // Buscar servicio anterior para el subvuelo
                if ($isNational || ($isInternational && !is_null($flight['city_in_iso']))) {
                    $subPreviousService = $possibleSubServices->first(function($service) use ($flight) {
                        return $service['hotel_origin'] == 1 &&
                            $service['hotel_destination'] == 0 &&
                            $service['city_out_iso'] == $flight['city_in_iso'];
                    });
                }

                // Buscar servicio posterior para el subvuelo
                if ($isNational || ($isInternational && !is_null($flight['city_out_iso']))) {
                    $subNextService = $possibleSubServices->first(function($service) use ($flight) {
                        return $service['hotel_origin'] == 0 &&
                            $service['hotel_destination'] == 1 &&
                            $service['city_in_iso'] == $flight['city_out_iso'];
                    });
                }

                if ($subPreviousService || $subNextService) {
                    // Realizar los mismos cálculos de tiempo basado en el vuelo padre
                    // $subflight['flights'] = $flight['flights'];
                    $subEntry = [
                        'entity'            => $subflight,
                        'departure_time'    => $subflight['departure_time'],
                        'max_arrival_time'  => $subflight['arrival_time'],
                        'flights'           => $flight['flights'],
                    ];

                    // Calcular before_arrival_time (usando la misma lógica nacional/internacional del padre)
                    if ($subflight['departure_time'] && ($isNational || $isInternational)) {
                        $hoursToSubtract = $isNational ? 2.5 : 3.5;
                        $beforeDepartureTime = (new DateTime($flight['date_in']." ".$subflight['departure_time']))
                            ->sub(new DateInterval('PT' . (int)$hoursToSubtract . 'H'))
                            ->sub(new DateInterval('PT' . (($hoursToSubtract - (int)$hoursToSubtract) * 60) . 'M'))
                            ->format('Y-m-d H:i:s');

                        $subEntry['before_departure_time'] = $this->normalizeTime($beforeDepartureTime, $flight['date_in']." ".$subflight['departure_time']);

                        if($subEntry['before_departure_time'] != "00:00:00") {
                            $beforeStartTime = (new DateTime($beforeDepartureTime))
                                ->sub(new DateInterval('PT1H'))
                                ->format('Y-m-d H:i:s');

                            $subEntry['before_start_time'] = $this->normalizeTime($beforeStartTime, $flight['date_in']." ".$subflight['departure_time']);
                        } else {
                            $subEntry['before_start_time'] = "00:00:00";
                        }
                    }

                    $subEntry['last_start_time'] = $subflight['arrival_time'];
                    $subEntry['last_departure_time'] = $this->addTime($flight['date_in']." ".$subflight['arrival_time'], 1);

                    // Asignar servicios al subvuelo
                    if ($subPreviousService) {
                        $subPreviousService['start_time'] = $subEntry['before_start_time'];
                        $subPreviousService['departure_time'] = $subEntry['before_departure_time'];
                        $subEntry['previous_service'] = $subPreviousService;

                        // Actualizaciones (similar al código existente)
                        if($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $subPreviousService['id']) {
                            // ACTUALIZACION DEL ITINERARIO - TIPO SERVICIO DE TRASLADO
                            $result['update_itineraries'][] = $subPreviousService['id'];
                            FileItineraryEloquentModel::query()
                                ->where('id',$subPreviousService['id'])
                                ->update([
                                    'start_time'        => $subPreviousService['start_time'],
                                    'departure_time'    => $subPreviousService['departure_time']
                                ]);

                            // ACTUALIZACION DE LOS SERVICES
                            FileServiceEloquentModel::where('file_itinerary_id', $subPreviousService['id'])
                                ->update([
                                    'start_time'        => $subPreviousService['start_time'],
                                    'departure_time'    => $subPreviousService['departure_time'],
                                ]);

                            if(!empty($subPreviousService['services'] ?? [])){
                                foreach($subPreviousService['services'] as $service){
                                    $components = [];

                                    if($service['type_ifx'] == 'package' && !empty($service['compositions'] ?? []) ){
                                        foreach($service['compositions'] as $composition){
                                            $components[] = [
                                                'code' => $composition['code'],
                                                'auto_order' => $this->findOrder($finalResult, $subPreviousService['id'], $service['id'], $composition['id']),
                                                'type_ifx' => $service['type_ifx'],
                                                'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                                'start_time_current' => substr($composition['start_time'], 0, 5),
                                                'start_time' => substr($subPreviousService['start_time'], 0, 5),
                                                'departure_time' => substr($subPreviousService['departure_time'], 0, 5)
                                            ];
                                        }
                                    }

                                    $result['master_services'][] = [
                                        'code'                  => $service['code'],
                                        'auto_order'            => $this->findOrder($finalResult, $subPreviousService['id'], $service['id']),
                                        'type_ifx'              => $service['type_ifx'],
                                        'date_in'               => Carbon::parse($service['date_in'])->format('d/m/Y'),
                                        'start_time_current'    => substr($service['start_time'],0,5),
                                        'start_time'            => substr($subPreviousService['start_time'],0,5),
                                        'departure_time'        => substr($subPreviousService['departure_time'],0,5),
                                        'components'            => $components,
                                    ];
                                }
                            }

                            // ACTUALIZACION DE LOS COMPOSITIONS
                            $serviceIds = collect($subPreviousService['services'])->pluck('id');
                            if(count($serviceIds) > 0){
                                FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                                    ->update([
                                        'start_time' => $subPreviousService['start_time'],
                                        'departure_time' => $subPreviousService['departure_time'],
                                    ]);
                            }
                        }
                    }

                    if ($subNextService) {
                        $subNextService['start_time'] = $subflight['arrival_time'];
                        $subEntry['next_service'] = $subNextService;

                        // Actualizaciones (similar al código existente)
                        if($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $subNextService['id']) {
                            $result['update_itineraries'][] = $subNextService['id'];

                            FileItineraryEloquentModel::query()
                                ->where('id',$subNextService['id'])
                                ->update([
                                    'start_time'        => $subNextService['start_time'],
                                    'departure_time'    => $subEntry['last_departure_time']
                                ]);

                            // ACTUALIZACION DEL SERVICES
                            FileServiceEloquentModel::where('file_itinerary_id', $subNextService['id'])
                                ->update([
                                    'start_time'        => $subNextService['start_time'],
                                    'departure_time'    => $subEntry['last_departure_time'],
                                ]);

                            if(!empty($subNextService['services'] ?? [])){
                                foreach($subNextService['services'] as $service){
                                    $components = [];

                                    if($service['type_ifx'] == 'package' && !empty($service['compositions'] ?? []) ){
                                        foreach($service['compositions'] as $composition){
                                            $components[] = [
                                                'code' => $composition['code'],
                                                'auto_order' => $this->findOrder($finalResult, $subNextService['id'], $service['id'], $composition['id']),
                                                'type_ifx' => $service['type_ifx'],
                                                'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                                'start_time_current' => substr($composition['start_time'], 0, 5),
                                                'start_time' => substr($subEntry['max_arrival_time'], 0, 5),
                                                'departure_time' => substr($subEntry['last_departure_time'], 0, 5),
                                            ];
                                        }
                                    }

                                    $result['master_services'][] = [
                                        'code'                  => $service['code'],
                                        'auto_order'            => $this->findOrder($finalResult, $subNextService['id'], $service['id']),
                                        'type_ifx'              => $service['type_ifx'],
                                        'date_in'               => Carbon::parse($service['date_in'])->format('d/m/Y'),
                                        'start_time_current'    => substr($service['start_time'],0,5),
                                        'start_time'            => substr($subEntry['max_arrival_time'],0,5),
                                        'departure_time'        => substr($subEntry['last_departure_time'],0,5),
                                        'components'            => $components,
                                    ];
                                }
                            }

                            // ACTUALIZACION DE LOS COMPOSITIONS
                            $serviceIds = collect($subNextService['services'])->pluck('id');
                            if(count($serviceIds) > 0){
                                FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                                    ->update([
                                        'start_time'        => $subNextService['start_time'],
                                        'departure_time'    => $subEntry['last_departure_time']
                                    ]);
                            }
                        }
                    }

                    // Agregar a asociados y remover de no asociados
                    $result['associated_services'][] = $subEntry;

                    // Remover servicios asociados del array de no asociados
                    $result['unassociated_services'] = array_filter($result['unassociated_services'], function($service) use ($subPreviousService, $subNextService) {
                        return $service !== ($subPreviousService ?? null) && $service !== ($subNextService ?? null);
                    });
                }
            }
        }

        $result['unassociated_services'] = array_values($result['unassociated_services']);

        $unassociatedFlights = collect($result['unassociated_flights']);
        // 1. Primero, agrupamos los vuelos no asociados en dos categorías:
        //    - Llegadas internacionales (city_out_iso = 'LIM')
        //    - Salidas internacionales (city_in_iso = 'LIM')

        $this->processUnassociatedFlightsAndServices($result, $itineraryFlightIdProcess, $finalResult, $finalResultHotel, $hotels);
        // return $result;
        // Grupo 1: Vuelos de llegada internacional (terminan en LIM)
        $arrivalGroups = $unassociatedFlights
            ->filter(fn($flight) => $flight['object_code'] === 'AEIFLT' && $flight['city_out_iso'] === 'LIM')
            ->groupBy('date_in')
            ->map(function ($flightsByDate) {
                $passengers = $flightsByDate
                    ->pluck('flights')
                    ->flatten(1)
                    ->pluck('accommodations')
                    ->flatten(1)
                    ->pluck('file_passenger_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

                return [
                    'flights' => $flightsByDate->toArray(),
                    'passengers' => $passengers,
                    'date' => $flightsByDate->first()['date_in'],
                    'city' => 'LIM'
                ];
            });

        // Grupo 2: Vuelos de salida internacional (comienzan en LIM)
        $departureGroups = $unassociatedFlights
            ->filter(fn($flight) => $flight['object_code'] === 'AEIFLT' && $flight['city_in_iso'] === 'LIM')
            ->groupBy('date_in')
            ->map(function ($flightsByDate) {
                $passengers = $flightsByDate
                    ->pluck('flights')
                    ->flatten(1)
                    ->pluck('accommodations')
                    ->flatten(1)
                    ->pluck('file_passenger_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

                return [
                    'flights' => $flightsByDate->toArray(),
                    'passengers' => $passengers,
                    'date' => $flightsByDate->first()['date_in'],
                    'city' => 'LIM'
                ];
            });

        // Procesar ambos tipos de grupos
        $this->associateServicesToGroups($result, $arrivalGroups, 'arrival', $itineraryFlightIdProcess, $hotels, $finalResult, $finalResultHotel);
        $this->associateServicesToGroups($result, $departureGroups, 'departure', $itineraryFlightIdProcess, $hotels, $finalResult, $finalResultHotel);

        if(isset($result['associated_services'])){
            $associateds = $result['associated_services'][0] ?? [];
        }else{
            $associateds = [];
        }
        // ACTUALIZAR EL ITINERARIO DE LOS VUELOS
        // Si el coleccion lo convertimos en array
        if(!empty($associateds)){
            if(isset($associateds['entity'])){
                $associateds['entity'] = $associateds['entity'] instanceof Collection ? $associateds['entity']->toArray() : $associateds['entity'];
            }
            if(isset($associateds['entity'])){
                if(isset($associateds['entity']['file_itinerary_id'])){
                    $itinerary_id = $associateds['entity']['file_itinerary_id'];
                }else{
                    $itinerary_id = $associateds['entity']['id'];
                }

                if(isset($associateds['flights'])){
                    if($associateds['flights'] instanceof Collection){
                        $values = $this->findMinDepartureMaxArrivalSimple($associateds['flights']->toArray());
                    }else{
                        $values = $this->findMinDepartureMaxArrivalSimple($associateds['flights']);
                    }
                }elseif (isset($associateds['entity']['flights'])){
                    if($associateds['entity']['flights'] instanceof Collection){
                        $values = $this->findMinDepartureMaxArrivalSimple($associateds['entity']['flights']->toArray());
                    }else{
                        $values = $this->findMinDepartureMaxArrivalSimple($associateds['entity']['flights']);
                    }
                }

                FileItineraryEloquentModel::find($itinerary_id)->update([
                    'start_time'        => $values['min_departure'],
                    'departure_time'    => $values['max_arrival']
                ]);
            }elseif (isset($associateds['flights'])){
                if (!empty($associateds['flights'])) {
                    foreach($associateds['flights'] as $vuelo){
                        $itinerary_id = $vuelo['id'];
                        $values = $this->findMinDepartureMaxArrivalSimple($vuelo['flights']);
                        FileItineraryEloquentModel::find($itinerary_id)->update([
                            'start_time'        => $values['min_departure'],
                            'departure_time'    => $values['max_arrival']
                        ]);
                    }
                }
            }
        }

        $result['update_itineraries'] = array_values(array_diff($result['update_itineraries'], [$itineraryFlightIdProcess]));

        if($debug){
            return $result;
        }
    }

    protected function associateServicesToGroups(&$result, $groups, $groupType, $itineraryFlightIdProcess,$hotels, $finalResult, $finalResultHotel)
    {
        foreach ($groups as $group) {
            $compatibleServices = collect($result['unassociated_services'])
                ->filter(function ($service) use ($group, $groupType) {
                    // Validar tipo de servicio
                    $isCorrectType = $groupType === 'arrival'
                        ? ($service['hotel_destination'] == 1 && $service['hotel_origin'] == 0)
                        : ($service['hotel_origin'] == 1 && $service['hotel_destination'] == 0);

                    // Validar ciudad y fecha
                    if($groupType === 'arrival'){
                        $sameCityDate = $service['city_out_iso'] === $group['city']
                        && $service['date_in'] === $group['date'];
                    }else{
                        $sameCityDate = $service['city_in_iso'] === $group['city']
                        && $service['date_in'] === $group['date'];
                    }

                    // Validar pasajeros
                    $servicePassengers = collect($service['accommodations'])
                        ->pluck('file_passenger_id')
                        ->sort()
                        ->values()
                        ->toArray();

                    return $isCorrectType && $sameCityDate && ($servicePassengers === $group['passengers']);
                });

            if ($compatibleServices->isNotEmpty()) {
                // Calcular tiempos según el tipo de grupo
                $times = $this->calculateTimesForGroup($group['flights'], $groupType);

                foreach ($compatibleServices as $service) {
                    // Crear entrada en servicios_asociados
                    $entry = [
                        'type' => 'international_group_' . $groupType,
                        'date' => $group['date'],
                        'passengers' => $group['passengers'],
                        'service_' . ($groupType === 'arrival' ? 'next' : 'previous') => $service,
                        'flights' => $group['flights'] // Incluimos siempre los vuelos
                    ];

                    // Agregar los tiempos calculados
                    $entry = array_merge($entry, $times);

                    // AQUI VALIDAR EL TEMA DE LOS SERVICIOS PARA GUARDAR LLEGADA === POSTERIOR
                    $flightIds = collect($entry['flights'])->pluck('id')->toArray();

                    // $result['update_itineraries'] = array_merge($result['update_itineraries'],$flightIds);

                    if($groupType === 'arrival'){
                        // ACTUALIZACION DEL ITINERARIO - TIPO SERVICIO DE TRASLADO
                        if(in_array($itineraryFlightIdProcess,$flightIds) || $service['id'] === $itineraryFlightIdProcess){
                            $result['update_itineraries'][] = $service['id'];
                            FileItineraryEloquentModel::query()
                                ->where('id',$service['id'])
                                ->update([
                                    'start_time'        => $entry['arrival_time'],
                                    'departure_time'    => $entry['last_departure_time'],
                                ]);

                            // ACTUALIZACION DEL SERVICES
                            FileServiceEloquentModel::where('file_itinerary_id', $service['id'])
                                ->update([
                                    'start_time'        => $entry['arrival_time'],
                                    'departure_time'    => $entry['last_departure_time'],
                                ]);

                            if(!empty($service['services'] ?? [])){
                                foreach($service['services'] as $serv){
                                    $components = [];

                                    if($serv['type_ifx'] == 'package' && !empty($serv['compositions'] ?? []) ){
                                        foreach($serv['compositions'] as $composition){
                                            $components[] = [
                                                'code' => $composition['code'],
                                                'auto_order' => $this->findOrder($finalResult, $service['id'], $serv['id'], $composition['id']),
                                                'type_ifx' => $serv['type_ifx'],
                                                'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                                'start_time_current' => substr($composition['start_time'], 0, 5),
                                                'start_time' => substr($entry['arrival_time'], 0, 5),
                                                'departure_time' => substr($entry['last_departure_time'], 0, 5),
                                            ];
                                        }
                                    }

                                    $result['master_services'][] = [
                                        'code'                  => $serv['code'],
                                        'auto_order'            => $this->findOrder($finalResult, $service['id'], $serv['id']),
                                        'type_ifx'              => $serv['type_ifx'],
                                        'date_in'               => Carbon::parse($serv['date_in'])->format('d/m/Y'),
                                        'start_time_current'    => substr($serv['start_time'],0,5),
                                        'start_time'            => substr($entry['arrival_time'],0,5),
                                        'departure_time'        => substr($entry['last_departure_time'],0,5),
                                        'components'            => $components,
                                    ];
                                }
                            }

                            // ACTUALIZACION DE LOS COMPOSITIONS
                            $serviceIds = collect($service['services'])->pluck('id');
                            if(count($serviceIds) > 0){
                                FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                                    ->update([
                                        'start_time'        => $entry['arrival_time'],
                                        'departure_time'    => $entry['last_departure_time'],
                                    ]);
                            }
                            // if(!empty($hotels)){
                            //     foreach($hotels as $hotel){
                            //         $result['update_hotels'][] = $hotel;
                            //         $result['update_itineraries'][] = $hotel['id'];
                            //         if(!empty($hotel['rooms'] ?? [])){
                            //             foreach($hotel['rooms'] as $serv){
                            //                 $result['master_services'][] = [
                            //                     'code'                  => $hotel['object_code'],
                            //                     'auto_order'            => $this->findOrderHotel($finalResultHotel,$hotel['id'],$serv['id']),
                            //                     'type_ifx'              => 'direct',
                            //                     'date_in'               => Carbon::parse($hotel['date_in'])->format('d/m/Y'),
                            //                     'start_time_current'    => substr($hotel['start_time'],0,5),
                            //                     'start_time'            => substr($entry['last_departure_time'],0,5),
                            //                     'departure_time'        => substr($hotel['departure_time'],0,5),
                            //                     'components'            => [],
                            //                 ];
                            //             }
                            //         }

                            //         FileItineraryEloquentModel::query()
                            //         ->where('id',$hotel['id'])
                            //         ->update([
                            //             'start_time'        => $entry['last_departure_time']
                            //         ]);
                            //     }
                            // }
                        }
                    }else{
                        if(in_array($itineraryFlightIdProcess,$flightIds) || $service['id'] === $itineraryFlightIdProcess){
                            $result['update_itineraries'][] = $service['id'];
                            FileItineraryEloquentModel::query()
                                ->where('id',$service['id'])
                                ->update([
                                    'start_time'        => $entry['before_start_time'],
                                    'departure_time'    => $entry['before_departure_time'],
                                ]);

                            // ACTUALIZACION DE LOS SERVICES
                            FileServiceEloquentModel::where('file_itinerary_id', $service['id'])
                                ->update([
                                    'start_time'        => $entry['before_start_time'],
                                    'departure_time'    => $entry['before_departure_time'],
                                ]);

                            if(!empty($service['services'] ?? [])){
                                foreach($service['services'] as $serv){
                                    $components = [];

                                    if($serv['type_ifx'] == 'package' && !empty($serv['compositions'] ?? []) ){
                                        foreach($serv['compositions'] as $composition){
                                            $components[] = [
                                                'code' => $composition['code'],
                                                'auto_order' => $this->findOrder($finalResult, $service['id'], $serv['id'], $composition['id']),
                                                'type_ifx' => $serv['type_ifx'],
                                                'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                                                'start_time_current' => substr($composition['start_time'], 0, 5),
                                                'start_time' => substr($entry['before_start_time'], 0, 5),
                                                'departure_time' => substr($entry['before_departure_time'], 0, 5),
                                            ];
                                        }
                                    }

                                    $result['master_services'][] = [
                                        'code'                  => $serv['code'],
                                        'auto_order'            => $this->findOrder($finalResult, $service['id'], $serv['id']),
                                        'type_ifx'              => $serv['type_ifx'],
                                        'date_in'               => Carbon::parse($serv['date_in'])->format('d/m/Y'),
                                        'start_time_current'    => substr($serv['start_time'],0,5),
                                        'start_time'            => substr($entry['before_start_time'],0,5),
                                        'departure_time'        => substr($entry['before_departure_time'],0,5),
                                        'components'            => $components,
                                    ];
                                }
                            }

                            // ACTUALIZACION DE LOS COMPOSITIONS
                            $serviceIds = collect($service['services'])->pluck('id');
                            if(count($serviceIds) > 0){
                                FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                                    ->update([
                                        'start_time'        => $entry['before_start_time'],
                                        'departure_time'    => $entry['before_departure_time'],
                                    ]);
                            }
                        }
                    }

                    $result['associated_services'][] = $entry;

                    // Eliminar servicio de no asociados
                    $result['unassociated_services'] = collect($result['unassociated_services'])
                        ->reject(fn($s) => $s['id'] === $service['id'])
                        ->values()
                        ->toArray();
                }

                // Eliminar vuelos de no asociados (tanto para llegadas como salidas)
                $associatedFlightIds = collect($group['flights'])->pluck('id')->toArray();
                $result['unassociated_flights'] = collect($result['unassociated_flights'])
                    ->reject(fn($f) => in_array($f['id'], $associatedFlightIds))
                    ->values()
                    ->toArray();
            }
        }
    }

    protected function groupAndValidateServices($flights, $services, $direction = 'previous')
    {
        $validServices = collect();

        // Obtener todos los pasajeros de los vuelos (de todos los subvuelos)
        $flightPassengers = collect($flights)
            ->pluck('flights')
            ->flatten(1)
            ->pluck('accommodations')
            ->flatten(1)
            ->pluck('file_passenger_id')
            ->unique()
            ->sort()
            ->values();

        // Filtrar servicios según dirección (previous/next)
        $filteredServices = $services->filter(function($service) use ($direction) {
            return $direction === 'previous'
                ? ($service['hotel_origin'] == 1 && $service['hotel_destination'] == 0)
                : ($service['hotel_origin'] == 0 && $service['hotel_destination'] == 1);
        });

        // Agrupar servicios por combinación de ciudad y fecha
        $groupedServices = $filteredServices->groupBy(function($service) use ($direction) {
            return $direction === 'previous'
                ? "{$service['city_out_iso']}_{$service['date_in']}"
                : "{$service['city_in_iso']}_{$service['date_in']}";
        });

        // Validar cada grupo
        foreach ($groupedServices as $group) {
            // Obtener todos los pasajeros únicos del grupo de servicios
            $servicePassengers = $group
                ->pluck('accommodations')
                ->flatten(1)
                ->pluck('file_passenger_id')
                ->unique()
                ->sort()
                ->values();

            // Verificar coincidencia exacta de pasajeros
            if ($servicePassengers->toArray() === $flightPassengers->toArray()) {
                $validServices = $validServices->merge($group);
            }
        }

        return $validServices;
    }

    function findOrder(array $finalResult, int $itinerary_id, int $service_id, ?int $composition_id = null): int
    {
        $service = collect($finalResult)
            ->firstWhere(fn ($s) => $s['itinerary_id'] == $itinerary_id && $s['id'] == $service_id);

        if (!$service) return 1;

        if (is_null($composition_id)) {
            return $service['order'] ?? 1;
        }

        return collect($service['compositions'] ?? [])
                ->firstWhere('id', $composition_id)['order'] ?? 1;
    }

    function findOrderHotel(array $finalResultHotel, int $itinerary_id, int $room_id): int
    {
        $room = collect($finalResultHotel)
            ->first(function ($room) use ($itinerary_id, $room_id) {
                return $room['itinerary_id'] === $itinerary_id
                    && $room['id'] === $room_id;
            });

        return $room['order'] ?? 1;
    }

    protected function calculateTimesForGroup($flights, $groupType)
    {
        // Obtener todos los subvuelos
        $subflights = collect($flights)
            ->pluck('flights')
            ->flatten(1)
            ->filter(fn($subflight) => !empty($subflight['arrival_time']) && !empty($subflight['departure_time']));

        if ($groupType === 'arrival') {
            // Para llegadas: tomar el arrival_time más alto y su departure_time correspondiente
            $selectedSubflight = $subflights->sortByDesc('arrival_time')->first();
            $arrivalTime = $selectedSubflight['arrival_time'] ?? null;
            $departureTime = $selectedSubflight['departure_time'] ?? null;
        } else {
            // Para salidas: tomar el arrival_time más bajo y su departure_time correspondiente
            $selectedSubflight = $subflights->sortBy('arrival_time')->first();
            $arrivalTime = $selectedSubflight['arrival_time'] ?? null;
            $departureTime = $selectedSubflight['departure_time'] ?? null;
        }


        $flightDate = $flights[0]['date_in'];
        $last_departure_time = $this->addTime($flightDate." ".$arrivalTime,1);

        $result = [
            'arrival_time' => $arrivalTime,
            'departure_time' => $departureTime,
            'last_departure_time' => $last_departure_time
        ];

        // Calcular before_departure_time (departure_time - 3.5 horas)
        if ($departureTime) {
            $beforeDepartureTime = (new DateTime($flightDate." ".$departureTime))
                ->sub(new DateInterval('PT3H30M'))
                ->format('Y-m-d H:i:s');

            $result['before_departure_time'] = $this->normalizeTime($beforeDepartureTime, $flightDate." ".$departureTime);

            // Calcular before_start_time (before_departure_time - 1 hora)
            $beforeStartTime = (new DateTime($beforeDepartureTime))
                ->sub(new DateInterval('PT1H'))
                ->format('Y-m-d H:i:s');

            if($result['before_departure_time'] == "00:00:00"){
                $result['before_start_time'] = $this->normalizeTime($beforeStartTime, $flightDate." ".$departureTime);
            }else{
                $result['before_start_time'] = "00:00:00";
            }
        }

        return $result;
    }

    protected function normalizeTime(string $beforeDepartureTime, string $departureTime): string
    {
        try {
            $beforeDate = (new DateTime($beforeDepartureTime))->format('Y-m-d');
            $departureDate = (new DateTime($departureTime))->format('Y-m-d');

            return ($beforeDate < $departureDate) ? '00:00:00' : (new DateTime($beforeDepartureTime))->format('H:i:s');

        } catch (\Exception $e) {
            return '00:00:00';
        }
    }

    protected function addTime(string $beforeDepartureTime,int $tiempo = 1){
        try{
            $beforeStartTime = (new DateTime($beforeDepartureTime))->add(new DateInterval('PT'.$tiempo.'H'));
            $departureDate = (new DateTime($beforeDepartureTime))->format('Y-m-d');

            return ($beforeStartTime->format('Y-m-d') > $departureDate) ? '00:00:00' : $beforeStartTime->format('H:i:s');
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }

    function findMinDepartureMaxArrivalSimple(array $schedules): array
    {
        $departures = array_column($schedules, 'departure_time');
        $arrivals = array_column($schedules, 'arrival_time');

        return [
            'min_departure' => min($departures),
            'max_arrival' => max($arrivals)
        ];
    }

    function searchServiceAsociatedForPassengers($services, $flight, $flightPassengers){
        return $services->filter(function($service) use ($flight, $flightPassengers) {
            // Verificar misma fecha
            if ($service['date_in'] != $flight['date_in']) {
                return false;
            }

            // Verificar pasajeros
            $servicePassengers = collect($service['accommodations'])
                ->pluck('file_passenger_id')
                ->sort()
                ->values()
                ->toArray();

            return $servicePassengers === $flightPassengers;
        });
    }

    /**
     * Procesa vuelos y servicios no asociados para encontrar coincidencias entre pasajeros
     * y asociar servicios a subvuelos cuando corresponda.
     */
    protected function processUnassociatedFlightsAndServices(&$result, $itineraryFlightIdProcess, $finalResult, $finalResultHotel, $hotels)
    {
        // Procesar cada vuelo no asociado
        foreach ($result['unassociated_flights'] as $flightIndex => $flight) {
            $isInternational = $flight['object_code'] === 'AEIFLT';
            $isNational = $flight['object_code'] === 'AECFLT';

            // Procesar cada subvuelo del vuelo principal
            foreach ($flight['flights'] as $subflightIndex => $subflight) {
                // Obtener pasajeros de este subvuelo específico
                $subflightPassengers = collect($subflight['accommodations'])
                    ->pluck('file_passenger_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

                // Buscar servicios no asociados que coincidan con este subvuelo
                $possibleSubServices = collect($result['unassociated_services'])
                    ->filter(function($service) use ($flight, $subflightPassengers) {
                        // Misma fecha
                        if ($service['date_in'] != $flight['date_in']) {
                            return false;
                        }

                        // Mismos pasajeros
                        $servicePassengers = collect($service['accommodations'])
                            ->pluck('file_passenger_id')
                            ->sort()
                            ->values()
                            ->toArray();

                        return !empty(array_intersect($servicePassengers, $subflightPassengers));
                    });

                $subPreviousServices = collect();
                $subNextServices = collect();

                // Buscar servicios anteriores para el subvuelo
                if ($isNational || ($isInternational && !is_null($flight['city_in_iso']))) {
                    $subPreviousServices = $possibleSubServices->filter(function($service) use ($flight) {
                        return $service['hotel_origin'] == 1 &&
                            $service['hotel_destination'] == 0 &&
                            $service['city_out_iso'] == $flight['city_in_iso'];
                    });
                }

                // Buscar servicios posteriores para el subvuelo
                if ($isNational || ($isInternational && !is_null($flight['city_out_iso']))) {
                    $subNextServices = $possibleSubServices->filter(function($service) use ($flight) {
                        return $service['hotel_origin'] == 0 &&
                            $service['hotel_destination'] == 1 &&
                            $service['city_in_iso'] == $flight['city_out_iso'];
                    });
                }

                // Si encontramos servicios para asociar
                if ($subPreviousServices->isNotEmpty() || $subNextServices->isNotEmpty()) {
                    // Crear entrada para este subvuelo con sus servicios asociados
                    $subEntry = [
                        'entity' => $subflight,
                        'departure_time' => $subflight['departure_time'],
                        'max_arrival_time' => $subflight['arrival_time'],
                        'flights' => $flight['flights'], // Mantener referencia a todos los subvuelos
                        'previous_services' => [],
                        'next_services' => []
                    ];

                    // Calcular tiempos (usando la misma lógica nacional/internacional del padre)
                    if ($subflight['departure_time'] && ($isNational || $isInternational)) {
                        $hoursToSubtract = $isNational ? 2.5 : 3.5;
                        $beforeDepartureTime = (new DateTime($flight['date_in']." ".$subflight['departure_time']))
                            ->sub(new DateInterval('PT' . (int)$hoursToSubtract . 'H'))
                            ->sub(new DateInterval('PT' . (($hoursToSubtract - (int)$hoursToSubtract) * 60) . 'M'))
                            ->format('Y-m-d H:i:s');

                        $subEntry['before_departure_time'] = $this->normalizeTime($beforeDepartureTime, $flight['date_in']." ".$subflight['departure_time']);

                        if ($subEntry['before_departure_time'] != "00:00:00") {
                            $beforeStartTime = (new DateTime($beforeDepartureTime))
                                ->sub(new DateInterval('PT1H'))
                                ->format('Y-m-d H:i:s');

                            $subEntry['before_start_time'] = $this->normalizeTime($beforeStartTime, $flight['date_in']." ".$subflight['departure_time']);
                        } else {
                            $subEntry['before_start_time'] = "00:00:00";
                        }
                    }

                    $subEntry['last_start_time'] = $subflight['arrival_time'];
                    $subEntry['last_departure_time'] = $this->addTime($flight['date_in']." ".$subflight['arrival_time'], 1);

                    // Procesar servicios anteriores
                    foreach ($subPreviousServices as $service) {
                        $service['start_time'] = $subEntry['before_start_time'];
                        $service['departure_time'] = $subEntry['before_departure_time'];
                        $subEntry['previous_services'][] = $service;

                        // Actualizaciones en BD si corresponde
                        if ($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $service['id']) {
                            $this->updateServiceTimes(
                                $service,
                                $subEntry['before_start_time'],
                                $subEntry['before_departure_time'],
                                $result,
                                $finalResult,
                                $finalResultHotel,
                                $hotels
                            );
                        }
                    }

                    // Procesar servicios posteriores
                    foreach ($subNextServices as $service) {
                        $service['start_time'] = $subflight['arrival_time'];
                        $subEntry['next_services'][] = $service;

                        // Actualizaciones en BD si corresponde
                        if ($itineraryFlightIdProcess === $flight['id'] || $itineraryFlightIdProcess === $service['id']) {
                            $this->updateServiceTimes(
                                $service,
                                $subflight['arrival_time'],
                                $subEntry['last_departure_time'],
                                $result,
                                $finalResult,
                                $finalResultHotel,
                                $hotels
                            );
                        }
                    }

                    // Agregar a asociados y remover de no asociados
                    $result['associated_services'][] = $subEntry;

                    // Remover servicios asociados del array de no asociados
                    $serviceIdsToRemove = $subPreviousServices->merge($subNextServices)->pluck('id')->toArray();
                    $result['unassociated_services'] = collect($result['unassociated_services'])
                        ->reject(function($service) use ($serviceIdsToRemove) {
                            return in_array($service['id'], $serviceIdsToRemove);
                        })
                        ->values()
                        ->toArray();

                    // Actualizar el vuelo principal en el array de no asociados
                    // Si todos sus subvuelos están asociados, lo quitamos de no asociados
                    $allSubflightsAssociated = true;
                    foreach ($flight['flights'] as $sf) {
                        $sfAssociated = false;
                        foreach ($result['associated_services'] as $as) {
                            if (isset($as['entity']['id']) && $as['entity']['id'] == $sf['id']) {
                                $sfAssociated = true;
                                break;
                            }
                        }
                        if (!$sfAssociated) {
                            $allSubflightsAssociated = false;
                            break;
                        }
                    }

                    if ($allSubflightsAssociated) {
                        unset($result['unassociated_flights'][$flightIndex]);
                        $result['unassociated_flights'] = array_values($result['unassociated_flights']);
                    }
                }
            }
        }
    }

    /**
     * Actualiza los tiempos de un servicio en la base de datos y agrega a master_services
     */
    protected function updateServiceTimes($service, $startTime, $departureTime, &$result, $finalResult, $finalResultHotel, $hotels)
    {
        $result['update_itineraries'][] = $service['id'];

        // Actualizar itinerario
        FileItineraryEloquentModel::query()
            ->where('id', $service['id'])
            ->update([
                'start_time' => $startTime,
                'departure_time' => $departureTime
            ]);

        // Actualizar services
        FileServiceEloquentModel::where('file_itinerary_id', $service['id'])
            ->update([
                'start_time' => $startTime,
                'departure_time' => $departureTime
            ]);

        // Procesar servicios y composiciones para master_services
        if (!empty($service['services'] ?? [])) {
            foreach ($service['services'] as $serv) {
                $components = [];

                if ($serv['type_ifx'] == 'package' && !empty($serv['compositions'] ?? [])) {
                    foreach ($serv['compositions'] as $composition) {
                        $components[] = [
                            'code' => $composition['code'],
                            'auto_order' => $this->findOrder($finalResult, $service['id'], $serv['id'], $composition['id']),
                            'type_ifx' => $serv['type_ifx'],
                            'date_in' => Carbon::parse($composition['date_in'])->format('d/m/Y'),
                            'start_time_current' => substr($composition['start_time'], 0, 5),
                            'start_time' => substr($startTime, 0, 5),
                            'departure_time' => substr($departureTime, 0, 5)
                        ];
                    }
                }

                $result['master_services'][] = [
                    'code' => $serv['code'],
                    'auto_order' => $this->findOrder($finalResult, $service['id'], $serv['id']),
                    'type_ifx' => $serv['type_ifx'],
                    'date_in' => Carbon::parse($serv['date_in'])->format('d/m/Y'),
                    'start_time_current' => substr($serv['start_time'], 0, 5),
                    'start_time' => substr($startTime, 0, 5),
                    'departure_time' => substr($departureTime, 0, 5),
                    'components' => $components
                ];
            }
        }

        // Actualizar compositions
        $serviceIds = collect($service['services'])->pluck('id');
        if ($serviceIds->isNotEmpty()) {
            FileServiceCompositionEloquentModel::whereIn('file_service_id', $serviceIds)
                ->update([
                    'start_time' => $startTime,
                    'departure_time' => $departureTime
                ]);
        }

        // Actualizar hoteles si es un servicio posterior
        // if ($departureTime == $service['start_time'] && !empty($hotels)) {
        //     foreach ($hotels as $hotel) {
        //         $result['update_hotels'][] = $hotel;
        //         $result['update_itineraries'][] = $hotel['id'];

        //         if (!empty($hotel['rooms'] ?? [])) {
        //             foreach ($hotel['rooms'] as $serv) {
        //                 $result['master_services'][] = [
        //                     'code' => $hotel['object_code'],
        //                     'auto_order' => $this->findOrderHotel($finalResultHotel, $hotel['id'], $serv['id']),
        //                     'type_ifx' => 'direct',
        //                     'date_in' => Carbon::parse($hotel['date_in'])->format('d/m/Y'),
        //                     'start_time_current' => substr($hotel['start_time'], 0, 5),
        //                     'start_time' => substr($departureTime, 0, 5),
        //                     'departure_time' => substr($hotel['departure_time'], 0, 5),
        //                     'components' => []
        //                 ];
        //             }
        //         }

        //         FileItineraryEloquentModel::query()
        //             ->where('id', $hotel['id'])
        //             ->update([
        //                 'start_time' => $departureTime
        //             ]);
        //     }
        // }
    }
}
