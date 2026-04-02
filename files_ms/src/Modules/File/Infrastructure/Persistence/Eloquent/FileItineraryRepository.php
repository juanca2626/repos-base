<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Carbon\Carbon;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryRoomAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryServiceAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileItineraryAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileItineraryDetailMapper;
use Src\Modules\File\Application\Mappers\FileServiceAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileServiceAmountLogMapper;
use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Application\Mappers\FileServiceMapper;
use Src\Modules\File\Application\Mappers\FileServiceUnitMapper;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Model\FilePassenger;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\RoundLito;
use Src\Modules\File\Presentation\Http\Traits\ServiceAutoOrder;

class FileItineraryRepository implements FileItineraryRepositoryInterface
{

    use RoundLito,ServiceAutoOrder;

    public function searchFlights(int $file_id): array
    {
        // $flights = FileItineraryEloquentModel::with('flights.accommodations.filePassenger')->where('file_id', '=', $file_id)->where('object_id', 'flight')->orderBy('date_in')->get();

        // $flights = FileItineraryEloquentModel::with(array('flights' => function($query) {
        //     $query->with('accommodations.filePassenger');
        //     $query->orderBy('departure');
        // }))->where('file_id', '=', $file_id)->where('object_id', 'flight')->orderBy('date_in')->get();


        $flights = FileItineraryEloquentModel::query()->with([
            'flights'=> function ($query) {
                $query->with('accommodations.filePassenger');
                $query->orderBy('departure_time');
            }
        ])->where('file_id', '=', $file_id)->where('object_id', 'flight')->orderBy('date_in')->get()->toArray();

        return $flights;

        // $itineraries = $flights ? array_map(function ($itineraries) {
        //     return FileItineraryMapper::fromArray($itineraries);
        // }, $flights?->toArray() ?? []) : [];

        // return  new FileItineraries($itineraries);

    }

    public function updateFileDateInOut($fileItineraryEloquent){

        $fileEloquent = FileEloquentModel::query()->findOrFail($fileItineraryEloquent->file_id);

        if($fileItineraryEloquent->date_in < $fileEloquent->date_in){
            $fileEloquent->date_in = $fileItineraryEloquent->date_in;
            $fileEloquent->save();
        }

        if($fileItineraryEloquent->date_out > $fileEloquent->date_out){
            $fileEloquent->date_out = $fileItineraryEloquent->date_out;
            $fileEloquent->save();
        }

    }

    public function create(FileItinerary $fileItinerary): array
    {

        return DB::transaction(function () use ($fileItinerary) {

            $fileItineraryEloquent = $this->saveFileItinerary($fileItinerary);
            if($fileItinerary->entity == 'flight'){
               $this->updateFileDateInOut($fileItineraryEloquent);
            }

            if($fileItinerary->entity == 'service-mask'){
                $this->saveDetails($fileItineraryEloquent, (array) $fileItinerary->getFileItineraryDetails());
                $this->saveAccommodations($fileItineraryEloquent, (array) $fileItinerary->getItineraryAccommodations());
            }

            if($fileItinerary->entity == 'service-temporary'){
                foreach($fileItinerary->services as $fileService){
                    $fileService->fileItineraryId->setValue($fileItineraryEloquent->id);
                    $fileServiceEloquent = $this->saveFileService($fileService);
                    $this->saveFileServiceAmountLogDefault($fileServiceEloquent, $fileService->getFileServiceAmountLog());
                    $this->saveFileServiceCompositions($fileServiceEloquent, (array) $fileService->getCompositions());
                }
                $this->saveDetails($fileItineraryEloquent, (array) $fileItinerary->getFileItineraryDetails());
            }

            event(new FilePassToOpeEvent($fileItinerary->fileId->value()));

            return $this->getItineraryByFlight($fileItineraryEloquent->id);
            // return FileItineraryMapper::fromEloquent($fileItineraryEloquent);
        });
    }

    protected function saveFileService(FileService $fileService): FileServiceEloquentModel
    {
        $fileEloquent = FileServiceMapper::toEloquent($fileService);
        $fileEloquent->save();

        return $fileEloquent;
    }

    protected function saveFileServiceAmountLogDefault(FileServiceEloquentModel $fileServiceEloquent, $fileServiceAmountLog): void
    {
        $fileServiceCompositionEloquent = FileServiceAmountLogMapper::toEloquent($fileServiceAmountLog->fileServiceAmount);
        $fileServiceEloquent->fileServiceAmountLogs()->save($fileServiceCompositionEloquent);
    }


    protected function saveFileServiceCompositions(FileServiceEloquentModel $fileServiceEloquent, array $compositions): void
    {
        foreach ($compositions as $composition) {
            // dd($composition->units->units);
            $fileServiceCompositionEloquent = FileServiceCompositionMapper::toEloquent($composition);
            $fileServiceEloquent->compositions()->save($fileServiceCompositionEloquent);
            $this->saveFileServiceUnits($fileServiceCompositionEloquent, $composition->units);
        }
    }

