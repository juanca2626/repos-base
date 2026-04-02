<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryScheduleCommand;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceByIdQuery;
use Src\Modules\File\Application\UseCases\Commands\CreateFileServiceAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileServiceAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitStatusCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryHotelByCancellation;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryServiceByCancellation;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileHotelRoomMapper;
use Src\Modules\File\Application\UseCases\Commands\AssociateTemporaryServiceFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\CancelFileHotelRoomCommand;
use Src\Modules\File\Application\UseCases\Commands\CancelFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\CancelFileItineraryFlightCommand;
use Src\Modules\File\Application\UseCases\Commands\CancelFileServiceCommand;
use Src\Modules\File\Application\UseCases\Commands\CreateFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileItineraryAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\FileCancelCommand;
use Src\Modules\File\Application\UseCases\Commands\GetCancelItineraryRoomUnitCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateConfirmationStatusFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryAmountSaleCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryStatusCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceCompositionAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceCompositionStatusCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceUnitAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceUnitStatusCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateNumberOfPassengersCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileTemporaryServiceByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileAmountTypeFlagLockedQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileItineraryFlightQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchItineraryServiceTemporaryQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchTokenHotelsQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachAuroraInformation;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCommunication;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCommunicationHeader;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlight;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileTemporaryServiceForClient;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryFlightResource;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryResource;
use Src\Modules\File\Presentation\Http\Traits\CommunicationServiceTemplate;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Exports\FlightExport;
use Src\Modules\File\Application\UseCases\Commands\FileItineraryViewProtectedRateCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomAmountCancellationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitAmountSaleCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryAddStatementCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateManuallyStatementCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdArrayQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileLatestItineriesQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFilePassengerQuery;
use Src\Modules\File\Presentation\Http\Resources\FileItineraryLasterResource;
use Src\Modules\File\Presentation\Http\Traits\RoundLito;
use Src\Modules\File\Presentation\Http\Service\FileFlightService;
use Src\Modules\File\Presentation\Http\Traits\AuthUser;
use Src\Modules\File\Presentation\Http\Traits\ServiceAutoOrder;
use Src\Modules\File\Presentation\Http\Service\OrderingService;
class FileItineraryController extends Controller
{
    use ApiResponse, CommunicationServiceTemplate,RoundLito, AuthUser, ServiceAutoOrder;

    private FileFlightService $fileFlightService;
    private OrderingService $orderingService;

    public function __construct(){
        $this->fileFlightService = app()->make(FileFlightService::class);
        $this->orderingService = app()->make(OrderingService::class);
    }

