<?php
namespace Src\Modules\File\Presentation\Http\Service;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
class OrderingService {
    /**
     * @var FileRepositoryInterface|mixed
     */
    private FileRepositoryInterface $fileRepository;
    /**
     * @var FilePassengerRepositoryInterface|mixed
     */
    private FilePassengerRepositoryInterface $filePassengerRepository;

    /**
     * @throws BindingResolutionException
     */

    public function __construct()
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
        $this->filePassengerRepository = app()->make(FilePassengerRepositoryInterface::class);
    }

    public function applyRoomsOrdering(array $data): array{

        // TRAER LOS ITINERARIOS FILTRADO POR FECHAS
        $files = $this->fileRepository->queryFileDate($data['file_id'], $data['date_in']);
        $itineraries = $files['itineraries'];

        $hotels = $itineraries->where('entity','hotel')->where('status','=','1')->map(function ($value){
            return collect($value)->only(['id','entity','name','city_in_iso','city_out_iso','date_in','date_out','start_time','departure_time','total_adults','rooms','object_code']);
        });

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

        // FILTRO POR EL ITINERARIO EL ROOM Y DEVOLVER EL ORDER
        return [];
    }

    public function applyFlightsOrdering(array $data): array
    {
        $itineraries = collect($this->fileRepository->queryFileDate($data['file_id'], $data['date_in'])['itineraries'] ?? []);

        // return $itineraries->toArray();
        $result = $itineraries
        ->where('entity', 'flight')
        ->sortBy('id')
        ->flatMap(function ($itinerary) {  // Eliminado el type-hint array
            // Convertir el modelo a array si es necesario
            $itineraryArray = $itinerary instanceof \Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel
                ? $itinerary->toArray()
                : (array)$itinerary;

            $flights = collect($itineraryArray['flights'] ?? []);

            return $flights->sortBy('id')
                ->map(fn (array $flight, int $index) => [
                    'id'                => $flight['id'],
                    'auto_order'        => $flights->count() > 1 ? $index + 1 : 1,
                    'type'              => $this->codFlight(substr($itineraryArray['name'] ?? "", 0, 3), ($itineraryArray['city_in_iso'] ?? ""), ($itineraryArray['city_out_iso'] ?? "")),
                    'codsvs'            => $this->codFlight(substr($itineraryArray['name'] ?? "", 0, 3), ($itineraryArray['city_in_iso'] ?? ""), ($itineraryArray['city_out_iso'] ?? "")),
                    'origin'            => $itineraryArray['city_in_iso'] ?? "",
                    'destiny'           => $itineraryArray['city_out_iso'] ?? "",
                    "date_current"      => Carbon::parse($itineraryArray['date_in'] ?? null)->format('d/m/Y'),
                    "date"              => Carbon::parse($itineraryArray['date_in'] ?? null)->format('d/m/Y'),
                    'departure_current' => substr($flight['departure_time'],0,5),
                    'departure'         => substr($flight['departure_time'],0,5),
                    'arrival'           => substr($flight['arrival_time'],0,5),
                    'pnr'               => $flight['pnr'] ?? "",
                    'number_flight'     => $flight['airline_number'] ?? "",
                    'airline'           => $flight['airline_code'] ?? "",
                    'paxs'              => $flight['nro_pax'] ?? "",
                    'entity'            => 'flight',
                    'itinerary_id'      => $itineraryArray['id'] ?? null,
                    'accommodations'    => collect($flight['accommodations'] ?? [])->pluck('file_passenger_id'),
                ]);
        })
        ->values()
        ->all();

        // return $result;

        $flight = [];

        // DATE_UPDATE
        $date_update = $data['date_update'] ?? $data['date_in'];
        $departure_update = $data['departure_current'] ?? null;
        if($departure_update != null){
            $flight = collect($result)->sortBy(['id','asc'])->values()->map(fn (array $flight, int $index) => [
                ...$flight,
                "auto_order"        => $index + 1,
                "date"              => Carbon::parse($date_update)->format('d/m/Y'),
                "departure_current" => substr($departure_update,0,5),
            ])->toArray();
        }else{
            $flight = collect($result)->sortBy(['id','asc'])->values()->map(fn (array $flight, int $index) => [
                ...$flight,
                "auto_order"        => $index + 1,
                "date"              => Carbon::parse($date_update)->format('d/m/Y')
            ])->toArray();
        }
        // VUELOS YA AGRUPADO Y ORDENADOS

        $stella = [];
        if(isset($data['flight_id'])){
            $stella = collect($flight)->where('id', $data['flight_id'])->values();
        }else if(isset($data['file_itinerary_id'])){
            $stella = collect($flight)->where('itinerary_id', $data['file_itinerary_id'])->values();
        }else{
            $stella = collect($flight);
        }

        // return $stella;
        $stellaPassengers = ["master_services"=>[]];

        foreach($stella as $value){
            $sequence_numbers = collect($this->filePassengerRepository->searchAll($data['file_id']))
                ->sortBy('id')
                ->whereIn('id', $value['accommodations'])
                ->pluck('sequence_number');

            $stellaPassengers['master_services'][] = [
                "code" => $value['codsvs'],
                "is_component" => false,
                "date_in" => Carbon::createFromFormat('d/m/Y',$value['date'])->format('d-m-Y'),
                "start_time" => $value['departure'],
                "auto_order" => $value['auto_order'] ?? 1,
                "sequence_numbers" => $sequence_numbers ?? []
            ];
        }

        return [
            "stella_flights"    => collect($stella)->map(function ($item) {
                return collect($item)->only(['auto_order','type','codsvs','origin','destiny','date_current','date','departure_current','departure','arrival','pnr','airline','number_flight','paxs']);
            })->toArray(),
            "stella_passengers" => $stellaPassengers
        ];
    }

    public function sequenceNumberOrdering(array $data): array {
        $file_id = $data['file_id'];
        $date_in = $data['date_in'];
        $itinerary_id = $data['file_itinerary_id'];

        $itineraries = collect($this->fileRepository->queryFileDate($file_id, $date_in)['itineraries'] ?? []);

        $service = $itineraries->where('id', $itinerary_id)->first();
        $accommodations = $service->accommodations->toArray();
        $sequence_numbers = [];

        foreach($accommodations as $accommodation){
            $sequence_numbers[] = $accommodation['file_passenger']['sequence_number'];
        }

        sort($sequence_numbers);

        return $sequence_numbers;
    }

    protected function findOrderHotel(array $finalResultHotel, int $itinerary_id, int $room_id): int
    {
        $room = collect($finalResultHotel)
            ->first(function ($room) use ($itinerary_id, $room_id) {
                return $room['itinerary_id'] === $itinerary_id
                    && $room['id'] === $room_id;
            });

        return $room['order'] ?? 1;
    }

    protected function codFlight(string $cod, string $origin, string $destiny): string
    {
        if ($cod === "AEI") {
            return $this->getAeiFlightCode($origin, $destiny);
        }

        return $cod;
    }

    private function getAeiFlightCode(string $origin, string $destiny): string
    {
        if ($destiny === "LIM") {
            return "ARRLIM";
        }

        if ($origin === "LIM") {
            return "DEPLIM";
        }

        return "AEI"; // Valor por defecto si no cumple ninguna condición
    }
}