    protected function saveFileServiceUnits(FileServiceCompositionEloquentModel $fileServiceCompositionEloquent, $units): void
    {

        foreach ($units as $unit) {
            $fileServiceUnitEloquent = FileServiceUnitMapper::toEloquent($unit);
            $fileServiceCompositionEloquent->units()->save($fileServiceUnitEloquent);
            $this->saveFileServiceAccommodations($fileServiceUnitEloquent, $unit->accommodations);
        }

    }

    protected function saveFileServiceAccommodations(FileServiceUnitEloquentModel $fileServiceUnitEloquent, $accommodations): void
    {
        foreach ($accommodations as $accommodation) {

            $fileServiceAccomodationEloquent = FileServiceAccommodationMapper::toEloquent($accommodation);
            $fileServiceUnitEloquent->accommodations()->save($fileServiceAccomodationEloquent);
        }

    }


    public function update(FileItinerary $fileItinerary, int $fileItineraryId): array
    {
        return DB::transaction(function () use ($fileItinerary) {
            $fileItineraryEloquent = $this->saveFileItinerary($fileItinerary);
            $this->updateFileDateInOut($fileItineraryEloquent);
            event(new FilePassToOpeEvent($fileItinerary->fileId->value()));
            return $this->getItineraryByFlight($fileItineraryEloquent->id);
            // return FileItineraryMapper::fromEloquent($fileItineraryEloquent);
        });
    }


    public function getItineraryByFlight($id): array
    {
        $flights = FileItineraryEloquentModel::query()->with([
            'flights'=> function ($query) {
                $query->with('accommodations.filePassenger');
                $query->orderBy('departure_time');
            }
        ])->find($id)->toArray();

        return $flights;
    }


    public function updateStatus(int $id, int $status): bool
    {

        $ileItineraryEloquentModel = FileItineraryEloquentModel::query()->findOrFail($id);
        $ileItineraryEloquentModel->status = 0;
        $ileItineraryEloquentModel->save();
        event(new FilePassToOpeEvent($ileItineraryEloquentModel->file_id));
        return true;
    }

    protected function saveFileItinerary(FileItinerary $fileItinerary): FileItineraryEloquentModel
    {
        $fileItineraryEloquent = FileItineraryMapper::toEloquent($fileItinerary);

        $fileItineraryEloquent->save();
        return $fileItineraryEloquent;
    }

    protected function saveDetails(FileItineraryEloquentModel $fileItineraryEloquent, array $detailsData): void
    {
        foreach($detailsData as $detail){
            $fileItineraryDetailMapper = FileItineraryDetailMapper::toEloquent($detail);
            $fileItineraryEloquent->details()->save($fileItineraryDetailMapper);
        }
    }

    protected function saveAccommodations(FileItineraryEloquentModel $fileItineraryEloquent, array $accommodationsData): void
    {
        foreach($accommodationsData as $accommodation){
            $fileItineraryAccommodationMapper = FileItineraryAccommodationMapper::toEloquent($accommodation);
            $fileItineraryEloquent->accommodations()->save($fileItineraryAccommodationMapper);
        }
    }