    public function index(Request $request, int $fileId): JsonResponse
    {

        try {

            try {

                return $this->successResponse(ResponseAlias::HTTP_OK, []);
            } catch (\DomainException $domainException) {
                return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }



    public function itinerary_flights(Request $request, int $fileId): JsonResponse
    {

        try {
            $itinerary_flight = (new SearchFileItineraryFlightQuery($fileId))->handle();
            $itinerary_flights = FileItineraryFlightResource::collection($itinerary_flight);
            return $this->successResponse(ResponseAlias::HTTP_OK, $itinerary_flights);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function exportItineraryFlights(Request $request,int $fileId)
    {

        try {
            $itinerary_flight = (new SearchFileItineraryFlightQuery($fileId))->handle();

            $itinerary_flights = FileItineraryFlightResource::collection($itinerary_flight);

            return Excel::download(new FlightExport($itinerary_flights), 'itinerary_flights.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(Request $request, $file_id): FileItineraryFlightResource|JsonResponse
    {

        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            (new FileValidateStatus(
                $file['status']
            ));

            $newFileItinerary = FileItineraryMapper::fromRequestCreate($request, $file);

            $filItinerary = (new CreateFileItineraryCommand($newFileItinerary))->execute();


            $fileItinerary = (new FindFileItineraryByIdArrayQuery($filItinerary['id'], "array"))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileItinerary['file_id']))->handle())['status']
            ));

            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($filItinerary['id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($filItinerary['id']))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary['file_id']))->execute();



            if($request->__get('flag_lambda'))
            {
                $response = ['stella' => []];

                if($request->input('entity') != 'flight'){ // al crear el itinerario no enviamos nada a stela

                    // evaluar si se envia servicios a stela

                    // $response = [
                    //     'stella' => new FileItineraryFlight($filItinerary)
                    // ];
                }

                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            }else{
                return new FileItineraryFlightResource($filItinerary);
            }



        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function update(Request $request,int $file_id, int $file_tinerary_id): FileItineraryFlightResource|JsonResponse
    {
        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            (new FileValidateStatus(
                $file['status']
            ));

            $fileItinerary = (new FindFileItineraryByIdQuery($file_tinerary_id))->handle();
            if(!$fileItinerary->id){
                throw new \DomainException("the itinerary id does not exist");
            }

            $fileItinerary = FileItineraryMapper::fromRequestUpdate($request, $file, $fileItinerary);
            $filItinerary = (new UpdateFileItineraryCommand($fileItinerary, $file_tinerary_id))->execute();

            if($request->__get('flag_lambda'))
            {

                $response = ['stella' => []];
                if($request->input('entity') == 'flight'){
                    // aqui se deberiamos de enviar todos los flight de un itinerario para stela
                    // pero ya no enviaremos por aqui porque hay otro endpoint que actualiza la informacion FileItineraryFlightController::updateCityIso

                    // $stela = (new FileItineraryFlight($filItinerary))->jsonSerialize();
                    // if(count($stela)>0)
                    // {
                    //     $response = [
                    //         'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/flights/".$file['file_number'],
                    //         'method' => 'put',
                    //         'stela' => $stela
                    //     ];
                    // }
                }

                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            }else{
                return new FileItineraryFlightResource($filItinerary);
            }

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(Request $request, int $file_id, int $id): FileItineraryFlightResource|JsonResponse
    {
        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            (new FileValidateStatus(
                $file['status']
            ));

            $itinerary = (new FindFileItineraryByIdQuery($id))->handle();
            if(!$itinerary->id){
                throw new \DomainException("the itinerary id does not exist");
            }

            if($itinerary->entity->value() == 'flight'){

                $response = [];
                $filItinerary = (new CancelFileItineraryFlightCommand($id))->execute();

                // debe de eliminar en stela y sea hace con el proceso de anular servicio, aqui debe de anular todos los items del flights

                // if($request->__get('flag_lambda'))
                // {
                //     $response = [
                //         'stella' => new FileItineraryFlight($filItinerary)
                //     ];
                // }

            }else{
                $response = (new UpdateFileItineraryStatusCommand($id, 0))->execute();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update_schedule(int $id, Request $request): JsonResponse
    {
        try {

            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle())['status']
            ));

            $params = FileItineraryMapper::fromRequestUpdateSchedule($request);
            $response = (new UpdateFileItineraryScheduleCommand($id, $params))->execute();
            //$master_services = (new SearchMasterServiceByIdQuery(['file_itinerary_id' => $id]))->handle();

            $response = [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$response['file_number']."/services/hours",
                'method' => 'put',
                'stela' => [
                    'master_services' =>$response['stela']
                ]
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update_status_rate(Request $request): void
    {
        //
    }

    public function update_status_rate_neg_locked(Request $request): void
    {
        //
    }

    public function update_passengers(int $id, Request $request): JsonResponse
    {

        // try {

            $passengers = FileItineraryMapper::fromRequestUpdatePassengers($request);
            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();
            $file = (new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle();

            (new FileValidateStatus(
                $file['status']
            ));

            foreach($fileItinerary->services as $service) {
                foreach($service->compositions as $composition) {
                    foreach($composition->units as $unit) {
                        (new DeleteFileServiceAccommodationCommand($unit->id))->execute();
                        foreach($passengers as $passenger) {
                            (new CreateFileServiceAccommodationCommand($unit->id, $passenger, ''))->execute();
                        }
                    }
                }
            }

            $response = (new UpdateFileItineraryAccommodationCommand($id, $passengers))->execute();

            // SE AGREGO ACTUALIZACION DEL HORARIO DE LOS VUELOS - AQUI DEBERIA ACTUALIZAR CUANDO EL SERVICIO ES TRASLADO
            if(str_contains(strtolower($fileItinerary->name->value()),'traslado') && (str_contains(strtolower($fileItinerary->name->value()),'hotel') || str_contains(strtolower($fileItinerary->name->value()),'aeropuerto'))){
                $resultService = $this->fileFlightService->calculateHoursService($fileItinerary->fileId->value(), $id,$fileItinerary->dateIn->value(), true);
            }

            // if($request->has('debug')){
            //     return response()->json($resultService);
            // }

            if($request->__get('flag_lambda'))
            {
                $master_services = (new SearchMasterServiceByIdQuery([
                    'file_itinerary_id' => $id, 'accommodations' => true, 'order' => true
                ]))->handle();

                $response = [];

                if(count($master_services)>0)
                {
                    $file_passengers = (new SearchFilePassengerQuery($fileItinerary->fileId->value()))->handle();
                    $file_passengers = collect($file_passengers)->sortBy('id')->pluck('id')->toArray();
                    $sequence_numbers = $this->orderingService->sequenceNumberOrdering([
                        'file_id'               => $fileItinerary->fileId->value(),
                        'date_in'               => $fileItinerary->dateIn->value(),
                        'file_itinerary_id'     => $id
                    ]);
                    // foreach($passengers as $passenger) {

                    //     if(array_search($passenger, $file_passengers) === null)
                    //     {
                    //         throw new \DomainException('the passenger does not belong to the file');
                    //     }

                    //     array_push($sequence_numbers,  array_search($passenger, $file_passengers) + 1);
                    // }



                    $stela = [];
                    foreach($master_services  as $master_service)
                    {
                        array_push($stela, [
                            'code' => $master_service['code'],
                            'is_component' => false,
                            'date_in' => Carbon::parse($master_service['date_in'])->format('d-m-Y') ,
                            'start_time' => $master_service['start_time'],
                            'auto_order' => $master_service['auto_order'],
                            "sequence_numbers" => $sequence_numbers
                        ]);
                    }


                    $response = [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/services/accommodations",
                        'method' => 'put',
                        'stela' => [
                            "master_services" => $stela
                        ]
                    ];

                }

                if($request->has('debug')){
                    return response()->json($response);
                }

                $responseMaster = [
                    'update_passengers'     => $response,
                    'update_hours'          =>
                    [
                        'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$fileItinerary->fileId->value()."/services/hours",
                        'method' => 'put',
                        'stela' => [
                            'master_services' =>$resultService['master_services'] ?? []
                        ]
                    ],
                    'update_itineraries' => $resultService['update_itineraries'] ?? []
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $responseMaster);
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function update_number_of_passengers(int $id, Request $request): JsonResponse
    {
        try {

            $passengers = FileItineraryMapper::fromRequestUpdatePassengers($request);
            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle())['status']
            ));

            $response = (new UpdateNumberOfPassengersCommand($id, $request->input()))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function show(Request $request, $file_id, $id): FileItineraryResource
    {
        // try {

            $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, "array"))->handle();
            return new FileItineraryResource($fileItinerary);

        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function cancel_hotels($id, Request $request): JsonResponse
    {
        try {
            $response = DB::transaction(function () use ($id, $request) {

                $status_reason_id = $request->input('status_reason_id') ? $request->input('status_reason_id') : 6;
                $file_amount_reason_id = $request->input('file_amount_reason_id') ? $request->input('file_amount_reason_id') : 15; // 15 = cancelation sin penalidad
                $executive_id = $request->input('executive_id') ? $request->input('executive_id') : NULL;
                $file_id = $request->input('file_id') ? $request->input('file_id') : NULL;
                $motive = $request->input('motive') ? $request->input('motive') : "";
                $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, "array"))->handle();
                $file = (new FindFileByIdAllQuery($fileItinerary['file_id']))->handle();
                $file['itineraries'] = [];
                array_push($file['itineraries'], $fileItinerary);

                (new FileValidateStatus(
                    $file['status']
                ));

                $fileCancellation = (
                    new SerachFileItineraryHotelByCancellation($file, $id, $request->input('rooms'))
                )->handle();

                foreach($fileCancellation['itineraries']['rooms'] as $room) {
                    //debemos de crear un log en rooms por cada unit que se cancele
                    $new_amount_cost_rooms = $room['amount_sale'];  // se cambio de amount_cost a amount_sale porque las cancelaciones influyen a la venta
                    foreach($room['units'] as $unit) {

                        $penality = $unit['penalty']['penalty_cost'];
                        if($file_amount_reason_id == "12"){
                            $penality = 0;
                        }


                        //cancelamos el unit
                        (new UpdateFileHotelRoomUnitStatusCommand($unit['id'], 0))->execute();
                        //actualizamos el amount_cost del unit
                        (new UpdateFileHotelRoomUnitAmountSaleCommand($unit['id'], $penality))->execute();  // se cambio UpdateFileHotelRoomUnitAmountCostCommand  UpdateFileHotelRoomUnitAmountSaleCommand

                        //actualizamos el monto del room por cada unit que cancelemos
                        $new_amount_cost_rooms = $penality;
                        // $new_amount_cost_rooms = $new_amount_cost_rooms - ($unit['amount_sale'] + $penality);  // se cambio de amount_cost a amount_sale porque las cancelaciones influyen a la venta

                        $fileAmountTypeFlags = (new SearchFileAmountTypeFlagLockedQuery())->handle();
                        $params = [
                            'file_amount_reason_id' => $file_amount_reason_id,
                            'executive_id' => $executive_id,
                            'file_id' => $file_id,
                            'motive' => $motive,
                            'file_amount_type_flag_id' => $fileAmountTypeFlags['id'],
                            'new_amount' => $new_amount_cost_rooms,
                            'module' => 'cancellation'
                        ];
                        $params = FileHotelRoomMapper::fromArrayUpdateAmountCost($params);
                        (new UpdateFileHotelRoomAmountCancellationCommand($room['id'], $params))->execute(); // aqui actualizamos precios y logs
                    }
                    //cancelamos el rooms
                    (new CancelFileHotelRoomCommand($room['id']))->execute();
                }
                //cancelamos el itinerario
                (new CancelFileItineraryCommand($fileCancellation['itineraries']['id']))->execute();
                // actualizamos el total_amount itinerario
                (new UpdateFileItineraryTotalAmountCommand($fileCancellation['itineraries']['id']))->execute();
                // actualizamos el profitability itinerario
                (new UpdateFileItineraryProfitabilityCommand($fileCancellation['itineraries']['id']))->execute();
                // actualizamos el file statement
                (new UpdateFileStatementCommand($fileCancellation['id']))->execute();
                // cancelamos el file solo si todos los items de los itinerarios estan cancelados
                (new FileCancelCommand($fileCancellation['id'], $status_reason_id ))->execute();

                return [
                    'stella' => $fileCancellation['itineraries']['stella'],
                    'hyperguest' => $fileCancellation['hyperguest']
                ];
            });

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function communication_hotel_cancellation($id, Request $request)
    {
        try {
            $subject = "Cancelación de Reserva"; $html = ""; $executive_email = ''; $hotel_contacts = [];

            $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, "array"))->handle();
            $file = (new FindFileByIdAllQuery($fileItinerary['file_id']))->handle();

            $file['itineraries'] = [];
            array_push($file['itineraries'], $fileItinerary);

            $fileItineraryByCancellation = (
                new SerachFileItineraryHotelByCancellation($file, $id, $request->input('rooms'))
            )->handle();
            if(count($fileItineraryByCancellation)) {
                $html = view('emails.reservations.hotels.cancellation', [
                    "file" => $fileItineraryByCancellation,
                    "notas" => $request->input('notas'),
                    "attachments" => $request->input('attachments')
                ])->render();

                        // return view('emails.reservations.hotels.cancellation', [
                        //     "file" => $fileItineraryByCancellation,
                        //     "notas" => $request->input('notas'),
                        //     "attachments" => $request->input('attachments')
                        // ]);

                $executive_email = $fileItineraryByCancellation['executive_email'];
                $hotel_contacts = $fileItineraryByCancellation['hotel_contacts'];
            }

            $response = [
                'html' => [
                    'hotel' => $html,
                    'executive' => $html,
                ],
                'subject' => $subject,
                'executive_email' => [$executive_email],
                'hotel_contacts' => $hotel_contacts,
                // 'rooms' => $fileItineraryByCancellation['itineraries']['rooms'],
                // 'hyperguest' => $fileItineraryByCancellation['hyperguest']
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }


    public function cancel_services($id, Request $request): JsonResponse
    {

        // try {
            $response = DB::transaction(function () use ($id, $request) {
                $stella = [];
                $status_reason_id = $request->input('status_reason_id') ? $request->input('status_reason_id') : null;
                $file_amount_reason_id = $request->input('file_amount_reason_id') ? $request->input('file_amount_reason_id') : 15; // 15 = cancelation sin penalidad
                $executive_id = $request->input('executive_id') ? $request->input('executive_id') : NULL;
                $file_id = $request->input('file_id') ? $request->input('file_id') : NULL;
                $motive = $request->input('motive') ? $request->input('motive') : "";
                $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, "array"))->handle();
                $file = (new FindFileByIdAllQuery($fileItinerary['file_id']))->handle();

                (new FileValidateStatus(
                    $file['status']
                ));

                if(in_array($fileItinerary['entity'], ['service', 'service-temporary'])){

                    $file['itineraries'] = [];
                    array_push($file['itineraries'], $fileItinerary);

                    $fileCancellation = (
                        new SerachFileItineraryServiceByCancellation($file, $id, $request->input('services'))
                    )->handle();

                    foreach($fileCancellation['itineraries']['services'] as $service) {

                        $auto_order = $this->getServiceAutoOrder($file['id'], $service['id'], $service['code'], $service['date_in']);

                        $stellaService = [
                            "code" => $service['code'],
                            "auto_order" => $auto_order,
                            "type_ifx" => $service['type_ifx'],
                            "date_in" => Carbon::parse($service['date_in'])->format('d/m/Y') ,
                            "start_time" => substr($service['start_time'], 0, 5),
                            "penalty" => 0,
                            "components" => []
                        ];

                        //debemos de crear un log en rooms por cada unit que se cancele
                        $new_amount_cost_service = $service['amount_cost'];
                        foreach($service['compositions'] as $composition) {
                            $penality = $composition['penalty']['penality_cost'];
                            if($file_amount_reason_id == "12"){
                                $penality = 0;
                            }

                            $stellaService["penalty"] = $penality;
                            if($service['type_ifx'] == 'package'){
                                array_push($stellaService['components'], [
                                    "code" => $composition['code'],
                                    "auto_order" => 1,
                                    "type_ifx" => "component",
                                    "date_in" => Carbon::parse($composition['date_in'])->format('d/m/Y') ,
                                    "start_time" => substr($composition['start_time'], 0, 5),
                                    "penalty" => 0
                                ]);
                            }

                            foreach($composition['units'] as $unit) {
                                //cancelamos el unit
                                (new UpdateFileServiceUnitStatusCommand($unit['id'], 0))->execute();
                                //actualizamos el amount_cost del unit
                                (new UpdateFileServiceUnitAmountCostCommand($unit['id'], $penality))->execute();
                            }

                            //cancelamos el composition
                            (new UpdateFileServiceCompositionStatusCommand($composition['id'], 0))->execute();
                            //actualizamos el amount_cost del composition
                            (new UpdateFileServiceCompositionAmountCostCommand($composition['id'], $penality))->execute();
                            //actualizamos el monto del service por cada composition que cancelemos
                            // no hay penalidad por servicio ni por composition
                            // $new_amount_cost_service = $new_amount_cost_service - ($unit['amount_cost'] + $unit['penality']);
                            $new_amount_cost_service = ($new_amount_cost_service - $composition['amount_cost']) + $penality;
                            $fileAmountTypeFlags = (new SearchFileAmountTypeFlagLockedQuery())->handle();
                            $params = [
                                'file_amount_reason_id' => $file_amount_reason_id,
                                'executive_id' => $executive_id,
                                'file_id' => $file_id,
                                'motive' => $motive,
                                'file_amount_type_flag_id' => $fileAmountTypeFlags['id'],
                                'new_amount' => $new_amount_cost_service
                            ];
                            $params = FileHotelRoomMapper::fromArrayUpdateAmountCost($params);
                            (new UpdateFileServiceAmountCostCommand($service['id'], $params))->execute();
                        }

                        array_push($stella, $stellaService);

                        //cancelamos el service
                        (new CancelFileServiceCommand($service['id']))->execute();
                    }
                }

                //cancelamos el itinerario
                (new CancelFileItineraryCommand($fileItinerary['id']))->execute();
                // actualizamos el total_amount itinerario
                (new UpdateFileItineraryTotalAmountCommand($fileItinerary['id']))->execute();    // (new UpdateFileItineraryTotalCostAmountCommand($fileItinerary->id))->execute();
                // actualizamos el profitability itinerario
                (new UpdateFileItineraryProfitabilityCommand($fileItinerary['id']))->execute();
                // actualizamos el file statement
                (new UpdateFileStatementCommand($fileItinerary['file_id']))->execute();
                // cancelamos el file solo si todos los items de los itinerarios estan cancelados
                (new FileCancelCommand($fileItinerary['file_id'], $status_reason_id ))->execute();

                return [
                    'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$file['file_number']."/master_services",
                    'method' => 'delete',
                    'stella' => [
                        'master_services' => $stella
                    ]
                ];
            });

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        // }
    }

    public function cancel_services_validate($id, Request $request): JsonResponse
    {

        // try {
            $response = DB::transaction(function () use ($id, $request) {
                $stella = [];
                $file_amount_reason_id = $request->input('file_amount_reason_id') ? $request->input('file_amount_reason_id') : 15; // 15 = cancelation sin penalidad
                $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, "array"))->handle();
                $file = (new FindFileByIdAllQuery($fileItinerary['file_id']))->handle();

                (new FileValidateStatus(
                    $file['status']
                ));

                if(in_array($fileItinerary['entity'], ['service', 'service-temporary'])){
                    $file['itineraries'] = [];
                    array_push($file['itineraries'], $fileItinerary);
                    $fileCancellation = (
                        new SerachFileItineraryServiceByCancellation($file, $id, $request->input('services'))
                    )->handle();

                    foreach($fileCancellation['itineraries']['services'] as $service) {

                        $auto_order = $this->getServiceAutoOrder($file['id'], $service['id'], $service['code'], $service['date_in']);

                        $stellaService = [
                            "code" => $service['code'],
                            "auto_order" => $auto_order,
                            "type_ifx" => $service['type_ifx'],
                            "date_in" => Carbon::parse($service['date_in'])->format('d/m/Y') ,
                            "start_time" => substr($service['start_time'], 0, 5),
                            "penalty" => 0,
                            "components" => []
                        ];

                        //debemos de crear un log en rooms por cada unit que se cancele
                        $new_amount_cost_service = $service['amount_cost'];
                        foreach($service['compositions'] as $composition) {
                            $penality = $composition['penalty']['penality_cost'];
                            if($file_amount_reason_id == "12"){
                                $penality = 0;
                            }

                            $stellaService["penalty"] = $penality;
                            if($service['type_ifx'] == 'package'){
                                array_push($stellaService['components'], [
                                    "code" => $composition['code'],
                                    "auto_order" => 1,
                                    "type_ifx" => "component",
                                    "date_in" => Carbon::parse($composition['date_in'])->format('d/m/Y') ,
                                    "start_time" => substr($composition['start_time'], 0, 5),
                                    "penalty" => 0
                                ]);
                            }
                        }
                        array_push($stella, $stellaService);
                    }
                }

                return [
                    'file_number' => $file['file_number'],
                    'stella' => $stella
                ];
            });

            if($request->has('v')){
                return response()->json([$response['file_number'],$response['stella']]);
            }

            $file_number = $response['file_number'];
            $master_services = $response['stella'];

            try {
                
                $stella = new ApiGatewayExternal();
                $response = (array) $stella->delete_master_services_validate($file_number, ['master_services' => $master_services]);

                $response['params_stella'] = [
                    'number_file'=> $file_number,
                    'master_services'=> $master_services
                ];

                return $this->successResponse(ResponseAlias::HTTP_OK, $response);

            }catch (\GuzzleHttp\Exception\ServerException $e) {
                $response = $e->getResponse();
                $response = json_decode($response->getBody()->getContents(),true);

                if(array_key_exists("data", $response)){

                    if(array_key_exists("message",$response['data'])){
                        $response['message'] = $response['data']['message'];
                    }

                    if(array_key_exists("master_services",$response['data'])){
                        $response['master_services'] = $response['data']['master_services'];
                    }
                }

                $response['params_stella'] = [
                    'number_file'=> $file_number,
                    'master_services'=> $master_services
                ];

                return $this->errorResponse($response, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $response = json_decode($response->getBody()->getContents(),true);

                if(array_key_exists("data", $response)){

                    if(array_key_exists("message",$response['data'])){
                        $response['message'] = $response['data']['message'];
                    }

                    if(array_key_exists("master_services",$response['data'])){
                        $response['master_services'] = $response['data']['master_services'];
                    }
                }

                $response['params_stella'] = [
                    'number_file'=> $file_number,
                    'master_services'=> $master_services
                ];

                return $this->errorResponse($response, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }



        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        // }
    }

    public function communication_update_service(Request $request, $file_id, $file_itinerary_id)
    {
        try
        {
            $adults = $request->input('total_adults') ;
            $children = $request->input('total_children');
            $services_news = $this->getServicesMaster($request->input());

            if(count($services_news) == 0){
                throw new \DomainException('the service does not exist');
            }

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $serachAuroraInformation = (new SerachAuroraInformation([
                'executive_code' => $file['executive_code'],
                'hotel_id' => '',
                'client_id' => $file['client_id']
            ]))->handle();

            $file_header = [
                'file_number' => $file['file_number'],
                'description' => $file['description'],
                'created_at' => date('Y-m-d'),
                'adults' => $adults,
                'children' => $children,
                'infants' => 0
            ];
            $file_header +=  $serachAuroraInformation;

            $results = (new FileItineraryServiceByCommunication($file , [], [], $services_news[0]['master_services'], $file_itinerary_id))->jsonSerialize();

            $results =  $this->communication_modification_html($file_header, $results['updates'], "emails.reservations.services.reservation_solicitude");

            return $this->successResponse(ResponseAlias::HTTP_OK, $results);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function communication_cancellation_service(Request $request, $file_id, $file_itinerary_id)
    {
        try
        {
            $file = (new FindFileByIdAllQuery($file_id))->handle();
            $deletes = [];
            foreach($file['itineraries'] as $itinerary){
                if($itinerary["id"] == $file_itinerary_id){
                    foreach($itinerary["services"] as $service){
                        array_push($deletes, $service['id']);
                    }
                }
            }

            $serachAuroraInformation = (new SerachAuroraInformation([
                'executive_code' => $file['executive_code'],
                'hotel_id' => '',
                'client_id' => $file['client_id']
            ]))->handle();

            $file_header = (new FileItineraryServiceByCommunicationHeader($file, $file_itinerary_id , $serachAuroraInformation))->jsonSerialize();

            $file_header +=  $serachAuroraInformation;

            $results = (new FileItineraryServiceByCommunication($file , [], $deletes, []))->jsonSerialize();

            $results =  $this->communication_html($file_header, $results['deletes'], "emails.reservations.services.cancellation_new");

            return $this->successResponse(ResponseAlias::HTTP_OK, $results);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function communication_service_cancellation__________eliminado($id, Request $request)
    {
        try {            
            $stella = new ApiGatewayExternal();

            $subject = "Cancelación de Reserva";
            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();
            $file = (new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle();

            $fileItineraryByCancellation = (
                new SerachFileItineraryServiceByCancellation($file, $id, $request->input('services'))
            )->handle();
            if(count($fileItineraryByCancellation)) {

                $compositions = [];

                foreach($fileItineraryByCancellation['itineraries']['services'] as $service){
                    foreach($service['compositions'] as $composition){

                        $supplier = 'No supplier';
                        $supplier_emails = [];
                        if(isset($composition['supplier']['code_request_book'])){
                            $masterServices = (array) $stella->getSuppliers($composition['supplier']['code_request_book']);
                            if(isset($masterServices) and count($masterServices)>0){
                                $masterServices = (object) $masterServices[0];
                                $supplier = $masterServices->razon;
                                $emails = [];
                                foreach($masterServices->contacts as $contact){
                                    array_push($emails, $contact->email);
                                }
                                $supplier_emails = $emails;

                            }
                        }

                        array_push($compositions, [
                            'code' => $composition['code'],
                            'name' => $composition['name'],
                            'supplier' => $supplier,
                            'supplier_emails' => $supplier_emails,
                            'html' =>  view('emails.reservations.services.cancellation', [ //aqui validar cancellation_new
                                "file" => $fileItineraryByCancellation,
                                "service" => $service,
                                "composition" => $composition,
                                "notas" => $request->input('notas'),
                                "attachments" => $request->input('attachments')
                            ])->render()
                        ]);

                        // return view('emails.reservations.services.cancellation', [
                        //     "file" => $fileItineraryByCancellation,
                        //     "service" => $service,
                        //     "composition" => $composition,
                        //     "notas" => $request->input('notas'),
                        //     "attachments" => $request->input('attachments')
                        // ]);

                    }
                }

            }

            $response = [
                'compositions' => $compositions,
                'subject' => $subject
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }


    public function update_amount_sale(int $id, Request $request): JsonResponse
    {
        try {
            $params = FileItineraryMapper::fromRequestUpdateAmountCost($request);

            if($params['file_amount_type_flag_id'] === 0) {
                $fileAmountTypeFlags = (new SearchFileAmountTypeFlagLockedQuery())->handle();
                $params['file_amount_type_flag_id'] = $fileAmountTypeFlags['id'];
            }

            $response = (new UpdateFileItineraryAmountSaleCommand($id, $params))->execute();
            $fileItinerary = (new FindFileItineraryByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileItinerary->fileId->value()))->handle())['status']
            ));

            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($id))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($id))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();



            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function communication_hotel_modification(Request $request, $id)
    {
        try {
            $params = $request->input();
            $params['file_itinerary_id'] = $id;
            $fileItinerary = (new FindFileItineraryByIdArrayQuery($id, 'array'))->handle();

            $file = (new FindFileByIdAllQuery($fileItinerary['file_id']))->handle();
            $file['itineraries'] = [];
            array_push($file['itineraries'], $fileItinerary);

            // cancelations
            $reservation_delete = $params['reservation_delete'];

            $roomsUnitForCancelation =  (new GetCancelItineraryRoomUnitCommand($id))->handle();
            $fileItineraryByCancellation = (
                new SerachFileItineraryHotelByCancellation($file, $id, $roomsUnitForCancelation)
            )->handle();


            // new reservation
            $dataSearchHotel = (new SearchTokenHotelsQuery($params, $file))->handle();

            // return $this->successResponse(ResponseAlias::HTTP_OK, $dataSearchHotel);
            // if($fileItineraryByCancellation['itineraries']['object_id'] != $dataSearchHotel['itineraries']['hotel_id']){
            //     throw new \DomainException("In this process it is required that the 2 hotels are the same");
            // }

            // return view("emails.reservations.hotels.reservation.modification", ["reservation" => $dataSearchHotel, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"] );

            $html_hotel = "";
            $html_executive = "";
            $subject = "Modificación de reserva [".$dataSearchHotel['file_number']."] - ".$dataSearchHotel['itineraries']['hotel_code']."]."."-".$dataSearchHotel['customer_name'];

            if(count($dataSearchHotel)>0){
                $html_hotel = view("emails.reservations.hotels.reservation.modification", ["reservation" => $dataSearchHotel, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "cost"] )->render();
                $html_executive = view("emails.reservations.hotels.reservation.modification", ["reservation" => $dataSearchHotel, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"] )->render();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'subject' => $subject,
                'html' => [
                    'hotel' => $html_hotel,
                    'client' => $html_executive,
                    'executive' => $html_executive,
                ],
                'executive_email' => [$dataSearchHotel['executive_email']],
                'clients_email' => $dataSearchHotel['client_executives'],
                'hotel_contacts' =>  $dataSearchHotel['hotel_contacts']
            ]);

        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function hotel_on_request(Request $request, $id)
    {
        try {
            $response = (new UpdateConfirmationStatusFileItineraryCommand($id, $request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function view_protected_rate(Request $request, $file_id, $id)
    {
        try
        {

            $response = (new FileItineraryViewProtectedRateCommand($id, false))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }


    }

    public function update_itinerary_add_statement(Request $request, $file_id)
    {
        try
        {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            (new FileValidateStatus(
                $file['status']
            ));


            (new UpdateFileItineraryAddStatementCommand($file_id, $request->input('itineraries')))->execute();

            if($request->has('modify_manually') and $request->input('modify_manually') == true)
            {
                $user = $this->getAuthUser($request);
                if(isset($user['id']))
                {
                    $request->merge(["user_id" => $user['id']]);
                    (new UpdateManuallyStatementCommand($file_id, $request->input()))->execute();
                }
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, true);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function latest_itineraries(Request $request)
    {

        try {

            $fileItineraries = (new SearchFileLatestItineriesQuery($request->input()))->handle();
            return FileItineraryLasterResource::collection($fileItineraries);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


}
