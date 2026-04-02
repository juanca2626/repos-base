<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Presentation\Http\Service\OrderingService;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Presentation\Http\Service\FileFlightService;
use Src\Modules\File\Application\Mappers\FileItineraryFlightMapper;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\SearchFilePassengerQuery;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlight;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryFlightResource;
use Src\Modules\File\Application\UseCases\Commands\CreateFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryPaxCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateNumberOfPassengersCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryFlightByIdQuery;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryFlightFlightResource;
use Src\Modules\File\Application\UseCases\Commands\CreateFileItineraryFlightCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileItineraryFlightCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryFlightCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryStatusCommand;
use Src\Modules\File\Application\UseCases\Commands\FileItineraryUpdateFlightDateCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryFlightTimeCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryFlightCityIsoCommand;
use Src\Modules\File\Presentation\Http\Requests\FileItineraryFlight\CreateFileItineraryFlightRequest;
use Src\Modules\File\Presentation\Http\Requests\FileItineraryFlight\UpdateFileItineraryFlightCityIsoRequest;

class FileItineraryFlightController extends Controller
{
    use ApiResponse;

    private FileFlightService $fileFlightService;
    private OrderingService $orderingService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->fileFlightService = app()->make(FileFlightService::class);
        $this->orderingService = app()->make(OrderingService::class);
    }

    public function index(Request $request, int $fileId): JsonResponse
    {

        try {

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(CreateFileItineraryFlightRequest $request, $file_id, $file_itinerary_id): FileItineraryFlightFlightResource|JsonResponse
    {

        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            // return response()->json($file['adults']);

            if(!$file['id']){
                throw new \DomainException("the file id does not exist");
            }

            (new FileValidateStatus(
                $file['status']
            ));


            $fileItinerary = (new FindFileItineraryByIdQuery($file_itinerary_id))->handle();

            if (!$fileItinerary->id) {
                throw new \DomainException("the itinerary id does not exist");
            }

            $fileItineraryFlight = FileItineraryFlightMapper::fromRequestCreate($request, $file, $fileItinerary, (new SearchFilePassengerQuery($file['id']))->handle());
            $fileItineraryFlight = (new CreateFileItineraryFlightCommand($file_id, $fileItineraryFlight))->execute();

            $fileItinerary = (new FindFileItineraryByIdQuery($file_itinerary_id))->handle();

            // (new UpdateFileItineraryPaxCommand($file_itinerary_id))->execute();
            $resultService = $this->fileFlightService->calculateHoursService($file_id, $file_itinerary_id,$fileItinerary->dateIn->value(), true, 'flight');

            // ACTUALIZAR EL DEPARTURE_TIME Y ARRIVAL_TIME DEL ITINERARIO
            (
                new UpdateFileItineraryFlightTimeCommand($file_itinerary_id)
            )->execute();

            // if(intval($request->input('nro_pax')) < $fileItinerary->totalAdults->value() && $fileItinerary->totalAdults->value() < $file['adults']){
            //     $sum = $fileItinerary->totalAdults->value() - intval($request->input('nro_pax'));
            //     (
            //         new UpdateNumberOfPassengersCommand($file_itinerary_id, [
            //             "total_adults" => $fileItinerary->totalAdults->value() + $sum,
            //             "total_children" => $fileItinerary->totalChildren->value()
            //         ])
            //     )->execute();
            // }

            if($request->has('debug')){
                return response()->json($resultService);
            }

            // dd(new FileItineraryFlightFlightResource($fileItineraryFlight));
            // return response()->json($fileItineraryFlight->id);

            $resultOrdering = $this->orderingService->applyFlightsOrdering([
                'file_id'               => $file_id,
                'date_in'               => $fileItinerary->dateIn->value(),
                'departure_time'        => $fileItineraryFlight->departureTime->value(),
                'file_itinerary_id'     => $file_itinerary_id,
                'flight_id'             => $fileItineraryFlight->id
            ]);

            // return response()->json([
            //     'file_id'               => $file_id,
            //     'date_in'               => $fileItinerary->dateIn->value(),
            //     'departure_time'        => $fileItineraryFlight->departureTime->value(),
            //     'file_itinerary_id'     => $file_itinerary_id,
            //     'flight_id'             => $fileItineraryFlight->id
            // ]);

            if ($request->__get('flag_lambda')) {
                $response = [
                    'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/flights/".$file['file_number'],
                    'method' => 'put',
                    'stela' =>
                    [
                        [
                            "auto_order" => $resultOrdering['stella_flights'][0]['auto_order'] ?? 1, /* con esta llave (y otras más) se debe llegar al nroite 17 x ejem */
                            // "type" => substr($fileItinerary->name->value(), 0, 3),
                            "type" => substr($fileItinerary->name->value(), 0, 3) === "AEI" ? ($fileItinerary->cityOutIso->value() === "LIM" ? "ARRLIM" : ($fileItinerary->cityInIso->value() === "LIM" ? "DEPLIM" : substr($fileItinerary->name->value(), 0, 3))) : substr($fileItinerary->name->value(), 0, 3),
                            "codsvs" => substr($fileItinerary->name->value(), 0, 3) === "AEI" ? ($fileItinerary->cityOutIso->value() === "LIM" ? "ARRLIM" : ($fileItinerary->cityInIso->value() === "LIM" ? "DEPLIM" : substr($fileItinerary->name->value(), 0, 3))) : substr($fileItinerary->name->value(), 0, 3),
                            "origin" => $fileItinerary->cityInIso->value(),
                            "destiny" => $fileItinerary->cityOutIso->value(),
                            "date_current" => Carbon::parse($fileItinerary->dateIn->value())->format('d/m/Y'),
                            "date" => Carbon::parse($fileItinerary->dateIn->value())->format('d/m/Y'),
                            "departure_current" => null, // * nuevo
                            "departure" => $fileItineraryFlight->departureTime->value(),
                            "arrival" => $fileItineraryFlight->arrivalTime->value(),
                            "pnr" => $fileItineraryFlight->pnr->value(),
                            "airline" => $fileItineraryFlight->airlineCode->value(),
                            "number_flight" => $fileItineraryFlight->airlineNumber->value(),
                            "paxs" => $fileItineraryFlight->nroPax->value()
                        ]
                    ]
                ];

                $responseMaster = [
                    'update_flight'      => $response,
                    'update_hours'      =>
                    [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/services/hours",
                        'method' => 'put',
                        'stela' => [
                            'master_services' =>$resultService['master_services']
                        ]
                    ],
                    'update_itineraries'    => $resultService['update_itineraries'],
                    'update_accommodations' => [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/services/accommodations",
                        'method' => 'put',
                        "stela" => $resultOrdering['stella_passengers'] ?? []
                    ],
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $responseMaster);
            } else {
                return new FileItineraryFlightFlightResource($fileItineraryFlight);
            }
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(CreateFileItineraryFlightRequest $request, int $file_id, int $file_tinerary_id, int $file_tinerary_flight_id): FileItineraryFlightFlightResource|JsonResponse
    {

        // try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();
            if (!$file['id']) {
                throw new \DomainException("the file id does not exist");
            }

            (new FileValidateStatus(
                $file['status']
            ));

            $fileItinerary = (new FindFileItineraryByIdQuery($file_tinerary_id))->handle();
            if (!$fileItinerary->id) {
                throw new \DomainException("the itinerary id does not exist");
            }


            $fileItineraryFlightPrev = ((new FindFileItineraryFlightByIdQuery($file_tinerary_flight_id))->handle());

            $fileItineraryFlight = FileItineraryFlightMapper::fromRequestUpdate($request, $file, $fileItinerary,  (new SearchFilePassengerQuery($file['id']))->handle(), $file_tinerary_flight_id);

            $fileItineraryFlight = (new UpdateFileItineraryFlightCommand($file_id, $fileItineraryFlight))->execute();

            $fileItinerary = (new FindFileItineraryByIdQuery($file_tinerary_id))->handle();

            $resultService = $this->fileFlightService->calculateHoursService($file_id, $file_tinerary_id,$fileItinerary->dateIn->value(), true, 'flight');

            (
                new UpdateFileItineraryFlightTimeCommand($file_tinerary_id)
            )->execute();

            if($request->has('debug')){
                return response()->json($resultService);
            }

            $resultOrdering = $this->orderingService->applyFlightsOrdering([
                'file_id'               => $file_id,
                'date_in'               => $fileItinerary->dateIn->value(),
                'departure_current'     => $fileItineraryFlightPrev['departure_time'],
                'departure_time'        => $fileItineraryFlight->departureTime->value(),
                'file_itinerary_id'     => $file_tinerary_id,
                'flight_id'             => $file_tinerary_flight_id
            ]);

            // return response()->json($resultOrdering);

            if ($request->__get('flag_lambda')) {
                $response = [
                    'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/flights/".$file['file_number'],
                    'method' => 'put',
                    // 'stela' => [
                    //     "auto_order" => $resultOrdering['auto_order'] ?? 1, /* con esta llave (y otras más) se debe llegar al nroite 17 x ejem */
                    //     "type" => substr($fileItinerary->name->value(), 0, 3),
                    //     "codsvs" => substr($fileItinerary->name->value(), 0, 3),
                    //     "origin" => $fileItinerary->cityInIso->value(),
                    //     "destiny" => $fileItinerary->cityOutIso->value(),
                    //     "date_current" => Carbon::parse($fileItinerary->dateIn->value())->format('d/m/Y'),
                    //     "date" => Carbon::parse($fileItinerary->dateIn->value())->format('d/m/Y'),
                    //     "departure_current" => substr($fileItineraryFlightPrev['departure_time'], 0, 5),
                    //     "departure" => $fileItineraryFlight->departureTime->value(),
                    //     "arrival" => $fileItineraryFlight->arrivalTime->value(),
                    //     "pnr" => $fileItineraryFlight->pnr->value(),
                    //     "airline" => $fileItineraryFlight->airlineCode->value(),
                    //     "number_flight" => $fileItineraryFlight->airlineNumber->value(),
                    //     "paxs" => $fileItineraryFlight->nroPax->value()
                    // ]
                    'stela' => $resultOrdering['stella_flights'] ?? []
                ];

                $responseMaster = [
                    'update_flight'      => $response,
                    'update_hours'      =>
                    [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/services/hours",
                        'method' => 'put',
                        'stela' => [
                            'master_services' =>$resultService['master_services']
                        ]
                    ],
                    'update_itineraries' => $resultService['update_itineraries'],
                    'update_accommodations'  =>
                    [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/services/accommodations",
                        'method' => 'put',
                        "stela" => $resultOrdering['stella_passengers'] ?? []
                    ]
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $responseMaster);

            }
            else{
                return new FileItineraryFlightFlightResource($fileItineraryFlight);
            }
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function destroy(Request $request, int $file_id, int $file_tinerary_id, int $file_tinerary_flight_id): FileItineraryFlightResource|JsonResponse
    {
        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();
            if (!$file['id']) {
                throw new \DomainException("the file id does not exist");
            }

            (new FileValidateStatus(
                $file['status']
            ));

            $fileItinerary = (new FindFileItineraryByIdQuery($file_tinerary_id))->handle();
            if (!$fileItinerary->id) {
                throw new \DomainException("the itinerary id does not exist");
            }

            $flight = (new DeleteFileItineraryFlightCommand($file_id, $file_tinerary_flight_id))->execute();

            $fileItinerary = (new FindFileItineraryByIdQuery($file_tinerary_id))->handle();

            // (new UpdateFileItineraryPaxCommand($file_tinerary_id))->execute();

            $resultOrdering = $this->orderingService->applyFlightsOrdering([
                'file_id'               => $file_id,
                'date_in'               => $flight['file_itinerary']['date_in'],
                'file_itinerary_id'     => $file_tinerary_id,
                // 'flight_id'             => $file_tinerary_flight_id
            ]);

            // return response()->json($resultOrdering);

            if ($request->__get('flag_lambda')) {

                // debe de eliminar en stela tambien, se debe de usar el anular servicio, se anulara solo el flight que esta eliminado

                $response = [
                    'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/master_services",
                    'method' => 'delete',
                    'stela' => [
                        [
                            'master_services' => [
                                [
                                    // "code" => substr($flight['file_itinerary']['name'], 0, 3),
                                    "code" => substr($flight['file_itinerary']['name'], 0, 3) === "AEI" ? ($flight['file_itinerary']['city_in_iso'] === "LIM" ? "DEPLIM" : ($flight['file_itinerary']['city_out_iso'] === "LIM" ? "ARRLIM" : substr($flight['file_itinerary']['name'], 0, 3))) : substr($flight['file_itinerary']['name'], 0, 3),
                                    "auto_order" => $resultOrdering['stella_flights'][0]['auto_order'] ?? 1,
                                    "type_ifx" => "direct",
                                    "date_in" => Carbon::parse($flight['file_itinerary']['date_in'])->format('d/m/Y') ,
                                    "start_time" => substr($flight['departure_time'], 0, 5),
                                    "penalty" => 0,
                                    "components" => []
                                ]
                            ]
                        ]
                    ]
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            } else {
                return $this->successResponse(ResponseAlias::HTTP_OK, true);
            }
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateCityIso(UpdateFileItineraryFlightCityIsoRequest $request,int $file_id, int $file_itinerary_id) :JsonResponse{
        try {
            $responseUpdateCity = (new UpdateFileItineraryFlightCityIsoCommand($file_id, $file_itinerary_id, $request->input()))->execute();

            // AGRUPA Y ORDENA TODOS LOS VUELOS Y ASIGNAR EL AUTO_ORDER
            $resultOrdering = $this->orderingService->applyFlightsOrdering([
                'file_id'               => $file_id,
                'date_in'               => $responseUpdateCity['date'],
                'file_itinerary_id'     => $file_itinerary_id,
                // 'flight_id'             => $file_tinerary_flight_id
            ]);

            $response = [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/flights/".$responseUpdateCity['file_number'],
                'method' => 'put',
                'stela' => $resultOrdering['stella_flights']
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateDate(Request $request, int $file_id, int $id): JsonResponse
    {
        try {
            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();
            // AGRUPA Y ORDENA TODOS LOS VUELOS Y ASIGNAR EL AUTO_ORDER Y VIENE CON EL FORMATO PARA STELLA
            $resultOrdering = $this->orderingService->applyFlightsOrdering([
                'file_id'               => $file_id,
                'date_in'               => $fileItinerary->dateIn->value(),
                'date_update'           => $request->input('date'), // SOLO SE ENVIA CUANDO SE NECESITA CAMBIAR FECHA
                'file_itinerary_id'     => $id,
                // 'flight_id'             => $file_tinerary_flight_id
            ]);

            $responseUpdateDate = (new FileItineraryUpdateFlightDateCommand($file_id, $id, $request->input()))->execute();

            $response = [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/flights/".$responseUpdateDate['file_number'],
                'method' => 'put',
                'stela' => $resultOrdering['stella_flights']
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