    public function updateSchedule(int $id, array $params): array
    {
        $duration_minutes = null;
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file',
            'rooms',
            'services.compositions'
        ])->find($id);
        $start_time_current = $fileItineraryEloquent->start_time;
        $fileItinerarieStarTime = $params['start_time']->value();
        $departure_time = $params['departure_time']->value();

        if($fileItineraryEloquent->entity == 'hotel')
        {
            $fileItineraryEloquent->start_time = $fileItinerarieStarTime;
            if($departure_time != null){
               $fileItineraryEloquent->departure_time = $departure_time;
            }

            $stela = [];
            $auto_order = 0;
            foreach($fileItineraryEloquent->rooms as $room)
            {
                if($room->status == 1)
                {
                    $auto_order++;
                    array_push($stela, [
                        'code' => $fileItineraryEloquent->object_code,
                        'auto_order' => 1,
                        'type_ifx' => 'direct',
                        'date_in' => Carbon::parse($fileItineraryEloquent->date_in)->format('d/m/Y'),
                        'start_time_current' => substr($start_time_current, 0, 5),
                        'start_time' => substr($fileItineraryEloquent->start_time, 0, 5),
                        'departure_time' => substr($fileItineraryEloquent->departure_time, 0, 5),
                        'components' => [],
                    ]);
                }
            }

        }else{

            $fileItinerarie_start_time = strtotime($fileItinerarieStarTime);
            if($fileItinerarieStarTime !== null and $departure_time !== null){
                $duration_minutes = Carbon::parse($fileItinerarieStarTime)->diffInMinutes(Carbon::parse($departure_time));
            }

            $stela = [];
            foreach($fileItineraryEloquent->services as $service)
            {

                $auto_order = $this->getServiceAutoOrder($fileItineraryEloquent->file_id, $service->id, $service->code, $service->date_in);

                $stelaService = [
                    'code' => $service->code,
                    "service_name" => NULL,
                    'auto_order' => $auto_order,
                    'type_ifx' => $service->type_ifx,
                    'date_in' => Carbon::parse($service->date_in)->format('d/m/Y'),
                    'start_time_current' => substr($service->start_time, 0, 5),
                    'start_time' => substr($service->start_time, 0, 5),
                    'departure_time' => substr($service->departure_time, 0, 5),
                    'components' => [],
                ];

                if(!$service->frecuency_code){ // si tiene frecuencias no modificamos

                    foreach($service->compositions as $composition)
                    {
                        $start_time_current = $composition->start_time;
                        if($duration_minutes !== null and $composition->duration_minutes < 1 ){
                            $departureTimeComposition = date("H:i", strtotime("+{$duration_minutes} minutes", $fileItinerarie_start_time));
                        }else{
                            $departureTimeComposition = date("H:i", strtotime("+{$composition->duration_minutes} minutes", $fileItinerarie_start_time));
                        }

                        $composition->start_time = $fileItinerarieStarTime;
                        $composition->departure_time = $departureTimeComposition;
                        $composition->save();

                        if($service->type_ifx == 'package'){
                            array_push($stelaService['components'], [
                                'code' => $composition['code'],
                                'auto_order' => 1,
                                'type_ifx' => $service['type_ifx'],
                                'date_in' => Carbon::parse($composition->date_in)->format('d/m/Y'),
                                'start_time_current' => substr($start_time_current, 0, 5) , // dato antiguo para haga merge
                                'start_time' => substr($composition->start_time, 0, 5) ,
                                'departure_time' => $composition->departure_time
                            ]);
                        }
                    }

                    $service->start_time = $fileItinerarieStarTime;
                    $service->departure_time = $service->compositions->max('departure_time');
                    $service->save();

                    $stelaService['start_time'] = $fileItinerarieStarTime;
                    $stelaService['departure_time'] =  $service->departure_time;

                }else{

                    if($service->type_ifx == 'package'){
                        foreach($service->compositions as $composition)
                        {
                            array_push($stelaService['components'], [
                                'code' => $composition->code,
                                'auto_order' => 1,
                                'type_ifx' => $service->type_ifx,
                                'date_in' => Carbon::parse($composition->date_in)->format('d/m/Y'),
                                'start_time_current' => substr($composition->start_time, 0, 5),
                                'start_time' =>  substr($composition->start_time, 0, 5) ,
                                'departure_time' => substr($composition->departure_time, 0, 5)
                            ]);
                        }
                    }
                }

                array_push($stela, $stelaService);
            }

            $fileItineraryEloquent->start_time = $fileItinerarieStarTime;
            $fileItineraryEloquent->departure_time = $fileItineraryEloquent->services->max('departure_time');
        }

        $fileItineraryEloquent->save();

        event(new FilePassToOpeEvent($fileItineraryEloquent->file_id));

        return [
            'file_number' => $fileItineraryEloquent->file->file_number,
            'stela' => $stela
        ];
    }



    public function updateStatusRate(int $id, array $params): bool
    {
        return false;
    }

    public function updateStatusRateNeg(int $id, array $params): bool
    {
        return false;
    }

    public function searchMasterServicesByItinerary(array $params) : array
    {
        $master_services = FileServiceEloquentModel::query(); $type = 'service';

        if(isset($params['file_hotel_room_id']) && !empty($params['file_hotel_room_id']))
        {
            $type = 'room';

            $master_services = FileHotelRoomEloquentModel::query()
                ->with(['units.accommodations', 'itinerary']);

            if(isset($params['accommodations']) && $params['accommodations'])
            {
                $master_services = $master_services->with(['units.accommodations']);
            }

            $master_services = $master_services->where('id', '=', $params['file_hotel_room_id']);
        }
        elseif(isset($params['file_hotel_room_unit_id']) && !empty($params['file_hotel_room_unit_id']))
        {
            $type = 'room';

            $master_services = FileHotelRoomEloquentModel::query()
                ->with(['units.accommodations', 'itinerary']);

            if(isset($params['accommodations']) && $params['accommodations'])
            {
                $master_services = $master_services->with(['units.accommodations']);
            }

            $master_services = $master_services->whereHas('units', function ($query) use ($params) {
                $query->where('id', '=', $params['file_hotel_room_unit_id']);
            });
        }
        else
        {
            if(isset($params['accommodations']) && $params['accommodations'])
            {
                // $master_services = $master_services->with(['compositions.units.accommodations']);
                $master_services = $master_services->with(['compositions.units']);
            }
        }

        if(isset($params['file_id']) && !empty($params['file_id']))
        {
            $master_services = $master_services->with(['compositions'])
                ->with(['itinerary' => function ($query) use ($params) {
                    $query->where('file_id', '=', $params['file_id']);
                }])
                ->whereHas('itinerary', function ($query) use ($params) {
                    $query->where('file_id', '=', $params['file_id']);
                });
        }

        if(isset($params['file_itinerary_id']) && !empty($params['file_itinerary_id']))
        {
            $master_services = $master_services->with(['compositions'])
                ->where('file_itinerary_id', '=', $params['file_itinerary_id']);
        }

        if(isset($params['file_service_id']) && !empty($params['file_service_id']))
        {
            $master_services = $master_services->with(['compositions'])
                ->where('id', '=', $params['file_service_id']);
        }

        if(isset($params['file_composition_id']) && !empty($params['file_composition_id']))
        {
            $master_services = $master_services
                ->with(['compositions' => function ($query) use ($params) {
                    $query->where('id', '=', $params['file_composition_id']);
                }])
                ->whereHas('compositions', function ($query) use ($params) {
                    $query->where('id', '=', $params['file_composition_id']);
                });
        }

        $master_services = $master_services->get();

        if(isset($params['order']) && $params['order'])
        {
            if($type == 'service')
            {
                $master_services->filter(function ($master_service) {
                    $_master_services = FileServiceEloquentModel::query()
                        ->where('file_itinerary_id', '=', $master_service['file_itinerary_id'])
                        ->where('date_in', '=', $master_service['date_in'])
                        ->where('code', '=', $master_service['code'])
                        // ->where('start_time', '=', $master_service['start_time'])
                        ->orderBy('id')->pluck('id')->toArray();
                    $master_service->auto_order = (
                        array_search($master_service->id, $_master_services) + 1
                    );

                    $master_service->compositions->filter(function ($composition) {

                        $_compositions = FileServiceCompositionEloquentModel::query()
                            ->where('file_service_id', '=', $composition['file_service_id'])
                            ->where('date_in', '=', $composition['date_in'])
                            ->where('code', '=', $composition['code'])
                            // ->where('start_time', '=', $composition['start_time'])
                            ->orderBy('id')->pluck('id')->toArray();
                        $composition->auto_order = (array_search($composition->id, $_compositions) + 1);

                        return $composition;
                    });

                    return $master_service;
                });
            }

            if($type == 'room')
            {
                $master_services->filter(function ($master_hotel_room) {

                    $_itineraries = FileItineraryEloquentModel::query()
                        ->where('entity', '=', $master_hotel_room['itinerary']['entity'])
                        ->where('object_code', '=', $master_hotel_room['itinerary']['object_code'])
                        ->where('date_in', '=', $master_hotel_room['itinerary']['date_in'])
                        ->pluck('id')->toArray();
                    $master_hotel_room->auto_order = (
                        array_search($master_hotel_room->itinerary->id, $_itineraries) + 1
                    );

                    return $master_hotel_room;
                });
            }

        }

        return $master_services->toArray();
    }

    public function findById(int $id, string $type=""): FileItinerary|array
    {

        $fileItineraryEloquent = $this->queryFile();

        $fileItineraryEloquent = $fileItineraryEloquent->findOrFail($id);

        return FileItineraryMapper::fromArray($fileItineraryEloquent->toArray());

    }

    public function findByIdArray(int $id, string $type=""): array
    {

        $fileItineraryEloquent = $this->queryFileArray();

        $fileItineraryEloquent = $fileItineraryEloquent->findOrFail($id);

        return $fileItineraryEloquent->toArray();

    }


    public function queryFile()
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            // 'file.passengers',
            // 'rooms.fileRoomAmount.fileAmountReason',
            // 'rooms.fileRoomAmount.fileAmountTypeFlag',
            // 'rooms.fileRoomAmountLogs'=> function ($query) {
            //     $query->with('fileAmountReason');
            //     $query->with('fileAmountTypeFlag');
            //     $query->whereHas('fileAmountReason', function ($query){
            //         $query->where('area', 'COMERCIAL');
            //         $query->where('visible', 1);
            //     });
            // },
            'rooms.units.accommodations.filePassenger',
            'rooms.units.nights',
            'rooms.units.fileHotelRoom',
            // 'services.fileServiceAmount.fileAmountReason',
            // 'services.fileServiceAmount.fileAmountTypeFlag',
            // 'services.fileServiceAmountLogs'=> function ($query) {
            //     $query->with('fileAmountReason');
            //     $query->with('fileAmountTypeFlag');
            //     $query->whereHas('fileAmountReason', function ($query){
            //         $query->where('area', 'COMERCIAL');
            //         $query->where('visible', 1);
            //     });
            // },
            'services.compositions'=> function ($query) {
                $query->with('fileService.fileItinerary');
                $query->with('units');
                $query->with('supplier');
            },
            'flights.accommodations.filePassenger',
            'descriptions',
            'details',
            'accommodations.filePassenger',
            // 'service_amount_logs.fileServiceAmountLog',
            // 'room_amount_logs.fileRoomAmountLog'
        ]);

        return $fileItineraryEloquent;
    }
    public function queryFileArray()
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()
            ->select('id', 'file_id' ,'entity', 'name', 'category', 'object_id', 'object_code', 'country_in_name', 'date_in', 'date_out', 'start_time', 'departure_time', 'total_adults', 'total_children','total_amount', 'total_cost_amount', 'status', 'confirmation_status', 'profitability', 'is_in_ope', 'sent_to_ope', 'protected_rate', 'view_protected_rate', 'add_to_statement', 'city_in_iso','city_in_name', 'city_out_iso','city_out_name', 'country_in_iso','country_in_name', 'country_out_iso','country_out_name', 'zone_in_airport', 'zone_out_airport', 'hotel_origin', 'hotel_destination', 'service_category_id', 'service_sub_category_id', 'service_type_id', 'service_summary','service_itinerary','service_supplier_code','service_supplier_name','created_at','total_infants','zone_in_id', 'zone_out_id')
            ->with([
                'rooms:id,file_itinerary_id,room_id,room_name,room_type,rate_plan_id,rate_plan_name,rate_plan_code,channel_id,total_rooms,status,confirmation_status,total_adults,total_children,amount_sale,amount_cost,markup_created,protected_rate,channel_reservation_code_master',
                'rooms.fileRoomAmount:id,file_amount_type_flag_id,file_amount_reason_id,file_hotel_room_id,user_id,amount_previous,amount',
                // 'rooms.fileRoomAmount.fileAmountReason:id,name,influences_sale,area,visible',
                'rooms.fileRoomAmount.fileAmountTypeFlag:id,name,description,icon',
                'rooms.units:id,file_hotel_room_id,confirmation_code,amount_sale,amount_cost,taxed_unit_sale,taxed_unit_cost,adult_num,child_num,status,confirmation_status,policies_cancellation,reservations_rates_plans_rooms_id',
                'rooms.units.accommodations:id,file_hotel_room_unit_id,file_passenger_id,room_key',
                'rooms.units.accommodations.filePassenger:id,name,surnames,type',
                'rooms.units.nights:id,file_hotel_room_unit_id,date,number,price_adult_sale,price_adult_cost,price_child_sale,price_child_cost,price_infant_sale,price_infant_cost,price_extra_sale,price_extra_cost,total_amount_sale,total_amount_cost',
                'rooms.units.fileHotelRoom:id,markup_created',

                'services:id,master_service_id,file_itinerary_id,name,code,type_ifx,date_in,date_out,start_time,departure_time,amount_cost,status,confirmation_status,is_in_ope,frecuency_code,sent_to_ope',
                'services.fileServiceAmount:id,file_amount_type_flag_id,file_amount_reason_id,file_service_id,user_id,amount_previous,amount',
                'services.fileServiceAmount.fileAmountTypeFlag:id,name,description,icon',
                'services.compositions' => function ($query) {
                    $query->select('id','file_service_id','code','name','is_programmable','is_in_ope','sent_to_ope','start_time','departure_time','date_in','date_out','currency','amount_sale','amount_cost','amount_sale_origin','amount_cost_origin','markup_created','use_voucher','voucher_sent','voucher_number','duration_minutes','status') //,'total_adults','total_children','total_infants','total_extra','taxes','total_services','use_itinerary','use_ticket','use_accounting_document','ticket_sent','document_skeleton','document_purchase_order'
                        ->with([
                            'fileService:id',
                            'fileService.fileItinerary:id',
                            'units',
                            'supplier:id,file_service_composition_id,reservation_for_send,assigned,for_assign,code_request_book,code_request_invoice,code_request_voucher,policies_cancellation_service,send_communication'
                        ]);
                },

                'flights:id,file_itinerary_id,airline_name,airline_code,airline_number,departure_time,arrival_time,pnr,nro_pax',
                'flights.accommodations:id,file_itinerary_flight_id,file_passenger_id',
                'flights.accommodations.filePassenger:id,name,surnames,type',

                'descriptions:id,file_itinerary_id,language_id,code,description',
                'details:id,file_itinerary_id,language_id,itinerary,skeleton',
                'accommodations:id,file_itinerary_id,file_passenger_id',
                'accommodations.filePassenger:id,name,surnames,type'
        ]);

        return $fileItineraryEloquent;
    }

    public function searchFileLatestItineriesQuery(array $params): array
    {
        $fileItineraryEloquent = $this->queryFile($params);


        $fileItineraryEloquent = FileItineraryEloquentModel::
        select('id', 'file_id', 'status', 'entity', 'total_amount')->where(function ($query){
            $query->where('total_amount', '>', 0)->orWhere('status', '=', 1);
        })->whereHas('file', function ($query) use ($params) {
            $query->where('file_number', '=', $params['file_number']);
        })->where('id','>', $params['file_itinerary_id'])->orderBy('date_in')->orderBy('start_time');

        return $fileItineraryEloquent->get()->toArray();
    }


    public function serachFileItineraryByCancellation(array $file): array
    {
        if(count($file)>0){

            $data = [
                'executive_code' => $file['executive_code'],
                'hotel_id' => $file['itineraries']['object_id'],
                'client_id' => $file['client_id']
            ];
            $aurora = new AuroraExternalApiService();
            $response = $aurora->searchByCommunication($data);

            $file['executive_name'] = $response->executive->name;
            $file['executive_email'] = $response->executive->email;
            $file['client_name'] = $response->client->name;
            $file['client_nationality'] = $response->client->pais;
            $file['client_executives'] = $response->client_executives;
            $file['hotel_contacts'] = $response->hotel_contacts;
        }

        return $file;
    }


    public function cancelItineraryRoomUnits(int $fileItinearyId): array
    {

        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'rooms.units.nights'
        ])->find($fileItinearyId);

        if($fileItineraryEloquent->entity == "hotel"){

            $results = [];
            foreach($fileItineraryEloquent->rooms as $rooms){
                $units = [];
                foreach($rooms->units as $unit){

                    if($unit->status == "1"){
                        array_push($units, $unit->id);
                    }
                }
                if(count($units)>0){
                    array_push($results, [
                        'id' => $rooms->id,
                        'units' => $units
                    ]);
                }
            }

            return $results;
        }

        return [];

    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateTotalCostAmount(int $id): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->findOrFail($id);
        $fileItineraryEloquent->total_cost_amount = $fileItineraryEloquent->calculateTotalCostAmount();
        $fileItineraryEloquent->save();
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateTotalAmount(int $id): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file','services'
        ])->findOrFail($id);

        if(in_array($fileItineraryEloquent->entity, ['service','service-temporary'])){

            // solo actualizaremos el precio venta si todos los componentes estan cancelados
            $cancelAll = true;
            foreach($fileItineraryEloquent->services as $service){
                if($service->status == true){
                    $cancelAll = false;
                }
            }
            if($cancelAll == true){
               $totalServiceCost = $fileItineraryEloquent->calculateTotalAmount();
               $markup = $fileItineraryEloquent->markup_created;
               $person = $fileItineraryEloquent->total_adults +  $fileItineraryEloquent->total_children + $fileItineraryEloquent->total_infants;
               $totalServiceCost = $this->roundLito($totalServiceCost *  (1 + ($markup/100)));
               $fileItineraryEloquent->total_amount = $totalServiceCost * $person;
            }

        }else{
            $fileItineraryEloquent->total_amount = $fileItineraryEloquent->calculateTotalAmount();
        }


        $fileItineraryEloquent->save();
        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function updateProfitability(int $id): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->findOrFail($id);
        $fileItineraryEloquent->profitability = $fileItineraryEloquent->calculateProfitability();
        $fileItineraryEloquent->save();
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function update_number_of_passengers(int $id, array $params): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with(['file'])->find($id);

        if($params['total_adults'] > $fileItineraryEloquent->file->adults or $params['total_adults'] < 1){
            throw new \DomainException("The number of adults must be between 1 and ".$fileItineraryEloquent->file->adults);
        }

        if($params['total_children'] > $fileItineraryEloquent->file->children or $params['total_children'] < 0){
            throw new \DomainException("The number of adults must be between 1 and ".$fileItineraryEloquent->file->children);
        }

        $fileItineraryEloquent = FileItineraryEloquentModel::query()->findOrFail($id);
        $fileItineraryEloquent->total_adults = $params['total_adults'];
        $fileItineraryEloquent->total_children = $params['total_children'];
        $fileItineraryEloquent->save();

        // desasociamos los pax a la nueva cantidad asignada de pax

        DB::transaction(function () use ($id, $fileItineraryEloquent) {
            // 1. Obtener el itinerario
            $itinerary = FileItineraryEloquentModel::findOrFail($id);

            $totalAdults = $itinerary->total_adults;
            $totalChildren = $itinerary->total_children;

            // 2. Eliminar todas las asociaciones existentes
            FileItineraryAccommodationEloquentModel::where('file_itinerary_id', $id)->delete();

            // 3. Obtener adultos en orden de creación
            $adultos = FilePassengerEloquentModel::where('type', 'ADL')
                ->where('file_id', $fileItineraryEloquent->file->id)
                ->orderBy('created_at')
                ->limit($totalAdults)
                ->get();

            // 4. Obtener niños en orden de creación
            $ninos = FilePassengerEloquentModel::where('type', 'CHD')
                ->where('file_id', $fileItineraryEloquent->file->id)
                ->orderBy('created_at')
                ->limit($totalChildren)
                ->get();

            // 5. Insertar nuevas asociaciones
            $asociaciones = $adultos->merge($ninos)->map(function ($pasajero) use ($id) {
                return [
                    'file_itinerary_id' => $id,
                    'file_passenger_id' => $pasajero->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            FileItineraryAccommodationEloquentModel::insert($asociaciones->toArray());
        });




        return true;
    }



    public function cancel(int $id): bool
    {

        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'services.compositions.units',
            'rooms.units.nights'
        ])->find($id);

        if($fileItineraryEloquent->entity == "hotel"){

            $status = false;
            foreach($fileItineraryEloquent->rooms as $room){
                if($room->status == "1"){
                    $status = true;
                }

                foreach($room->units as $unit){
                    if($unit->status == "1"){
                        $status = true;
                    }
                }
            }

            if($status == false){
                $fileItineraryEloquent->status = 0;
                $fileItineraryEloquent->save();
            }

        }

        if($fileItineraryEloquent->entity == "service"){

            $status = false;
            foreach($fileItineraryEloquent->services as $service){

                if($service->status == "1"){
                    $status = true;
                }
                foreach($service->compositions as $composition){

                    if($composition->status == "1"){
                        $status = true;
                    }

                    foreach($composition->units as $unit){

                        if($unit->status == "1"){
                            $status = true;
                        }

                    }

                }
            }

            if($status == false){
                $fileItineraryEloquent->status = 0;
                $fileItineraryEloquent->save();
            }

        }

        if($fileItineraryEloquent->entity == "service-mask"){

            $fileItineraryEloquent->status = 0;
            $fileItineraryEloquent->save();

        }
        return true;
    }


    public function updateAmountSale(int $id, array $params): bool
    {

        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file'
        ])->find($id);

        $amountReason = FileAmountReasonEloquentModel::find($params['file_amount_reason_id']);

        $data = [
            'file_itinerary_id' => $fileItineraryEloquent->id,
            'value' => $fileItineraryEloquent->total_amount,
            'markup' => $fileItineraryEloquent->profitability,
            'file_amount_reason_id' => $amountReason->id
        ];

        if($fileItineraryEloquent->entity == 'hotel')
        {
            $data['file_room_amount_log_id'] = NULL;
            FileItineraryRoomAmountLogEloquentModel::create($data);
        }

        if($fileItineraryEloquent->entity == 'service')
        {
            $data['file_service_amount_log_id'] = NULL;
            FileItineraryServiceAmountLogEloquentModel::create($data);
        }

        // actualizamos el nuevo monto
        $fileItineraryEloquent->total_amount = $params['amount_sale'];
        $fileItineraryEloquent->save();

        return true;
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function serch_accomodation(int $id): array
    {
        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries.rooms.units.accommodations',
        ])->findOrFail($id);

        $file = $fileEloquent->toArray();
        $accommodation = [];
        foreach($fileEloquent->itineraries as $itinerary){

            if($itinerary->entity =='hotel'){

                foreach($itinerary->rooms as $room){

                    foreach($room->units as $unit){

                        $rooms = [
                            'unit_id' => $unit->id,
                            'type' => $room->occupation,
                            'passengers' => collect($unit->accommodations)->map(function ($accommodation) {
                                return $accommodation->file_passenger_id;
                            })
                        ];

                        array_push($accommodation, $rooms);
                    }
                }
                break;
            }
        }

        return $accommodation;
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function serch_accomodation_all_hotels(int $id): array
    {
        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries.rooms.units.accommodations',
        ])->findOrFail($id);

        $file = $fileEloquent->toArray();
        $itinirary_hotel_accommodation = [];
        foreach($fileEloquent->itineraries as $itinerary){

            if($itinerary->entity =='hotel'){
                $accommodation = [];
                foreach($itinerary->rooms as $room){

                    foreach($room->units as $unit){

                        $rooms = [
                            'unit_id' => $unit->id,
                            'type' => $room->occupation,
                            'passengers' => collect($unit->accommodations)->map(function ($accommodation) {
                                return $accommodation->file_passenger_id;
                            })
                        ];

                        array_push($accommodation, $rooms);
                    }
                }
                $itinirary_hotel_accommodation[$itinerary->id] = $accommodation;

            }
        }

        return $itinirary_hotel_accommodation;
    }

    public function delete_flight(int $id): array
    {
       $itinerary = $this->getItineraryByFlight($id);

       $fileItinery = FileItineraryEloquentModel::with([
        'flights',
       ])->find($id);

       if($fileItinery){
          $fileItinery->flights()->delete();
          $fileItinery->delete();
       }

       return $itinerary;
    }

    public function updateAccommodationServices(int $id, array $passengers): bool
    {
        FileItineraryAccommodationEloquentModel::where('file_itinerary_id', $id)->delete();

        foreach($passengers as $passenger){
            FileItineraryAccommodationEloquentModel::create([
                'file_itinerary_id' => $id,
                'file_passenger_id' => $passenger
            ]);
        }

        $fileItinerary = FileItineraryEloquentModel::find($id);

        event(new FilePassToOpeEvent($fileItinerary->file_id));

        return true;
    }



    public function updateAccommodation(int $id, array $accomodation_news): bool
    {
        $accomodation_all_hotels = $this->serch_accomodation_all_hotels($id);
        $accomodation_filter_hotel_process = $this->compareAllAccommodation($accomodation_all_hotels, $accomodation_news);

        DB::transaction(function () use ($accomodation_filter_hotel_process) {

            foreach($accomodation_filter_hotel_process as $accomodation_hotel_process){

                foreach($accomodation_hotel_process as $unit_process){
                FileRoomAccommodationEloquentModel::where('file_hotel_room_unit_id', $unit_process['unit_id'])->delete();

                foreach($unit_process['passenger_new'] as $passenger){
                        FileRoomAccommodationEloquentModel::create([
                            'file_hotel_room_unit_id' => $unit_process['unit_id'],
                            'file_passenger_id' => $passenger,
                            'room_key' => 0
                        ]);
                    }

                }
            }
        });

        return true;
    }

    public function compareAllAccommodation($accomodation_all_hotels, $accomodation_news)
    {
        // Sacamos un listado de hoteles que procesaremos y que tengan la misma estructura de la nueva acomodacion
        $byProcess = [];
        foreach($accomodation_all_hotels as $itinerary_id => $accomodation_hotels){

            //validamos que todos los hoteles tengan la misma estructura de la nueva acomodacion las que no lo tienen no la procesamos
            $validated = [];
            foreach($accomodation_news as $accomodation_new){

                $tipe_room = $accomodation_new['type'];
                foreach($accomodation_hotels as $index => $accomodation){

                    if(($tipe_room == $accomodation['type']) and  !in_array($index, $validated)){
                        if(count($accomodation_new['passengers']) == count($accomodation['passengers'])){
                            $accomodation_hotels[$index]['passenger_new'] = $accomodation_new['passengers'];
                            array_push($validated, $index);
                            break;
                        }
                    }

                }
            }

            if(count($validated) === count($accomodation_hotels)){
                $byProcess[$itinerary_id] = $accomodation_hotels;
            }

        }

        return $byProcess;

    }

    public function updatePax($id): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with(['flights.accommodations.filePassenger'])->find($id);

        if($fileItineraryEloquent->entity == 'flight'){

            $totalAdult = 0;
            $totalChild = 0;
            foreach($fileItineraryEloquent->flights as $flight){

                foreach($flight->accommodations as $accommodation){
                    if($accommodation->filePassenger->type  == 'ADL'){
                    $totalAdult = $totalAdult + 1;
                    }

                    if($accommodation->filePassenger->type  == 'CHD'){
                        $totalChild = $totalChild + 1;
                    }
                }

            }

            $fileItineraryEloquent->total_adults = $totalAdult;
            $fileItineraryEloquent->total_children = $totalChild;
            $fileItineraryEloquent->save();
        }

        return true;
    }

    public function updateConfirmationStatus($id, $params): bool
    {
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'rooms.units'
        ])->find($id);

        if($fileItineraryEloquent->entity == "hotel"){

            foreach($fileItineraryEloquent->rooms as $room){

                foreach($room->units as $unit){
                    $unit->confirmation_status = true;
                    $unit->save();
                }

                $room->confirmation_status = true;
                $room->save();
            }

            $fileItineraryEloquent->confirmation_status = true;
            $fileItineraryEloquent->save();

            event(new FilePassToOpeEvent($fileItineraryEloquent->file_id));
        }

        return true;
    }

    public function associateTemporaryService($id, $params): bool
    {
        $fileTemporaryServiceEloquentModel = FileTemporaryServiceEloquentModel::find($params['file_temporary_service_id']);

        if(!$fileTemporaryServiceEloquentModel){
            throw new \DomainException("temporary service does not exist");
        }

        $fileItineraryEloquent = FileItineraryEloquentModel::query()->find($id);
        $fileItineraryEloquent->entity = 'service-temporary';
        $fileItineraryEloquent->object_id = $params['file_temporary_service_id'];
        $fileItineraryEloquent->name = $fileTemporaryServiceEloquentModel->name;
        $fileItineraryEloquent->save();

        return true;
    }

    public function viewProtectedRate(int $id, bool $status = false): bool
    {
        $fileEloquent = FileItineraryEloquentModel::query()->find($id);

        $fileEloquent->view_protected_rate = $status;
        $fileEloquent->save();

        return true;
    }

    public function update_add_statement(int $file_id, array $itineraries): bool
    {

        if(count($itineraries) == 0){
            return true;
        }

        FileItineraryEloquentModel::whereIn('id', $itineraries)->where('file_id', $file_id)->update(['add_to_statement' => 1]);

        return true;
    }


}
