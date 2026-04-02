<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileCategoryMapper;
use Src\Modules\File\Application\Mappers\FileHotelRoomMapper;
use Src\Modules\File\Application\Mappers\FileHotelRoomUnitMapper;
use Src\Modules\File\Application\Mappers\FileHotelRoomUnitNightMapper;
use Src\Modules\File\Application\Mappers\FileItineraryAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Application\Mappers\FileMapper;
use Src\Modules\File\Application\Mappers\FilePassengerMapper;
use Src\Modules\File\Application\Mappers\FileRoomAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileRoomAmountLogMapper;
use Src\Modules\File\Domain\Events\CreatedFileEvent;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomUnitNightEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeLogsModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatusReasonEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\CalculateProfitability;
use Src\Modules\File\Presentation\Http\Traits\CreateFileStela;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileCreateFileServicesStelaJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileCreateFileStelaJob;
use Src\Modules\File\Presentation\Http\Traits\SqsNotification;
use Src\Modules\File\Presentation\Http\Traits\SuggestedAccommodation;
use Illuminate\Support\Facades\Auth;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\StatusReasonEloquentModel;

class FileRepository implements FileRepositoryInterface
{
    use CalculateProfitability, CreateFileStela, SqsNotification, SuggestedAccommodation;
    protected string $dateIn;
    protected string $dateOut;
    protected string $file_exist;

    public function create(File $file): bool
    {   
        return DB::transaction(function () use ($file) {

            $this->file_exist = false;
            if($file->fileNumber->value()) {
                $this->file_exist = $this->file_exist($file->fileNumber->value());
            }

            $fileEloquent = $this->saveFile($file);
            // Si existe un id de file quiere decir que ya se generó previamente e ignoramos el guardado de paxs..
            if($this->file_exist == false)
            {
                $this->savePassengers($fileEloquent, (array) $file->getPassengers());
                $this->createInitialFileStatusReasons($fileEloquent->id);
                // $this->createStatement($fileEloquent->id);
            }else{
                // $this->updateStatement($fileEloquent->id);
            }

            $this->saveCategories($fileEloquent, (array) $file->getCategories());

            $itinerariesData =  $this->saveItineraries($fileEloquent, (array) $file->getItineraries()); 

            $this->updateTypeRoomPassenger($fileEloquent->id);
            
            $this->update_suggested_accommodation($fileEloquent->id);
            $this->update_date_files($fileEloquent->id);
 
            // event(new CreatedFileEvent($fileEloquent->id, $file , $itinerariesData));
            return true;
            // return FileMapper::fromEloquent($fileEloquent, false, false, false);
        });
    }

    public function update_suggested_accommodation(string $file_id): bool
    {
        $fileEloquentModel = FileEloquentModel::with('itineraries.rooms')
        ->whereHas('itineraries', function ($query){
            $query->where('entity', 'hotel');
        })->where('id', $file_id)->first();

        if($fileEloquentModel)
        {
            $accommodation = $this->suggested_accommodation($fileEloquentModel);
            $fileEloquentModel->suggested_accommodation_sgl = $accommodation['sgl'];
            $fileEloquentModel->suggested_accommodation_dbl = $accommodation['dbl'];
            $fileEloquentModel->suggested_accommodation_tpl = $accommodation['tpl'];
            $fileEloquentModel->save();
        }

        return true;
    }

    public function update_date_files(string $file_id): bool
    {
        $fileEloquentModel = FileEloquentModel::with('itineraries')->find($file_id);

        if($fileEloquentModel)
        {
            $fileEloquentModel->date_in = $fileEloquentModel->itineraries->min('date_in');
            $fileEloquentModel->date_out = $fileEloquentModel->itineraries->max('date_out');
            $fileEloquentModel->save();
        }

        return true;
    }


    public function file_exist(string $file_number): bool
    {
        $fileEloquent = FileEloquentModel::where('file_number', '=', $file_number)->get();
        if(count($fileEloquent)>0){
            return true;
        }
        return false;
    }

    public function searchFileCreateExistQuery(File $file): File|null
    {
        $fileEloquent = FileEloquentModel::with('categories')->whereDoesntHave('itineraries');
        $fileEloquents = $fileEloquent->where('client_id', $file->clientId->value())
                        ->where('description', $file->description->value())
                        ->where('date_in', $file->dateIn->value())
                        ->where('adults', $file->adults->value())
                        ->where('children', $file->children->value())
                        ->where('suggested_accommodation_sgl', $file->suggestedAccommodationSgl->value())
                        ->where('suggested_accommodation_dbl', $file->suggestedAccommodationDbl->value())
                        ->where('suggested_accommodation_tpl', $file->suggestedAccommodationTpl->value())
                        ->where('lang', $file->lang->value())->get();

        $selectedFile = null;

        foreach($fileEloquents as $fileEloquent)
        {
            $diference = true;
            if($fileEloquent->categories->count() !== count($file->getCategories())){
                $diference = false;
            }

            $search = 0;
            foreach($file->getCategories() as $categories){
                foreach($fileEloquent->categories as $fileCategory){
                    if($categories->categoryId->value() == $fileCategory->category_id){
                        $search++;
                    }
                }
            }

            if(count($file->getCategories()) !== $search){
                $diference = false;
            }

            if($diference == true){
                $selectedFile = $fileEloquent;
                break;
            }

        }

        if($selectedFile === null){
            return null;
        }

        return FileMapper::fromEloquent($selectedFile, false, false, false);
    }


    protected function saveCategories(FileEloquentModel $fileEloquent, array $categiriesData): void
    {
        foreach ($categiriesData as $category) {
            // $category->fileId->setValue($fileEloquent->id);
            $categoryEloquent = FileCategoryMapper::toEloquent($category);
            $fileEloquent->categories()->save($categoryEloquent);

        }
    }
    protected function saveFile(File $file): FileEloquentModel
    {
        $fileEloquent = FileMapper::toEloquent($file);
        $fileEloquent->save();

        return $fileEloquent;
    }

    protected function ignore_itineraries($itineraries, $reservation)
    {
        $ignore = false;

        if(!empty($itineraries))
        {
            foreach($itineraries as $itinerary)
            {
                if($itinerary->aurora_reservation_id == $reservation->auroraReservationId->value())
                {
                    $ignore = true;
                    break;
                }

                if(
                    $itinerary->entity == $reservation->entity->value() and
                    $itinerary->object_code == $reservation->serviceCode->value() and
                    $itinerary->date_in == $reservation->dateIn->value() and
                    $itinerary->date_out == $reservation->dateOut->value()
                )
                {
                    $ignore = true;
                    break;
                }
            }
        }

        return $ignore;
    }

    protected function saveItineraries(FileEloquentModel $fileEloquent, array $itinerariesData): array
    {
        $file_itinerary_ids = [];

        $datesIn = collect();
        $datesOut = collect();
        $totalAmount = 0;

        // -----------------------  Validación de agregar al file ---------------------------
        $itineraries = FileItineraryEloquentModel::query()
            ->where('file_id', '=', $fileEloquent->id)->get();
        // ------------------------------------------------------------------------------------

        foreach ($itinerariesData as $index => $itineraryData) {
            $datesIn->add(Carbon::parse($itineraryData->dateIn->value())->format('Y-m-d'));
            $datesOut->add(Carbon::parse($itineraryData->dateOut->value())->format('Y-m-d'));
            $totalAmount = $totalAmount + $itineraryData->totalAmount->value();

            // if(!in_array(@$itineraryData->auroraReservationId->value(), $ignore))
            // {
            // if(!$this->ignore_itineraries($itineraries, $itineraryData))
            // {

                if ($itineraryData->entity->value() == 'flight')
                {

                }

                $itineraryEloquent = FileItineraryMapper::toEloquent($itineraryData);
                $fileEloquent->itineraries()->save($itineraryEloquent);

                if ($itineraryData->entity->value() == 'service') {                    
                    $itinerariesData[$index]->id = $itineraryEloquent->id;                
                    array_push($file_itinerary_ids, $itineraryEloquent->id);
                    $this->saveAccommodations($itineraryEloquent, $itineraryData->accommodations);
                }

                if ($itineraryData->entity->value() == 'hotel') {
                    $this->saveHotelRooms($itineraryEloquent, $itineraryData->rooms);
                    if($itineraryEloquent->files_ms_parameters){
                        $files_ms_parameters = json_decode($itineraryEloquent->files_ms_parameters);
                        $files_ms_parameters->send_communication_file_itinerary_id = $itineraryEloquent->id;
                        $fileItineraryEloquementModel = FileItineraryEloquentModel::find($itineraryEloquent->id);
                        $fileItineraryEloquementModel->files_ms_parameters = json_encode((array) $files_ms_parameters);
                        $fileItineraryEloquementModel->save();
                        $this->send_notification($files_ms_parameters);
                    }

                }

            // }
        }

        return $itinerariesData;
    }


    protected function saveAccommodations(FileItineraryEloquentModel $itineraryEloquent, $accommodations): void
    {

        foreach ($accommodations as $accommodation) {

            $reservationPassenger = $itineraryEloquent->file->passengers->filter(function ($value) use ($accommodation) {
                return $value['sequence_number'] == $accommodation->filePassengerId->value();  // en filePassengerId se guarda el sequence_number que llega del request
            })->first();

            if($reservationPassenger){
                $accommodation->filePassengerId->setValue($reservationPassenger->id);
            }
            $fileItineraryAccommodation = FileItineraryAccommodationMapper::toEloquent($accommodation);
            $itineraryEloquent->accommodations()->save($fileItineraryAccommodation);

        }
    }


    protected function saveHotelRooms(FileItineraryEloquentModel $itineraryEloquent, $hotelRoomsData): void
    {
        $totalAdults = 0;
        $totalChildren = 0;
        $totalInfants = 0;
        $totalAmountCost = 0;

        foreach ($hotelRoomsData as $hotelRoomData) {
            $hotelRoomData->totalRooms->setValue($hotelRoomData->units->count()); // Cantidad de rooms
            $hotelRoomEloquent = FileHotelRoomMapper::toEloquent($hotelRoomData);
            $totalAdults += $hotelRoomEloquent->total_adults;
            $totalChildren += $hotelRoomEloquent->total_children;
            $totalInfants += $hotelRoomEloquent->total_infants;
            $totalAmountCost += $hotelRoomEloquent->amount_cost;
            $itineraryEloquent->rooms()->save($hotelRoomEloquent);
            $this->saveRoomAmountLogs($hotelRoomEloquent, $hotelRoomData->fileRoomAmountLogs);
            $this->saveHotelRoomUnits($hotelRoomEloquent, $hotelRoomData->units);
        }

        $itineraryEloquent->total_adults = $totalAdults;
        $itineraryEloquent->total_children = $totalChildren;
        $itineraryEloquent->total_infants = $totalInfants;
        $itineraryEloquent->total_cost_amount = $totalAmountCost;
        $itineraryEloquent->profitability = $this->calculateProfitability(
            $itineraryEloquent->total_amount,
            $itineraryEloquent->total_cost_amount
        );
        $itineraryEloquent->save();

    }

    protected function saveRoomAmountLogs(
        FileHotelRoomEloquentModel $hotelRoomEloquent,
        $fileRoomAmountLogData): void
    {
        foreach ($fileRoomAmountLogData as $amountLog) {
            $hotelRoomUnitEloquent = FileRoomAmountLogMapper::toEloquent($amountLog);

            $hotelRoomEloquent->fileRoomAmountLogs()->save($hotelRoomUnitEloquent);
        }
    }

    protected function saveHotelRoomUnits(
        FileHotelRoomEloquentModel $hotelRoomEloquent,
        $hotelRoomUnitData): void
    {
        foreach ($hotelRoomUnitData as $hotelRoomUnit) {
            $hotelRoomUnitEloquent = FileHotelRoomUnitMapper::toEloquent($hotelRoomUnit);
            $hotelRoomEloquent->units()->save($hotelRoomUnitEloquent);
            $this->saveHotelRoomUnitNights($hotelRoomUnitEloquent, $hotelRoomUnit->nights);
            $this->saveRoomAccommodations($hotelRoomUnitEloquent, $hotelRoomUnit->accommodations);
        }
    }

    protected function saveHotelRoomUnitNights(
        FileHotelRoomUnitEloquentModel $fileHotelRoomUnitEloquentModel,
        $hotelRoomUnitNightsData
    ): void {
        foreach ($hotelRoomUnitNightsData as $hotelRoomUnitNight) {
            $hotelRoomUnitNightEloquent = FileHotelRoomUnitNightMapper::toEloquent($hotelRoomUnitNight);
            $fileHotelRoomUnitEloquentModel->nights()->save($hotelRoomUnitNightEloquent);
        }
    }

    protected function saveRoomAccommodations(
        FileHotelRoomUnitEloquentModel $fileHotelRoomUnitEloquentModel,
        $roomUnitAccommodationsData
    ): void {
        foreach ($roomUnitAccommodationsData as $accommodations) {

            $reservationPassenger = $fileHotelRoomUnitEloquentModel->fileHotelRoom
                ->fileItinerary->file->passengers->filter(function ($value) use ($accommodations) {
                return $value['sequence_number'] == $accommodations->filePassengerId->value();  // en filePassengerId se guarda el sequence_number que llega del request
            })->first();

            if($reservationPassenger){
                $accommodations->filePassengerId->setValue($reservationPassenger->id);
            }
            $hotelRoomUnitNightEloquent = FileRoomAccommodationMapper::toEloquent($accommodations);
            $fileHotelRoomUnitEloquentModel->accommodations()->save($hotelRoomUnitNightEloquent);

        }
    }

    protected function savePassengers(FileEloquentModel $fileEloquent, array $passengerData): void
    {
        $adults = $children = $infants = 0;
        foreach ($passengerData as $passenger) {
            // $passenger->fileId->setValue($fileEloquent->id);
            $passengerEloquent = FilePassengerMapper::toEloquent($passenger);
            $fileEloquent->passengers()->save($passengerEloquent);

            if ($passengerEloquent->type == 'ADL') {
                $adults++;
            } elseif ($passengerEloquent->type == 'CHD') {
                $children++;
            } elseif ($passengerEloquent->type == 'INF') {
                $infants++;
            }
        }

        $fileEloquent->adults = $adults;
        $fileEloquent->children = $children;
        $fileEloquent->infants = $infants;
        $fileEloquent->save();
    }

    public function queryFile($id=null,$file_number=null){

        $fileEloquent = FileEloquentModel::query()->with([
            'status_reason',
            'vips.vip',
            'statement',
            'categories.category',
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms',
                    'services.compositions',
                    'flights'
                ]);
                $query->orderBy('date_in');
                $query->orderBy('start_time');
                $query->where(function ($query){
                    $query->where('total_amount', '>', 0)->orWhere('status', '=', 1);
                });
            },
        // ])->findOrFail($id);
        ])->when(!empty($id), function ($query) use ($id) {
            return $query->where('id', $id);
        })->when(!empty($file_number), function ($query) use ($file_number) {
            return $query->where('file_number', $file_number);
        })->first();

        return $fileEloquent;
    }

    public function queryFileAll($id=null,$file_number=null){

        $fileEloquent = FileEloquentModel::query()->with([
            'passengers',
            'fileStatusReason.statusReason',
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms.fileRoomAmount.fileAmountReason',
                    'rooms.fileRoomAmount.fileAmountTypeFlag',
                    'rooms.fileRoomAmountLogs'=> function ($query) {
                        $query->with('fileAmountReason');
                        $query->with('fileAmountTypeFlag');
                        $query->whereHas('fileAmountReason', function ($query){
                            $query->where('area', 'COMERCIAL');
                            $query->where('visible', 1);
                        });
                    },
                    'rooms.units.accommodations.filePassenger',
                    'rooms.units.nights',
                    'rooms.units.fileHotelRoom',
                    'services.fileServiceAmount.fileAmountReason',
                    'services.fileServiceAmount.fileAmountTypeFlag',
                    'services.fileServiceAmountLogs'=> function ($query) {
                        $query->with('fileAmountReason');
                        $query->with('fileAmountTypeFlag');
                        $query->whereHas('fileAmountReason', function ($query){
                            $query->where('area', 'COMERCIAL');
                            $query->where('visible', 1);
                        });
                    },
                    'services.compositions'=> function ($query) {
                        $query->with('fileService.fileItinerary');
                        $query->with('units.accommodations.filePassenger');
                        $query->with('supplier');
                    },
                    'flights.accommodations.filePassenger',
                    'descriptions',
                    'details',
                    'accommodations.filePassenger',
                    'service_amount_logs.fileServiceAmountLog',
                    'room_amount_logs.fileRoomAmountLog'
                ]);
                $query->orderBy('date_in');
                $query->orderBy('start_time');
            },
        // ])->findOrFail($id);
        ])->when(!empty($id), function ($query) use ($id) {
            return $query->where('id', $id);
        })->when(!empty($file_number), function ($query) use ($file_number) {
            return $query->where('file_number', $file_number);
        })->first();

        return $fileEloquent;
    }


    public function queryFileAllOpe($id=null,$file_number=null){

        $fileEloquent = FileEloquentModel::query()->with([
            'vips.vip',  
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms.units.accommodations',
                    'services',
                    'flights.accommodations',
                    'accommodations'
                ]);                
            },
        // ])->findOrFail($id);
        ])->when(!empty($id), function ($query) use ($id) {
            return $query->where('id', $id);
        })->when(!empty($file_number), function ($query) use ($file_number) {
            return $query->where('file_number', $file_number);
        })->first();

        return $fileEloquent;
    }

    public function queryFileDate($id = null, $date){
        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries'=> function ($query) use ($date){
                $query->with([
                    'flights',
                    'services.compositions'=> function ($query) {
                        $query->with('units.accommodations.filePassenger');
                    },
                    'flights.accommodations.filePassenger',
                    'accommodations.filePassenger',
                    'rooms.units:id,file_hotel_room_id,confirmation_code,amount_sale,amount_cost,taxed_unit_sale,taxed_unit_cost,adult_num,child_num,status,confirmation_status',
                    'rooms.units.accommodations:id,file_hotel_room_unit_id,file_passenger_id,room_key',
                    'rooms.units.accommodations.filePassenger:id,name,surnames,type',
                ]);
                $query->where('date_in', $date);
                $query->orderBy('date_in');
                $query->orderBy('start_time');
            },
        ])->when(!empty($id), function ($query) use ($id) {
            return $query->where('id', $id);
        })->first();

        return $fileEloquent;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): File
    {
        $fileEloquent = $this->queryFile($id);
        if($fileEloquent)
            return FileMapper::fromEloquent($fileEloquent);

        throw new \DomainException("file number does not exist");
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findByIdAll(int $id): array
    {
        $fileEloquent = $this->queryFile($id);
        if($fileEloquent){
            return $this->getFileReturn($fileEloquent);
            // return FileMapper::fromEloquent($fileEloquent);
        }
        throw new \DomainException("file number does not exist");
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function find_file_basic_info(int $id): array|null
    {       
        $fileEloquent = FileEloquentModel::with('vips.vip')->with('status_reason')->with('categories.category')->with('statement')->select(
             DB::raw("(select id from file_vips where file_id=files.id and deleted_at is null   limit 1) as vip"),'id' , 'file_number', 'client_id', 'client_code' , 'client_name', 'client_have_credit', 'client_credit_line', 'lang','description' ,'date_in', 'date_out', 'suggested_accommodation_sgl', 'suggested_accommodation_dbl', 'suggested_accommodation_tpl', 'status','status_reason_id', 'processing', 'revision_stages', 'ope_assign_stages')->find($id);
        if($fileEloquent)
            return $fileEloquent->toArray();
        return null;

        if($fileEloquent)
            return $fileEloquent->toArray();
        return null;
    }

    public function find_file_number_basic_info(int $file_number, array $params): array|null
    {
        $hotel_code = $params['code_hotel'] ?? ''; 
        $fileEloquent = FileEloquentModel::query()
            ->select(DB::raw("(select id from file_vips where file_id=files.id and deleted_at is null   limit 1) as vip"), 'id' , 'file_number', 'client_id', 'client_code' , 'client_name','description' ,'date_in', 'date_out','description','adults','children','infants', 'executive_code','lang','suggested_accommodation_sgl', 'suggested_accommodation_dbl', 'suggested_accommodation_tpl', 'status', 'status_reason_id', 'processing','revision_stages', 'ope_assign_stages')
            ->where('file_number',$file_number)
            ->with('vips.vip')
            ->with('status_reason')
            ->with('categories.category')
            ->with(['itineraries' => function ($query) use ($hotel_code) {
                $query->where('entity', 'hotel');
                $query->where('object_code', $hotel_code);
                $query->select('category', 'object_id', 'file_id'); // Asegúrate de incluir la clave foránea
            }])
            ->first();

        $fileEloquent->category = "";
        $fileEloquent->hotel_object_id = "";

        if(isset($params['code_hotel'])){
            $fileEloquent->category = $fileEloquent->itineraries->pluck('category')->first();
            $fileEloquent->hotel_object_id = $fileEloquent->itineraries->pluck('object_id')->first();
        }

        if($fileEloquent)
            return $fileEloquent->toArray();
        return null;
    }

    function getFileReturn($fileEloquent)
    {

        $fileEloquent = $fileEloquent->toArray();
        $itinerary_amount_logs = [];

        // foreach($fileEloquent['itineraries'] as $itinerary)
        // {

        //     if(count($itinerary['service_amount_logs']) > 0) {
        //         foreach($itinerary['service_amount_logs'] as $amount_log ) {
        //             $amount_log['type'] = 'service';
        //             array_push($itinerary_amount_logs,$amount_log );
        //         }
        //     }

        //     if(count($itinerary['room_amount_logs'])>0) {
        //         foreach($itinerary['room_amount_logs'] as $amount_log ) {
        //             $amount_log['type'] = 'hotel';
        //             array_push($itinerary_amount_logs, $amount_log );
        //         }
        //     }

        // }

        $fileEloquent['service_amount_logs'] = $itinerary_amount_logs;

        // foreach($fileEloquent['passengers'] as $id=> $passengers)
        // {
        //     $fileEloquent['passengers'][$id]['room_type_description'] = FilePassengerEloquentModel::getRoomTypeDescription($passengers['suggested_room_type']);
        // }
        return $fileEloquent;
    }


    /**
     * Esto solo lo usar files
     * @param int $id
     * @return File|null
     */
    public function findByNumber(int $file_number): array  | null
    {
        $fileEloquent = $this->queryFileAllOpe(null, $file_number);
        if($fileEloquent)
            return $fileEloquent = $fileEloquent->toArray(); 

        throw new \DomainException("file does not exist");
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function findByIdComplet(int $file_id): array  | null
    {
        $fileEloquent = $this->queryFileAll($file_id, null);
        if($fileEloquent)
            return $this->getFileReturn($fileEloquent);

        return null;
    }


    /**
     * @param int $id
     * @return File|null
     */
    public function validateFileExist(int|null $file_id , int|null $file_number): array  | null
    {

        $fileEloquent = FileEloquentModel::query()->select('id' ,'status')->when(!empty($file_id), function ($query) use ($file_id) {
            return $query->where('id', $file_id);
        })->when(!empty($file_number), function ($query) use ($file_number) {
            return $query->where('file_number', $file_number);
        })->first();

        if($fileEloquent)
            return $fileEloquent->toArray();

        return null;
    }


    /**
     * @param int $id
     * @return File|null
     */
    public function findByIdToArray(int $id): ?array
    {
        $fileEloquent = FileEloquentModel::query()->with([
            'vips.vip',
            'itineraries.rooms.units.accommodations.filePassenger',
            'itineraries.rooms.units.nights',
            'itineraries.services.compositions'=> function ($query) {
                $query->with('units.accommodations');
                $query->with('supplier');
            },
            'itineraries.flights.accommodations.filePassenger'
        ])->findOrFail($id);

        $file = $fileEloquent->toArray();

        return $file;
    }

    /**
     * @param int $id
     * @return array
     */
    public function findByIdFields(int $id, array $fields): array
    {
        $fileEloquent = FileEloquentModel::query()->findOrFail($id);
        $file_mapper = FileMapper::fromEloquent($fileEloquent, false, false, false);
        $file = FileMapper::toArray($file_mapper); $response = [];

        foreach($fields as $field)
        {
            $response[$field] = $file[$field];
        }

        return $response;
    }

    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function update(int $id, array $file): File
    {
        $fileEloquent = FileEloquentModel::query()->findOrFail($id);
        $fileEloquent->description = $file['description'];
        $fileEloquent->lang = $file['lang'];
        $fileEloquent->save();

        return FileMapper::fromEloquent($fileEloquent);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateStatement(int $id): bool
    {
        // $fileEloquent = FileEloquentModel::query()->findOrFail($id);
        // $fileEloquent->total_amount = $fileEloquent->itineraries->sum('total_amount');
        // $fileEloquent->save();
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function createInitialFileStatusReasons(int $id): bool
    {
        $fileStatusReasonEloquentModel = FileStatusReasonEloquentModel::where('file_id', $id)->where('status_reason_id', 1)->get();
        if(count($fileStatusReasonEloquentModel) == 0){
            FileStatusReasonEloquentModel::create([
                'file_id' => $id,
                'status' => 'OK',
                'status_reason_id' => 1
            ]);
        } 
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updatePassengerChanges(int $id, $statusChanges = false): bool
    {
        $fileEloquent = FileEloquentModel::query()->findOrFail($id);
        $fileEloquent->passenger_changes = $statusChanges;
        $fileEloquent->save();
        event(new FilePassToOpeEvent($id));
        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function updateTypeRoomPassenger(int $id): bool
    {

        $filePassengerEloquent = FilePassengerEloquentModel::query()->where('file_id', $id)->get();
        foreach($filePassengerEloquent as $filePassenger){

            $roomAccommodation = FileRoomAccommodationEloquentModel::query()->with(['fileHotelRoomUnit.fileHotelRoom', 'fileHotelRoomUnit.accommodations.filePassenger'])->where('file_passenger_id', $filePassenger->id)->first();

            $accommodationPassengers = [];
            if(isset($roomAccommodation->fileHotelRoomUnit)){
                foreach($roomAccommodation->fileHotelRoomUnit->accommodations as $accommodation){
                    if($accommodation->file_passenger_id !== $filePassenger->id){
                        array_push($accommodationPassengers, $accommodation->filePassenger->id);
                    }
                }
            }

            $cost_by_passenger = 0;
            $roomAccommodationAll = FileRoomAccommodationEloquentModel::query()->with(['fileHotelRoomUnit'])->where('file_passenger_id', $filePassenger->id)->get();

            foreach($roomAccommodationAll as $roomAccommod){
                $fileHotelRoomUnit = $roomAccommod->fileHotelRoomUnit;
                $amount_cost = $fileHotelRoomUnit->amount_cost;
                $totalPax = $fileHotelRoomUnit->adult_num + $fileHotelRoomUnit->child_num;
                $imporByPax = $amount_cost/$totalPax;

                $cost_by_passenger = $cost_by_passenger + $imporByPax;
            }

            FilePassengerEloquentModel::where("id", $filePassenger->id)->update([
                'suggested_room_type' => isset($roomAccommodation->fileHotelRoomUnit) ? $roomAccommodation->fileHotelRoomUnit->fileHotelRoom->occupation : NULL,
                'accommodation' => count($accommodationPassengers)>0 ? json_encode($accommodationPassengers) : NULL,
                'cost_by_passenger' => $cost_by_passenger
            ]);
        }

        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return false;
    }

    public function searchFilesQuery(array $filters): LengthAwarePaginator
    {
        $fileEloquent = FileEloquentModel::with(['vips.vip', 'itineraries.services'])->select(
            DB::raw("(select id from file_vips where file_id=files.id and deleted_at is null   limit 1) as vip"),
            DB::raw("(select id from file_itineraries where file_id=files.id and deleted_at is null   limit 1) as itinerary"),
            DB::raw("IF(revision_stages is null, 1, revision_stages) as revision_stages"),
            'files.*');

        if(isset($filters['complete'])) {
            if($filters['complete'] == true){
               $fileEloquent->whereHas('itineraries');
            }else{
                $fileEloquent->whereDoesntHave('itineraries');
            }
        }

        if (!empty($filters['filter'])) {
            $filter = $filters['filter'];

            $fileEloquent = $fileEloquent->where(function ($q) use ($filter) {

               $q->orwhere('file_number', 'like', '%' . $filter . '%');
                if(strlen($filter) !== 6)
                {
                    $q->where('order_number', 'like', '%' . $filter . '%');
                    $q->orwhere('reservation_number', 'like', '%' . $filter . '%');
                    $q->orwhere('description', 'like', '%' . $filter . '%');
                    $q->orwhere(DB::raw("(select id from file_passengers where file_id=files.id and CONCAT(name,' ',surnames,' ',document_number)"), 'like', DB::raw("'%" . $filter . "%' limit 1)") );
                }
            });
        }

        if (!empty($filters['executive_code'])) {
            $fileEloquent = $fileEloquent->where('executive_code', $filters['executive_code']);
        }

        if (!empty($filters['client_id'])) {
            $fileEloquent = $fileEloquent->where('client_id', $filters['client_id']);
        }

        $date_range = $filters['date_range'];

        if (!empty($date_range)) {
            $fileEloquent = $fileEloquent->where('date_in', '>=', $date_range[0]);
            $fileEloquent = $fileEloquent->where('date_in', '<=', $date_range[1]);
        }

        $filter_next_days = $filters['filter_next_days'];
        $revision_stages = $filters['revision_stages'];
        $filter_by = $filters['filter_by'];
        $filter_by_type = $filters['filter_by_type'];

        if (!empty($filter_next_days) || !empty($revision_stages)) {
            if (!empty($revision_stages)) {
                $filter_next_days = 45;
            }

            $time = strtotime(date('Y-m-d'));


            if($filter_next_days == 7){

                $date = date('Y-m-d', strtotime('+0 days', $time));
                $max = date('Y-m-d', strtotime('+7 days', $time));
            }

            if($filter_next_days == 15){
                $date = date('Y-m-d', strtotime('+8 days', $time));
                $max = date('Y-m-d', strtotime('+15 days', $time));
            }

            if($filter_next_days == 30){
                $date = date('Y-m-d', strtotime('+16 days', $time));
                $max = date('Y-m-d', strtotime('+1 month', $time));
            }

            if($filter_next_days == 45){
                $date = date('Y-m-d', strtotime('+0 days', $time));
                $max = date('Y-m-d', strtotime('+45 days', $time));
            }

            $fileEloquent = $fileEloquent->whereBetween('date_in', [$date, $max]);
        }

        if (!empty($revision_stages)) {
            if ($revision_stages == 2) {
                $fileEloquent = $fileEloquent->where('revision_stages', 2);
            } else {
                $fileEloquent = $fileEloquent->where(function ($query) {
                    $query->where('revision_stages', 1)
                        ->orWhereNull('revision_stages');
                });
            }
        }

        if ($filter_by == 'vips' ) {
            // $fileEloquent = $fileEloquent->has('vips');
            $fileEloquent = $fileEloquent->orderBy('vip', $filter_by_type);
        }elseif($filter_by == 'status'){
            $fileEloquent = $fileEloquent->orderBy('status', $filter_by_type);
        }elseif($filter_by == 'revision_stages'){
            $fileEloquent = $fileEloquent->orderBy('revision_stages', $filter_by_type);
        }else{
            $fileEloquent = $fileEloquent->orderBy('itinerary', 'asc');
        }
    //    dd( $fileEloquent->toSql());
        $perPage = $filters['per_page'];
        $page = $filters['page'];
        $count = $fileEloquent->count();

        $files = [];
        foreach ($fileEloquent->paginate($perPage, ['*'], 'page', $page) as $file) {

            $show_master_services = true;
            foreach($file->itineraries as $itinerary)
            {
                if($itinerary->entity == "service" || $itinerary->entity == "service-temporary")
                {
                    if(count($itinerary->services) == 0)
                    {
                        $show_master_services = false;
                        break;
                    }
                }
            }

            $file['show_master_services'] = $show_master_services;
            array_push($files, $file);
        }
        return new LengthAwarePaginator(
            $files,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }


    public function searchFileStelaQuery(array $params): array
    {
        $param_stela = [];
        $param_stela['filter'] = $params['filter'];
        $param_stela['executive_code'] = $params['executive_code'];
        $param_stela['client_code'] = $params['client_code'];
        $param_stela['page'] = isset($params['page']) ? $params['page'] : 1;
        $param_stela['limit'] = isset($params['per_page']) ? $params['per_page'] : 9;
        $param_stela['date_from'] = '';
        $param_stela['date_to'] = '';

        $date_range = $params['date_range'];
        if (!empty($date_range)) {
            $param_stela['date_from'] = Carbon::parse($date_range[0])->format('d/m/Y');
            $param_stela['date_to'] = Carbon::parse($date_range[1])->format('d/m/Y');
        }
        
        $stella = new ApiGatewayExternal();
        $files_stela = $stella->search_file_stela($param_stela, 'all', true, [], true);

        $files_stela = (array) $files_stela;
        $files = [];

        foreach($files_stela['data'] as $file)
        {
            array_push($files, $this->getDataFileStela($file));
        }

        return [
            'success' => true,
            'data' => $files,
            'pagination' => [
                'current_page' => $param_stela['page'],
                'last_page' => $files_stela['totalPages'],
                'per_page' => $param_stela['limit'],
                'total' => $files_stela['totalItems'],
            ],
            'code' => 200
        ];
    }

    public function getDataFileStela($data){

        $data = (array) $data;

        return [
            "id" => null,
            "serie_reserve_id" => 0,
            "client_id" => null,
            "client_code" => $data['client_code'],
            "client_name" => null,
            "reservation_id" => 0,
            "order_number" => null,
            "file_number" => $data['file_number'],
            "reservation_number" => null,
            "budget_number" => null,
            "sector_code" => null,
            "group" => null,
            "sale_type" => null,
            "tariff" => null,
            "currency" => null,
            "revision_stages" => null,
            "ope_assign_stages" => null,
            "executive_code" => $data['executive_code'],
            "executive_code_sale" => $data['executive_code_process'],
            "executive_code_process" => $data['executive_code'],
            "applicant" => null,
            "file_code_agency" => null,
            "description" => $data['description'],
            "lang" => "EN",
            "date_in" => $data['date_in'],
            "date_out" => $data['date_in'],
            "adults" => $data['adults'],
            "children" => $data['children'],
            "infants" => $data['infants'],
            "use_invoice" => null,
            "observation" => null,
            "total_pax" => null,
            "have_quote" => false,
            "have_voucher" => false,
            "have_ticket" => false,
            "have_invoice" => false,
            "status" => $data['status'],
            "promotion" => "",
            "total_amount" => 0,
            "markup_client" => 0,
            "type_class_id" => null,
            "suggested_accommodation_sgl" => $data['suggested_accommodation_sgl'],
            "suggested_accommodation_dbl" => $data['suggested_accommodation_dbl'],
            "suggested_accommodation_tpl" => $data['suggested_accommodation_tpl'],
            "generate_statement" => true,
            "protected_rate" => false,
            "view_protected_rate" => false,
            "file_reason_statement_id" => null,
            "itineraries" => [],
            "vips" => [],
            "passengers" => [],
            "created_at" => null,
            "status_reason" => "Primera apertura del File",
            "status_reason_id" => 1,
            "origin" => "stela",
            "stela_processing" => 0, // 0=visualizado ,  1=importando... , 2=importado, 3= Error
            "stela_processing_error" => ''
        ];
    }

    public function getFilesExists($results): array
    {
        $files = [];
        foreach($results as $result){
            array_push($files, $result['file_number']);
        }

        $files_db = FileEloquentModel::whereIn('file_number', $files)->pluck('file_number')->toArray();

        return $files_db;
    }


    public function create_stela(array $params): array
    {
        $client_no_exists = [];
        $errors = [];
        $file_exists = [];

        $results = $this->searchFileStela($params);
        $clients = $this->getClientAurora($results);
        $exists = $this->getFilesExists($results);

        foreach($results as $file)
        {
            if(in_array($file['file_number'], $exists))
            {
                array_push($file_exists, $file);
                continue;
            }

            $client_id = NULL;
            $client_name = NULL;
            $stela_processing = 3;
            $stela_processing_error = "cliente no existe en aurora";

            if(isset($clients[$file['client_code']])){
                $client_id = $clients[$file['client_code']]['id'];
                $client_name = $clients[$file['client_code']]['name'];
                $stela_processing = 1;
                $stela_processing_error = "";
            }



            $file['markup_client'] = 0;
            $file['file_number'] = $file['file_number'];
            $file['client_id'] = $client_id;
            $file['client_code'] = $file['client_code'];
            $file['client_name'] = $client_name;
            $file['executive_code'] = $file['executive_code'];
            $file['executive_code_process'] = $file['executive_code_process'];
            $file['executive_code_sale'] = $file['executive_code'];
            $file['status'] = $file['status'];
            $file['date_in'] = $file['date_in'];
            $file['date_out'] = $file['date_in'];
            $file['adults'] = $file['adults'];
            $file['children'] = $file['children'];
            $file['infants'] = $file['infants'];
            $file['description'] = $file['description'];
            $file['type_class_id'] = null;
            $file['suggested_accommodation_sgl'] = $file['suggested_accommodation_sgl'];
            $file['suggested_accommodation_dbl'] = $file['suggested_accommodation_dbl'];
            $file['suggested_accommodation_tpl'] = $file['suggested_accommodation_tpl'];
            $file['generate_statement'] = false;
            $file['reason_statement_id'] = null;
            $file['protected_rate'] = false;
            $file['view_protected_rate'] = false;
            $file['origin'] = 'stela';
            $file['stela_processing'] = $stela_processing;
            $file['stela_processing_error'] = $stela_processing_error;

            $fileEloquentModel = $this->fileToEloquentByStela($file);
            $fileEloquentModel->save();
            FileStatusReasonEloquentModel::create([
                'file_id' => $fileEloquentModel->id,
                'status' => $file['status'],
                'status_reason_id' => 1
            ]);

        }

        return [
            'status' => true,
            'errors' => $errors,
            'exists' => $file_exists,
            'client_no_exists' => $client_no_exists
        ];
    }

    public function create_stela_all(array $params): bool
    {

        $param_stela = [];
        $param_stela['filter'] = $params['filter'];
        $param_stela['executive_code'] = $params['executive_code'];
        $param_stela['client_code'] = $params['client_code'];
        $param_stela['date_from'] = '';
        $param_stela['date_to'] = '';
        $param_stela['page'] = 1;
        $param_stela['limit'] = 999999999999999;

        $date_range = $params['date_range'];
        if (!empty($date_range)) {
            $param_stela['date_from'] = Carbon::parse($date_range[0])->format('d/m/Y');
            $param_stela['date_to'] = Carbon::parse($date_range[1])->format('d/m/Y');
        }

        if($param_stela['filter'] == '' and $param_stela['executive_code'] == '' and $param_stela['client_code'] == '' and $param_stela['date_from'] == '' and $param_stela['date_to'] == '')
        {
            throw new \DomainException("There must be at least 1 parameter");
        }


        // ProcessFileCreateFileStelaJob::dispatchSync($request->all);
        dispatch(new ProcessFileCreateFileStelaJob($param_stela));


        return true;
    }

    public function create_file_services_stela($file_id, array $params): bool
    {

        $fileEloquent = FileEloquentModel::query()->findOrFail($file_id);

        if($fileEloquent->origin != 'stela'){
            throw new \DomainException("the origin is not stela");
        }

        if($fileEloquent->stela_processing != '1'){
            throw new \DomainException("the file has already been processed");
        }

        ProcessFileCreateFileServicesStelaJob::dispatchSync($fileEloquent->id,$fileEloquent->file_number);

        // dispatch(new ProcessFileCreateFileServicesStelaJob($fileEloquent->id,$fileEloquent->file_number));

        return true;
    }


    /**
     * @return array
     */
    public function findAll(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $files = [];
        foreach (FileEloquentModel::paginate($perPage, ['*'], 'page', $page) as $fileEloquent) {
            $fileWithRelationsData = FileMapper::fromEloquent($fileEloquent, false, false, false);
            $files[] = FileMapper::toEloquent($fileWithRelationsData);
        }

        return new LengthAwarePaginator(
            $files,
            FileEloquentModel::count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function statistics(string $date): array
    {
        $time = strtotime($date);
        $date = date('Y-m-d', $time);
        $today = date('Y-m-d', strtotime('+7 days', $time));
        $quarter = date('Y-m-d', strtotime('+15 days', $time));
        $month = date('Y-m-d', strtotime('+1 month', $time));
        $max = date('Y-m-d', strtotime('+45 days', $time));

        $files = collect([]);
        $filesEloquent = FileEloquentModel::query()->whereBetween('date_in', [$date, $max])->get();

        foreach ($filesEloquent as $fileEloquent) {
            // $fileWithRelationsData = FileMapper::fromEloquent($fileEloquent);
            // $fileItinerary = FileMapper::toEloquent($fileWithRelationsData);
            // $files->push($fileItinerary);
            $files->push($fileEloquent);
        }

        $files_today = 0;
        $files_quarter = 0;
        $files_month = 0;
        $files_max = 0;
        $files_total = 0;
        $files_total_ope = 0;
        $files_total_dtr = 0;

        $total_filters=[
            'files_today' => [] ,
            'files_quarter' => [] ,
            'files_month' => [] ,
            'files_max' => [] ,
            'files_total' => [] ,
            'files_total_ope' => [],
            'files_total_dtr' => []
         ];

        $files = $files->each(function ($file)
        use (
            $today,
            $quarter,
            $month,
            $max,
            &$files_today,
            &$files_quarter,
            &$files_month,
            &$files_max,
            &$files_total,
            &$files_total_ope,
            &$files_total_dtr,
            &$total_filters
        ) {

            if ($file->date_in <= $today) {
                $files_today += 1;
                array_push($total_filters['files_today'], ['id'=>$file->id, 'date_in' => $file->date_in]);
            } else {
                if ($file->date_in <= $quarter) {
                    $files_quarter += 1;
                    array_push($total_filters['files_quarter'], ['id'=>$file->id, 'date_in' => $file->date_in]);
                } else {
                    if ($file->date_in <= $month) {
                        $files_month += 1;
                        array_push($total_filters['files_month'], ['id'=>$file->id, 'date_in' => $file->date_in]);
                    } else {
                        if ($file->date_in <= $max) {
                            $files_max += 1;
                            array_push($total_filters['files_max'], ['id'=>$file->id, 'date_in' => $file->date_in]);
                        }
                    }
                }
            }

            if (strtoupper($file->status) == 'OK') {
                $files_total += 1;
                array_push($total_filters['files_total'], ['id'=>$file->id, 'date_in' => $file->date_in]);
            }

            if ($file->revision_stages == 2) {
                $files_total_ope += 1;
                array_push($total_filters['files_total_ope'], ['id'=>$file->id, 'date_in' => $file->date_in]);
            } else {
                $files_total_dtr += 1;
                array_push($total_filters['files_total_dtr'], ['id'=>$file->id, 'date_in' => $file->date_in]);
            }

            return $file;
        });

        return [
            'date_max' => $max,
            'date_month' => $month,
            'date_quarter' => $quarter,
            'date_weekly' => $today,
            'date' => $date,
            'files_max' => $files_max,
            'files_month' => $files_month,
            'files_quarter' => $files_quarter,
            'files_weekly' => $files_today,
            'files_total' => $files_total_ope + $files_total_dtr,
            'files_total_ope' => $files_total_ope,
            'files_total_dtr' => $files_total_dtr,
            'total_filters'=>$total_filters
        ];
    }

    public function updateSerie(array $params): bool
    {
        $fileEloquent = FileEloquentModel::query()->findOrFail($params['file_id']);
        $fileEloquent->serie_id = $params['serie_id'];
        $fileEloquent->save();

        return true;
        // return FileMapper::fromEloquent($fileEloquent);
    }

    public function removeSerie(array $params): bool
    {
        $fileEloquent = FileEloquentModel::query()->findOrFail($params['file_id']);
        $fileEloquent->serie_id = null;
        $fileEloquent->save();

        return true;
        // return FileMapper::fromEloquent($fileEloquent);
    }

    public function searchAllStatus(array $params): array
    {
        $lang_iso = $params['lang_iso'];

        if ($lang_iso == 'es') {
            $response = [
                ['iso' => 'OK', 'name' => 'Abierto'],
                ['iso' => 'XL', 'name' => 'Anulado'],
                ['iso' => 'BL', 'name' => 'Bloqueado'],
                ['iso' => 'CE', 'name' => 'Cerrado'],
                ['iso' => 'PF', 'name' => 'Por Facturar'],
            ];
        } else {
            $response = [
                ['iso' => 'OK', 'name' => 'Open'],
                ['iso' => 'XL', 'name' => 'Override'],
                ['iso' => 'BL', 'name' => 'Blocked'],
                ['iso' => 'CE', 'name' => 'Closed'],
                ['iso' => 'PF', 'name' => 'To Bill'],
            ];
        }

        return $response;
    }

    public function searchAllStages(): array
    {
        return [
            ['id' => 1, 'iso' => 'DTR'],
            ['id' => 2, 'iso' => 'OPE'],
        ];
    }

    public function searchHaveInvoice(array $params): array
    {
        $lang_iso = $params['lang_iso'];

        if ($lang_iso == 'es') {
            $response = [
                ['iso' => 'SI', 'name' => 'Facturado', 'detail' => 'Se ha facturado todo lo que corresponde al file'],
                [
                    'iso' => 'NO',
                    'name' => 'No Facturado',
                    'detail' => 'Tiene servicios para factura pero no se han facturado ninguno de ellos'
                ],
                [
                    'iso' => '00',
                    'name' => 'No Facturable',
                    'detail' => 'No tiene servicios o los servicios que tiene ninguno de ellos se factura'
                ],
                ['iso' => 'PL', 'name' => 'Facturado Parcial', 'detail' => 'Se ha facturado algún servicio'],
            ];
        } else {
            $response = [
                [
                    'iso' => 'SI',
                    'name' => 'Invoiced',
                    'detail' => 'Everything that corresponds to the file has been invoiced'
                ],
                [
                    'iso' => 'NO',
                    'name' => 'Not Invoiced',
                    'detail' => 'You have services for billing but none of them have been billed'
                ],
                [
                    'iso' => '00',
                    'name' => 'No Facturable',
                    'detail' => 'It does not have services or the services that it has none of them are billed'
                ],
                ['iso' => 'PL', 'name' => 'Facturado Parcial', 'detail' => 'A service has been billed'],
            ];
        }

        return $response;
    }

    public function updatePassengers($file_id, $passengers): bool
    {
        foreach ($passengers as $value) {
            $filePassengerEloquent = FilePassengerEloquentModel::query()->findOrFail($value['id']);
            $filePassengerEloquent->file_id = $file_id;
            $filePassengerEloquent->sequence_number = $value['sequence_number'];
            $filePassengerEloquent->order_number = $value['order_number'];
            $filePassengerEloquent->frequent = $value['frequent'];
            $filePassengerEloquent->document_type_id = $value['document_type_id'];
            $filePassengerEloquent->doctype_iso = $value['doctype_iso'];
            $filePassengerEloquent->document_number = $value['document_number'];
            $filePassengerEloquent->name = $value['name'];
            $filePassengerEloquent->surnames = $value['surnames'];
            $filePassengerEloquent->date_birth = $value['date_birth'];
            $filePassengerEloquent->type = $value['type'];
            $filePassengerEloquent->suggested_room_type = $value['suggested_room_type'];
            $filePassengerEloquent->genre = $value['genre'];
            $filePassengerEloquent->email = $value['email'];
            $filePassengerEloquent->phone = $value['phone'];
            $filePassengerEloquent->country_iso = $value['country_iso'];
            $filePassengerEloquent->city_iso = $value['city_iso'];
            $filePassengerEloquent->dietary_restrictions = $value['dietary_restrictions'];
            $filePassengerEloquent->medical_restrictions = $value['medical_restrictions'];
            $filePassengerEloquent->save();
        }

        return true;
    }

    public function updateAccommodations($file_id, $type, $type_id, $passengers): bool
    {
        if ($type == 'all') {
            $itineraries = FileItineraryEloquentModel::query()->where('file_id', '=', $file_id)->get();

            foreach ($itineraries as $itinerary) {
                if ($itinerary->entity == 'service') {
                    $file_services = FileServiceEloquentModel::query()
                        ->with(['compositions.units.accommodations'])
                        ->where('file_itinerary_id', '=', $itinerary->id)->get();

                    $ignore_passengers = [];

                    $file_services->each(function ($file_service) use ($passengers, &$ignore_passengers) {

                        $file_service->compositions->each(function ($composition) use (
                            $passengers,
                            &$ignore_passengers
                        ) {

                            $composition->units->each(function ($unit) use (
                                $passengers,
                                $composition,
                                &
                                $ignore_passengers
                            ) {

                                $unit->accommodations->each(function ($accomodation) use (&$ignore_passengers) {
                                    $ignore_passengers[] = $accomodation->file_passenger_id;
                                });

                                $count = $composition->total_adults - $unit->accommodations->count();

                                foreach ($passengers as $passenger) {
                                    if ($count > 0 && !in_array($passenger['id'], $ignore_passengers)) {
                                        // Save the accommodation..
                                        $accommodation = new FileServiceAccommodationEloquentModel();
                                        $accommodation->file_passenger_id = $passenger['id'];
                                        $accommodation->file_service_unit_id = $unit->id;
                                        $accommodation->save();

                                        $ignore_passengers[] = $passenger['id'];

                                        $count--;
                                    }
                                }

                                return $unit;
                            });

                            return $composition;
                        });

                        return $file_service;
                    });

                }

                if ($itinerary->entity == 'hotel') {
                    $file_hotel_rooms = FileHotelRoomEloquentModel::query()
                        ->with(['units.accommodations'])
                        ->where('file_itinerary_id', '=', $itinerary->id)->get();

                    $ignore_passengers = [];

                    $file_hotel_rooms->each(function ($file_hotel_room) use ($passengers, &$ignore_passengers) {

                        $file_hotel_room->units->each(function ($unit) use (
                            $file_hotel_room,
                            $passengers,
                            &
                            $ignore_passengers
                        ) {

                            $unit->accommodations->each(function ($accomodation) use (&$ignore_passengers) {
                                $ignore_passengers[] = $accomodation->file_passenger_id;
                            });

                            $count = (ceil($file_hotel_room->total_adults / $file_hotel_room->units->count())) - $unit->accommodations->count();

                            foreach ($passengers as $passenger) {
                                if ($count > 0 && !in_array($passenger['id'], $ignore_passengers)) {
                                    // Save the accommodation..
                                    $accommodation = new FileRoomAccommodationEloquentModel();
                                    $accommodation->file_passenger_id = $passenger['id'];
                                    $accommodation->file_hotel_room_unit_id = $unit->id;
                                    $accommodation->room_key = '';
                                    $accommodation->save();

                                    $ignore_passengers[] = $passenger['id'];

                                    $count--;
                                }
                            }

                            return $unit;
                        });

                        return $file_hotel_room;
                    });
                }
            }
        }

        if ($type == 'service') {
            $file_services = FileServiceEloquentModel::query()
                ->with(['compositions.units.accommodations'])
                ->where('file_itinerary_id', '=', $type_id)->get();

            $ignore_passengers = [];

            $file_services->each(function ($file_service) use ($passengers, &$ignore_passengers) {

                $file_service->compositions->each(function ($composition) use ($passengers, &$ignore_passengers) {

                    $composition->units->each(function ($unit) use ($passengers, $composition, &$ignore_passengers) {

                        $unit->accommodations->each(function ($accomodation) use (&$ignore_passengers) {
                            $ignore_passengers[] = $accomodation->file_passenger_id;
                        });

                        $count = (ceil($composition->total_adults / $composition->units->count())) - $unit->accommodations->count();

                        foreach ($passengers as $passenger) {
                            if ($count > 0 && !in_array($passenger['id'], $ignore_passengers)) {
                                // Save the accommodation..
                                $accommodation = new FileServiceAccommodationEloquentModel();
                                $accommodation->file_passenger_id = $passenger['id'];
                                $accommodation->file_service_unit_id = $unit->id;
                                $accommodation->save();

                                $ignore_passengers[] = $passenger['id'];

                                $count--;
                            }
                        }

                        return $unit;
                    });

                    return $composition;
                });

                return $file_service;
            });
        }

        if ($type == 'hotel') {
            $file_hotel_rooms = FileHotelRoomEloquentModel::query()
                ->with(['units.accommodations'])
                ->where('file_itinerary_id', '=', $type_id)->get();

            $ignore_passengers = [];

            $file_hotel_rooms->each(function ($file_hotel_room) use ($passengers, &$ignore_passengers) {

                $file_hotel_room->units->each(function ($unit) use (
                    $file_hotel_room,
                    $passengers,
                    &$ignore_passengers
                ) {

                    $unit->accommodations->each(function ($accomodation) use (&$ignore_passengers) {
                        $ignore_passengers[] = $accomodation->file_passenger_id;
                    });

                    $count = (ceil($file_hotel_room->total_adults / $file_hotel_room->units->count())) - $unit->accommodations->count();

                    foreach ($passengers as $passenger) {
                        if ($count > 0 && !in_array($passenger['id'], $ignore_passengers)) {
                            // Save the accommodation..
                            $accommodation = new FileRoomAccommodationEloquentModel();
                            $accommodation->file_passenger_id = $passenger['id'];
                            $accommodation->file_hotel_room_unit_id = $unit->id;
                            $accommodation->room_key = '';
                            $accommodation->save();

                            $ignore_passengers[] = $passenger['id'];

                            $count--;
                        }
                    }

                    return $unit;
                });

                return $file_hotel_room;
            });
        }

        if ($type == 'room') {
            $file_hotel_room = FileHotelRoomEloquentModel::query()
                ->with(['units.accommodations'])
                ->where('id', '=', $type_id)->first();

            $file_hotel_room->units->each(function ($unit) use ($file_hotel_room, $passengers, &$ignore_passengers) {

                $unit->accommodations->each(function ($accomodation) use (&$ignore_passengers) {
                    $ignore_passengers[] = $accomodation->file_passenger_id;
                });

                $count = (ceil($file_hotel_room->total_adults / $file_hotel_room->units->count())) - $unit->accommodations->count();

                foreach ($passengers as $passenger) {
                    if ($count > 0 && !in_array($passenger['id'], $ignore_passengers)) {
                        // Save the accommodation..
                        $accommodation = new FileRoomAccommodationEloquentModel();
                        $accommodation->file_passenger_id = $passenger['id'];
                        $accommodation->file_hotel_room_unit_id = $unit->id;
                        $accommodation->room_key = '';
                        $accommodation->save();

                        $ignore_passengers[] = $passenger['id'];

                        $count--;
                    }
                }

                return $unit;
            });
        }
        return true;
    }

    /* Devolvemos solo los units que no han sido eliminados */
    public function filtrar_units(object $room, array $delete_rooms): array
    {
        $unitsFiltrados = [];
        foreach($room->units as $id => $unit){

            $encontro = false;
            foreach($delete_rooms as $deleteRoom)
            {
                if($room->id == $deleteRoom['id'])
                {

                    foreach($deleteRoom['units'] as $deletUnits)
                    {
                        if($unit->id == $deletUnits)
                        {
                            $encontro = true;
                        }
                    }

                }
            }
            if($encontro == false)
            {
                array_push($unitsFiltrados, $unit->toArray());
            }
        }
        // dd($delete_rooms,$unitsFiltrados);
        return $unitsFiltrados;
    }

    public function getReservationActive(array $params): array
    {
        $delete_rooms = isset($params['reservation_delete']) ? $params['reservation_delete'] : [];

        $file_itinerary_id = $params['file_itinerary_id'];
        $fileItineraryEloquent = FileItineraryEloquentModel::query()->with([
            'file.passengers','rooms.units.nights'
        ])->findOrFail($file_itinerary_id);

        $rooms = [];
        foreach($fileItineraryEloquent->rooms as $room)
        {

            $amount_cost = 0;
            $amount_sale = 0;
            $occupantADL = 0;
            $occupantCHD = 0;


            $units = $this->filtrar_units($room, $delete_rooms);

            if(count($units)>0)
            {
                $amount_cost = 0;
                $amount_sale = 0;
                $occupantADL = 0;
                $occupantCHD = 0;
                foreach($units as $id => $unit){
                    $amount_cost = $amount_cost +  $unit['amount_cost'];
                    $amount_sale = $amount_sale +  $unit['amount_sale'];
                    $occupantADL = $occupantADL + $unit['adult_num'];
                    $occupantCHD = $occupantCHD + $unit['child_num'];
                }

                $extructure = [
                    'date_in' => $fileItineraryEloquent['date_in'],
                    'date_out' => $fileItineraryEloquent['date_out'],
                    'channel_id' => $room['channel_id'],
                    'channel_reservation_code_master' => '',
                    'room_name' => $room['room_name'],
                    'total_rooms' =>  count($units),
                    'total_adults' =>  $occupantADL,
                    'total_children' =>  $occupantCHD,
                    'occupants' =>  $occupantADL + $occupantCHD,
                    'amount_cost' =>  $amount_cost,
                    'amount_sale' =>  $amount_sale,
                    'rate_plan_name' => $room['rate_plan_name'],
                    'rate_plan_code' => $room['rate_plan_code'],
                    'confirmation_status' => $room['confirmation_status'],
                    'unit' => $units
                ];

                array_push($rooms, $extructure);
            }

        }

        return [
            'date_in' => $fileItineraryEloquent->date_in,
            'date_out' => $fileItineraryEloquent->date_out,
            'start_time' => $fileItineraryEloquent->start_time,
            'departure_time' => $fileItineraryEloquent->departure_time,
            'nights' => $this->nights($fileItineraryEloquent->date_in,$fileItineraryEloquent->date_out),
            'rooms' => $rooms
        ];
    }

    public function nights($dateIn, $dateOut): int
    {
        $date1 = new \DateTime($dateIn);
        $date2 = new \DateTime($dateOut);
        $diff = $date1->diff($date2);

        return $diff->days;
    }

    public function getEternalExtructureReservation(array $params, array $file): array
    {

        $reservation_add = $params['reservation_add'];

        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchTokenHotels($reservation_add);
        $data = json_decode(json_encode($response), true);


        foreach($data['reservations_hotel'] as $ix => $hotel){
            $rq = true;
            $hotel_rooms = [];

            if(!isset($hotel['reservations_hotel_rooms'])){
                continue;
            }

            foreach($hotel['reservations_hotel_rooms'] as $ixp => $room){
                if($room['onRequest'] == "0"){
                    $rq = false;
                }
                $index = explode("_", $ixp);
                if(!isset($hotel_rooms[$index[0]])){
                    $hotel_rooms[$index[0]][] = $room;
                }else{
                    array_push($hotel_rooms[$index[0]], $room);
                }
            }

            foreach($hotel_rooms as $id => $rooms){
                $amount_cost = 0;
                $amount_sale = 0;
                $occupantADL = 0;
                $occupantCHD = 0;
                foreach($rooms as $id => $room){
                    $amount_cost = $amount_cost +  $room['total_amount_base'];
                    $amount_sale = $amount_sale +  $room['total_amount'];
                    $occupantADL = $occupantADL + $room['adult_num'];
                    $occupantCHD = $occupantCHD + $room['child_num'];
                }

                $extructure = [
                    'date_in' => $rooms[0]['check_in'],
                    'date_out' => $rooms[0]['check_out'],
                    'channel_id' => $rooms[0]['channel_id'],
                    'channel_reservation_code_master' => '',
                    'room_name' => $rooms[0]['room_name'],
                    'total_rooms' =>  count($rooms),
                    'total_adults' =>  $occupantADL,
                    'total_children' =>  $occupantCHD,
                    'occupants' =>  $occupantADL + $occupantCHD,
                    'amount_cost' =>  $amount_cost,
                    'amount_sale' =>  $amount_sale,
                    'rate_plan_name' => $rooms[0]['rate_plan_name'],
                    'rate_plan_code' => $rooms[0]['rate_plan_code'],
                    // 'onRequest' => $rooms[0]['onRequest'],
                    'confirmation_status' => $rooms[0]['onRequest'],
                    'unit' => $rooms
                ];

                if(!isset($data['reservations_hotel'][$ix]['rooms'])){
                    $data['reservations_hotel'][$ix]['rooms'][] = $extructure;
                }else{
                    array_push($data['reservations_hotel'][$ix]['rooms'], $extructure);
                }

            }
            // $data['reservations_hotel'][$ix]['hotel_rooms'] = $hotel_rooms;
            $data['reservations_hotel'][$ix]['confirmation_status'] = $rq;
        }

        $reservations_hotel = $data['reservations_hotel'][0];
        unset($reservations_hotel['reservations_hotel_rooms']);
        unset($data['reservations_hotel']);

        $data['itineraries'] = $reservations_hotel;
        $data['itineraries']['object_id'] = $data['itineraries']['hotel_id'];
        $data['itineraries']['object_code'] = $data['itineraries']['hotel_code'];
        $data['itineraries']['name'] = $data['itineraries']['hotel_name'];
        $data['itineraries']['date_in'] = Carbon::parse($data['itineraries']['check_in'])->format('Y-m-d');
        $data['itineraries']['date_out'] = Carbon::parse($data['itineraries']['check_out'])->format('Y-m-d');
        $data['itineraries']['start_time'] = Carbon::parse($data['itineraries']['check_in_time'])->format('H:i');
        $data['itineraries']['departure_time'] = Carbon::parse($data['itineraries']['check_out_time'])->format('H:i');
        $data['file_number'] = $file['file_number'];
        $data['description'] = $file['description'];

        $data['created_at'] = date('Y-m-d');

        $file = [
            'executive_code' => $file['executive_code'],
            'hotel_id' => $data['itineraries']['hotel_id'],
            'client_id' => $file['client_id']
        ];
        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchByCommunication($file);

        $data['executive_name'] = $response->executive->name;
        $data['executive_email'] = $response->executive->email;
        $data['client_name'] = $response->client->name;
        $data['client_nationality'] = $response->client->pais;
        $data['client_executives'] = $response->client_executives;
        $data['hotel_contacts'] = $response->hotel_contacts;
        $data['lang'] = "en";


        $delete_rooms = isset($params['reservation_delete']) ? $params['reservation_delete'] : [];
        if(count($delete_rooms)>0){
            $rowOld = $this->getReservationActive($params);
            if(($data['itineraries']['date_in'] != $rowOld['date_in']) and count($rowOld['rooms'])>0){

                $data['itineraries']['rooms_new'] = $data['itineraries']['rooms'];
                $data['itineraries']['rooms_new_date_in'] = $data['itineraries']['date_in'];
                $data['itineraries']['rooms_new_date_out'] = $data['itineraries']['date_out'];
                $data['itineraries']['rooms_new_start_time'] = $data['itineraries']['start_time'];
                $data['itineraries']['rooms_new_departure_time'] = $data['itineraries']['departure_time'];
                $data['itineraries']['rooms_new_nights'] = $data['itineraries']['nights'];

                $data['itineraries']['rooms'] = $rowOld['rooms'];
                $data['itineraries']['date_in'] = $rowOld['date_in'];
                $data['itineraries']['date_out'] = $rowOld['date_out'];
                $data['itineraries']['start_time'] = $rowOld['start_time'];
                $data['itineraries']['departure_time'] = $rowOld['departure_time'];
                $data['itineraries']['departure_time'] = $rowOld['departure_time'];
                $data['itineraries']['nights'] = $data['itineraries']['nights'];

            }else{
                $data['itineraries']['rooms'] = array_merge($rowOld['rooms'], $data['itineraries']['rooms']);
            }
        }

        return $data;


    }

    public function validateMultipleFecha($rowOld, $itineraries)
    {

    }

    public function validateCancelFile(FileEloquentModel $fileEloquent): array
    {
        $hotels = [];
        $cancelAllIntinerary = true;
        $cancelWithPenality = false;
        foreach($fileEloquent->itineraries as $fileItineraryEloquent)
        {
            $hotel_code = [];
            if($fileItineraryEloquent->entity == "hotel"){

                foreach($fileItineraryEloquent->rooms as $room){

                    if($room->status == "1"){
                        $cancelAllIntinerary = false;
                    }

                    foreach($room->units as $unit){
                        if($unit->confirmation_code){
                           if(!in_array($unit->confirmation_code, $hotel_code)){
                                array_push($hotel_code, $unit->confirmation_code);
                           }
                        }else{
                            if(!in_array($fileItineraryEloquent->object_code, $hotel_code)){
                                array_push($hotel_code, $fileItineraryEloquent->object_code);
                           }
                        }

                        if($unit->status == "1"){
                            $cancelAllIntinerary = false;
                        }

                        if($unit->status == "0" and $unit->amount_cost > 0 ){
                            $cancelWithPenality = true;
                        }
                    }
                }

                if(!isset($hotels[$fileItineraryEloquent->name])){

                    $hotels[$fileItineraryEloquent->name] = $hotel_code;
                }else{
                    foreach($hotels[$fileItineraryEloquent->name] as $code){
                        if(!in_array($code, $hotel_code)){
                            array_push($hotel_code, $code);
                        }
                   }
                }


            }

            if($fileItineraryEloquent->entity == "service"){

                foreach($fileItineraryEloquent->services as $service){

                    if($service->status == "1"){
                        $cancelAllIntinerary = false;
                    }
                    foreach($service->compositions as $composition){

                        if($composition->status == "1"){
                            $cancelAllIntinerary = false;
                        }

                        foreach($composition->units as $unit){

                            if($unit->status == "1"){
                                $cancelAllIntinerary = false;
                            }

                        }

                    }
                }

                if($fileItineraryEloquent->status == "0" and $fileItineraryEloquent->total_cost_amount > 0 ){
                    $cancelWithPenality = true;
                }
            }
        }
        return [
            'status' => $cancelAllIntinerary,
            'hotel_code' => $hotels,
            'cancelWithPenality' => $cancelWithPenality ? 'Con penalidad' : 'Sin penalidad'
        ];
    }

    public function cancelFile(int $id, int $status_reason_id = null): bool
    {

        $fileEloquent = FileEloquentModel::query()->find($id);

        $validate = $this->validateCancelFile($fileEloquent);
        if($validate['status'] == true){

            $this->modifyStatus($fileEloquent, 'XL' , $status_reason_id);
        }

        return true;
    }

    public function activateFile(int $id, int $status_reason_id): bool
    {
        $fileEloquent = FileEloquentModel::query()->find($id);

        if($fileEloquent and $fileEloquent->status == 'XL') {

            $this->modifyStatus($fileEloquent, 'OK' , $status_reason_id);
        }


        return true;
    }

    public function viewProtectedRate(int $id, bool $status = false): bool
    {
        $fileEloquent = FileEloquentModel::query()->find($id);
        dd($id);
        $fileEloquent->view_protected_rate = $status;
        $fileEloquent->save();

        return true;
    }



    public function modifyStatus(FileEloquentModel $fileEloquent, string $status, int $status_reason_id){
        
        $user_id = NULL;
        $user_code = NULL;
        if(Auth::check())
        {
             $user_id = Auth::user()->id;
             $user_code = Auth::user()->code;
        }
       
        FileStatusReasonEloquentModel::create([
            'file_id' => $fileEloquent->id,
            'status' => $status,
            'status_reason_id' => $status_reason_id,
            'user_id' => $user_id
        ]);

        $fileEloquent->status = $status;
        $fileEloquent->processing = 0;        
        $fileEloquent->save();

        // $statusReasonEloquentModel = StatusReasonEloquentModel::where('status_iso',$status)->first();
        // $status_name = '';
        // if($statusReasonEloquentModel)
        // {
        //     $status_name = $statusReasonEloquentModel->name;
        // }

        $this->send_notification_status([
            'file_number' => $fileEloquent->file_number,
            'status' => $status 
        ]);  

    }

    public function statusChanges(int $id, string $status, int $status_reason_id): bool
    {
        $fileEloquent = FileEloquentModel::query()->find($id);

        $this->modifyStatus($fileEloquent, $status, $status_reason_id);

        return true;
    }

    public function processingChanges(int $id, string $status): bool
    {    
        FileEloquentModel::where("id", $id)->update([
            'processing' => $status 
        ]);

        return true;
    }
    
    public function canceledServices(int $id): array
    {

        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries.services.compositions.units',
            'itineraries.rooms.units.nights',
            'fileStatusReason.statusReason'
        ])->find($id);

        $validate = $this->validateCancelFile($fileEloquent);

        $params = [
            'executive_code' => $fileEloquent['executive_code']
        ];
        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchByCommunication($params);
        $data = [];
        $data['file'] = $fileEloquent->file_number;
        $data['client'] = $fileEloquent->client_name;
        $data['date_in'] = $fileEloquent->date_in;
        $data['import'] = $fileEloquent->total_amount;
        $data['status'] = $validate['status'];
        $data['hotel_code'] = $validate['hotel_code'];
        $data['cancelWithPenality'] = $validate['cancelWithPenality'];
        $data['created_at'] = $fileEloquent->created_at;
        $data['executive_name'] = $response->executive->name;
        $data['executive_email'] = $response->executive->email;
        // esto esta fijo porque aun NEG no tiene el servidor activo
        $data['emails'] = [
            'clo@limatours.com.pe',
            'kpa@limatours.com.pe',
            'rvp@limatours.com.pe'
        ];
        return $data;
    }


    public function invoicedChanges(int $id, bool $status, ): bool{

        $fileEloquent = FileEloquentModel::query()->find($id);
        $fileEloquent->have_invoice = $status;
        $fileEloquent->save();

        return true;
    }

    public function inOpeFinish(int $id, int $status ): bool{

        $fileEloquent = FileEloquentModel::query()->find($id);
        $fileEloquent->revision_stages = $status;
        if($status == 2){
            $fileEloquent->ope_assign_stages = true;
        }else{
            $fileEloquent->ope_assign_stages = false;
        }

        $fileEloquent->save();

        return true;
    }


    public function searchHotelRateFilesQuery(array $params): array
    {
        $units = FileHotelRoomUnitEloquentModel::with([
            'fileHotelRoom.itinerary.file',
            'nights'
          ]
        )->whereHas('fileHotelRoom', function ($query) use ($params) {
            $query->where('file_amount_type_flag_id', '<>', 3);
            $query->where('rate_plan_id', '=', $params['rates_plans_id']);
        });
        $units = $units->get()->toArray();
        $resultRooms=[];
        foreach($units as $indexRoom => $unit){

            if(isset($params['rangos'][$unit['file_hotel_room']['room_id']]) and count($params['rangos'][$unit['file_hotel_room']['room_id']])>0){

                $ratesChanges = $params['rangos'][$unit['file_hotel_room']['room_id']];
                $dataNights= [];
                $findRates = false;
                foreach($unit['nights'] as $indexNight => $night){
                    if(isset($ratesChanges[$night['date']]) and count($ratesChanges[$night['date']])>0){

                        if($night['price_adult_cost'] != $ratesChanges[$night['date']][0]['rates']['price_adult'] or
                            $night['price_child_cost'] != $ratesChanges[$night['date']][0]['rates']['price_child'] or
                            $night['price_infant_cost'] != $ratesChanges[$night['date']][0]['rates']['price_infant'] or
                            $night['price_extra_cost'] != $ratesChanges[$night['date']][0]['rates']['price_extra']
                        ){
                            $findRates = true;
                                $night['new'] = [
                                'price_adult_cost' => $ratesChanges[$night['date']][0]['rates']['price_adult'],
                                'price_child_cost' => $ratesChanges[$night['date']][0]['rates']['price_child'],
                                'price_infant_cost' => $ratesChanges[$night['date']][0]['rates']['price_infant'],
                                'price_extra_cost' => $ratesChanges[$night['date']][0]['rates']['price_extra'],
                                'total_amount_cost' => $ratesChanges[$night['date']][0]['rates']['price_adult']
                            ];
                        }
                    }
                    array_push($dataNights, $night);
                }

                if($findRates){
                    $unit['nights'] = $dataNights;
                    $unit['open'] = false;
                    array_push($resultRooms, $unit);
                }

            }
        }

        return $resultRooms;
    }

    public function updateAmountFromAurora(array $units): array
    {

        $file_hotel_room = [];
        foreach($units as $unit){
            $unitEloquement = FileHotelRoomUnitEloquentModel::find($unit['id']);
            $amount_cost = 0;
            $file_amount_type_flag_id_night = 1; //empesamos todos los candados abiertos
            foreach($unit['nights'] as $night){
                $nightEloquement = FileHotelRoomUnitNightEloquentModel::find($night['id']);
                if($night['file_amount_type_flag_id'] == 1){ // solo si el candado es abierto actualizo importes
                    $nightEloquement->price_adult_cost = $night['new']['price_adult_cost'];
                    $nightEloquement->price_child_cost = $night['new']['price_child_cost'];
                    $nightEloquement->price_infant_cost = $night['new']['price_infant_cost'];
                    $nightEloquement->price_extra_cost = $night['new']['price_extra_cost'];
                    $nightEloquement->total_amount_cost = $night['new']['total_amount_cost'];
                    $nightEloquement->file_amount_type_flag_id = $night['file_amount_type_flag_id'];
                    $nightEloquement->save();
                    $amount_cost = $amount_cost + $night['new']['total_amount_cost'];
                }else{
                    $nightEloquement->file_amount_type_flag_id = $night['file_amount_type_flag_id'];
                    $nightEloquement->save();
                    $amount_cost = $amount_cost + $nightEloquement->total_amount_cost;
                }
                if($night['file_amount_type_flag_id'] == 2){ // basta que 1 solo en los night este bloqueado y unit tendra el cancando cerrado
                  $file_amount_type_flag_id_night = 2;
                }
            }
            $unitEloquement->amount_cost = $amount_cost;
            $unitEloquement->file_amount_type_flag_id = $file_amount_type_flag_id_night;
            $unitEloquement->save();

            $file_hotel_room[$unit['file_hotel_room_id']][] = $file_amount_type_flag_id_night;

        }

        $itineraries = [];
        foreach($file_hotel_room as  $file_hotel_room_id => $file_amount_type_flag_id_units)
        {
            $file_amount_type_flag_id = in_array(2, $file_amount_type_flag_id_units) ? 2 : 1;
            $roomEloquement = FileHotelRoomEloquentModel::with(['fileRoomAmountLogs', 'fileItinerary.file', 'units'])->find($file_hotel_room_id);


            // Preoceso, crear registro en la tabla file_room_amount_logs, en esta tabla siempre habra 1 registro y si se crea uno nuevo se elimina el que estaba activo pero necesitamos recuperar el monto que tenia
            // actualmente el servicio actualizado

            $hotelRoomAmountLog = $roomEloquement->fileRoomAmountLogs->whereNull('deleted_at')->first();
            $fileRoomAmountLogEloquentModel = FileRoomAmountLogEloquentModel::create([
                'file_amount_type_flag_id' => $file_amount_type_flag_id,
                'file_amount_reason_id' => $file_amount_type_flag_id == 1 ? 8 : 9,
                'file_hotel_room_id' => $file_hotel_room_id,
                'user_id' => 1,
                'amount_previous' => $hotelRoomAmountLog->amount,
                'amount' => $roomEloquement->units->sum('amount_cost'),
            ]);
            $hotelRoomAmountLog->delete();

            $roomEloquement->file_amount_type_flag_id = $file_amount_type_flag_id;
            $roomEloquement->amount_cost = $roomEloquement->units->sum('amount_cost');
            $roomEloquement->protected_rate = 0;  // actualizmos el campo protegido porque ya se actualizo la tarifas
            $roomEloquement->save();

            $itineraries[$roomEloquement->fileItinerary->id] = $roomEloquement->fileItinerary->file->id;
        }

        // actualizamos el campo protected_rate en el itinerario y del file
        foreach($itineraries as $file_itinerary_id => $file_id){

            // actualizamos file itineraries

            $protected_rate = 0;
            $fileItineraryEloquement = FileItineraryEloquentModel::with('rooms')->find($file_itinerary_id);
            foreach($fileItineraryEloquement->rooms as $room){
                if($room->protected_rate == 1){
                    $protected_rate = 1; // basta que 1 rooms este protegido y todo el itinerario sera protegido
                }
            }
            $fileItineraryEloquement->protected_rate = $protected_rate;
            if($protected_rate == 0){
                $fileItineraryEloquement->view_rate_protected = true;
            }

            $fileItineraryEloquement->save();

            // actualizamos file
            $protected_rate = 0;
            $fileEloquement = FileEloquentModel::with('itineraries')->find($file_id);
            foreach($fileEloquement->itineraries as $itinerary){
                if($itinerary->protected_rate == 1){
                    $protected_rate = 1; // basta que 1 rooms este protegido y todo el itinerario sera protegido
                }
            }
            $fileEloquement->protected_rate = $protected_rate;
            if($protected_rate == 0){
                $fileEloquement->view_rate_protected = true;
            }
            $fileEloquement->save();

        }

        return $itineraries;
    }

    public function searchHotelRateFilesQuery_bk_2(array $params): array
    {
        $rooms = FileHotelRoomEloquentModel::with([
            'itinerary.file',
            'units.nights',
            'fileRoomAmountLogs' => function ($query) {
                $query->with('fileAmountReason');
                $query->with('fileAmountTypeFlag');
            }
          ]
        );
        $rooms->where('file_amount_type_flag_id', '<>', 3);
        $rooms->where('rate_plan_id', '=', $params['rates_plans_id']);
        $rooms = $rooms->get()->toArray();


        $resultRooms=[];
        foreach($rooms as $indexRoom => $room){

            if(isset($params['rangos'][$room['room_id']]) and count($params['rangos'][$room['room_id']])>0){
                $ratesChanges = $params['rangos'][$room['room_id']];
                $dataUnits = [];
                foreach($room['units'] as $indexUnit =>  $unit){
                    $dataNights= [];
                    $findRates = false;
                    foreach($unit['nights'] as $indexNight => $night){
                        if(isset($ratesChanges[$night['date']]) and count($ratesChanges[$night['date']])>0){

                            if($night['price_adult_cost'] != $ratesChanges[$night['date']][0]['rates']['price_adult'] or
                               $night['price_child_cost'] != $ratesChanges[$night['date']][0]['rates']['price_child'] or
                               $night['price_infant_cost'] != $ratesChanges[$night['date']][0]['rates']['price_infant'] or
                               $night['price_extra_cost'] != $ratesChanges[$night['date']][0]['rates']['price_extra']
                            ){
                                $findRates = true;
                                    $night['old'] = [
                                    'price_adult_cost' => $night['price_adult_cost'],
                                    'price_child_cost' => $night['price_child_cost'],
                                    'price_infant_cost' => $night['price_infant_cost'],
                                    'price_extra_cost' => $night['price_extra_cost'],
                                    'total_amount_cost' => $night['total_amount_cost'],
                                ];

                                $night['price_adult_cost'] = $ratesChanges[$night['date']][0]['rates']['price_adult'];
                                $night['price_child_cost'] = $ratesChanges[$night['date']][0]['rates']['price_child'];
                                $night['price_infant_cost'] = $ratesChanges[$night['date']][0]['rates']['price_infant'];
                                $night['price_extra_cost'] = $ratesChanges[$night['date']][0]['rates']['price_extra'];
                                $night['total_amount_cost'] = $night['price_adult_cost'];
                            }
                        }
                        array_push($dataNights, $night);
                    }

                    if($findRates){
                        $unit['nights'] = $dataNights;
                        array_push($dataUnits, $unit);
                    }
                }

                if(count($dataUnits)>0){
                    $room['units'] = $dataUnits;
                    $room['open'] = false;
                    array_push($resultRooms, $room);
                }
            }
        }

        return $resultRooms;
    }

    public function searchByCommunication($params): array
    {

        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchByCommunication($params);

        return [
            'executive_name' => $response->executive->name,
            'executive_email' => $response->executive->email,
            'client_name' => $response->client->name,
            'client_nationality' => $response->client->pais,
            'client_executives' => $response->client_executives,
            'hotel_contacts' => $response->hotel_contacts
        ];


    }

    public function searchHotelRateFilesQuery_BK(array $params): array
    {
        $itineraries = FileItineraryEloquentModel::with([
            'file',
            'rooms' => function ($query) use ($params)  {
                $query->with('units.nights');
                $query->where('rate_plan_id', $params['rates_plans_id']);
            },
            'rooms.fileRoomAmountLogs' => function ($query) {
                $query->with('fileAmountReason');
                $query->with('fileAmountTypeFlag');
            }
          ]
        );
        $files = $itineraries->whereHas('rooms', function ($query) use ($params) {
            $query->where('rate_plan_id', '=', $params['rates_plans_id']);
        });
        $itineraries->where('entity', 'hotel');
        $itineraries->where('object_id', $params['hotel_id']);
        $itineraries = $itineraries->get()->toArray();

        // $results=[];
        // $findRates = false;
        // foreach($itineraries as $indexIntinerary => $itinerary){
        //     foreach($itinerary['rooms'] as $indexRoom => $room){
        //         $unitsFound = [];
        //         if(isset($params['rangos'][$room['room_id']]) and count($params['rangos'][$room['room_id']])>0){
        //             $ratesChanges = $params['rangos'][$room['room_id']];
        //             $totalUnits = 0;
        //             $dataUnits = [];
        //             foreach($room['units'] as $indexUnit =>  $unit){
        //                 $totalNights = 0;
        //                 $dataNights= [];
        //                 foreach($unit['nights'] as $indexNight => $night){
        //                     if(isset($ratesChanges[$night['date']]) and count($ratesChanges[$night['date']])>0){
        //                         $findRates = true;
        //                          $night['old'] = [
        //                             'price_adult_cost' => $night['price_adult_cost'],
        //                             'price_child_cost' => $night['price_child_cost'],
        //                             'price_infant_cost' => $night['price_infant_cost'],
        //                             'price_extra_cost' => $night['price_extra_cost'],
        //                             'total_amount_cost' => $night['total_amount_cost'],
        //                         ];

        //                         $night['price_adult_cost'] = $ratesChanges[$night['date']]['rates']['price_adult'];
        //                         $night['price_child_cost'] = $ratesChanges[$night['date']]['rates']['price_child'];
        //                         $night['price_infant_cost'] = $ratesChanges[$night['date']]['rates']['price_infant'];
        //                         $night['price_extra_cost'] = $ratesChanges[$night['date']]['rates']['price_extra'];
        //                         $night['total_amount_cost'] = $night['price_adult_cost'];
        //                     }
        //                     $totalNights = $totalNights + $night['total_amount_cost'];
        //                     array_push($dataNights, $night);
        //                 }

        //                 $unit['amount_cost'] = $totalNights;
        //                 if($findRates == true){
        //                     $unit['nights'] = $dataNights;
        //                     array_push($dataUnits, $unit);

        //                 }

        //                 $totalUnits = $totalUnits + $unit['amount_cost'];
        //             }

        //         }
        //     }
        // }

        return $itineraries;

        // return new LengthAwarePaginator(
        //     $files,
        //     $count,
        //     $perPage,
        //     $page,
        //     ['path' => request()->url(), 'query' => request()->query()]
        // );
    }


    public function searchItineraryReportByHotel(int $file_id, string $hote_code, array $params): array
    {
        // $this->update_hotels_nroite($file_id);
        $results = [];

        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms.units',
                    'services.compositions'=> function ($query) {
                        $query->with(['units','supplier']);
                    },

                ]);
            },
        // ])->findOrFail($id);
        ])->when(!empty($file_id), function ($query) use ($file_id) {
            return $query->where('id', $file_id);
        })->first();

        if(!$fileEloquent)
        {
            throw new \DomainException("file number does not exist");
        }


        foreach($fileEloquent->itineraries as $itinerary)
        {
            if($itinerary->entity == 'hotel' and $itinerary->object_code == $hote_code)
            {
                foreach($itinerary->rooms as $room)
                {
                    $units = [];
                    $rq = 0;
                    foreach($room->units as $unit)
                    {
                        $status = "";
                        if($unit->confirmation_status == 1){
                            $status = "OK";
                        }else{
                            if($unit->waiting_list == 1){
                                $status = "WL";
                            }else{
                                $status = "RQ";
                                $rq++;
                            }
                        }

                       array_push($units, [
                            'id' => $unit->id,
                            'room_name' => $room->room_name,
                            'status' => $status,
                            'waiting_reason_id' => $unit->waiting_reason_id,
                            'waiting_reason_other_message' => $unit->waiting_reason_other_message,
                            'waiting_confirmation_code' => $unit->waiting_confirmation_code,
                            'confirmation_code' => $unit->confirmation_code,
                            'file_itinerary_id'=> $itinerary->id
                       ]);
                    }

                    $status = "";
                    if($room->confirmation_status == 1){
                        $status = "OK";
                    }else{
                        if($room->waiting_list == 1){
                            $status = "WL";
                        }else{
                            $status = "RQ";
                        }
                    }

                    array_push($results, [
                        'id'=> $room->id,
                        'item_number'=> $room->item_number,
                        'object_code' => $itinerary->object_code,
                        'hotel_name' => $itinerary->name,
                        'date_in' => $itinerary->date_in,
                        'date_out' => $itinerary->date_out,
                        'city' => $itinerary->city_in_name,
                        'room_name' => $room->room_name,
                        'quantity' => $room->total_rooms,
                        'status' => $status,
                        'confirmation_code' => $room->confirmation_code,
                        'rq_total' => $rq,
                        'unit_total' => count($units),
                        'units' => $units,
                        'file_itinerary_id'=> $itinerary->id
                    ]);
                }
            }

        }

        return $results;
    }

    public function searchItineraryReport(int $file_id, array $params): array
    {
        // $this->update_hotels_nroite($file_id);

        $results = [
            'total_ws' => 0,
            'total_rq' => 0,
            'hotels' => [],
            'cruises' => [],
            'trains' => [],
            'overflights' => [],
            'tickets' => [],
            'restaurants' => [],
            'others' => []
        ];

        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms.units',
                    'services.compositions'=> function ($query) {
                        $query->with(['units','supplier']);
                    },

                ])
            ->where('status', 1);},
        // ])->findOrFail($id);
        ])->when(!empty($file_id), function ($query) use ($file_id) {
            return $query->where('id', $file_id);
        })->first();

        if(!$fileEloquent)
        {
            throw new \DomainException("file number does not exist");
        }


        foreach($fileEloquent->itineraries as $itinerary)
        {
            if($itinerary->entity == 'hotel')
            {
                foreach($itinerary->rooms as $room)
                {
                    $units = [];
                    $rq = 0;
                    foreach($room->units as $unit)
                    {
                        $status = "";
                        if($unit->confirmation_status == 1){
                            $status = "OK";
                        }else{
                            if($unit->waiting_list == 1){
                                $status = "WL";
                            }else{
                                $status = "RQ";
                                $rq++;
                            }
                        }

                       array_push($units, [
                            'id' => $unit->id,
                            'room_name' => $room->room_name,
                            'status' => $status,
                            'waiting_reason_id' => $unit->waiting_reason_id,
                            'waiting_reason_other_message' => $unit->waiting_reason_other_message,
                            'waiting_confirmation_code' => $unit->waiting_confirmation_code,
                            'confirmation_code' => $unit->confirmation_code
                       ]);
                    }

                    $status = "";
                    if($room->confirmation_status == 1){
                        $status = "OK";
                    }else{
                        if($room->waiting_list == 1){
                            $status = "WL";
                        }else{
                            $status = "RQ";
                        }
                    }

                    array_push($results['hotels'], [
                        'id'=> $room->id,
                        'file_itinerary_id' => $itinerary->id,
                        'created_at'=> $itinerary->created_at,
                        'item_number'=> $room->item_number,
                        'object_code' => $itinerary->object_code,
                        'hotel_name' => $itinerary->name,
                        'date_in' => $itinerary->date_in,
                        'date_out' => $itinerary->date_out,
                        'city' => $itinerary->city_in_name,
                        'room_name' => $room->room_name,
                        'quantity' => $room->total_rooms,
                        'status' => $status,
                        'confirmation_code' => $room->confirmation_code,
                        'rq_total' => $rq,
                        'unit_total' => count($units),
                        'units' => $units
                    ]);
                }
            }

            if($itinerary->entity == 'service')
            {
                foreach($itinerary->services as $service){
                    foreach($service->compositions as $composition)
                    {
                        if($composition->requires_confirmation != 'S'){

                            if(!isset($composition->supplier))
                            {
                                continue;
                            }

                            if($composition->supplier->reservation_for_send != "1")
                            {
                                continue;
                            }

                        }

                        $units = [];
                        $rq = 0;
                        foreach($composition->units as $unit)
                        {
                            $status = "";
                            if($unit->confirmation_status == 1){
                                $status = "OK";
                            }else{
                                if($unit->waiting_list == 1){
                                    $status = "WL";
                                }else{
                                    $status = "RQ";
                                    $rq++;
                                }
                            }

                            array_push($units, [
                                    'id' => $unit->id,
                                    'code' => $composition->code,
                                    'name' => $composition->name,
                                    'status' => $status,
                                    'waiting_reason_id' => $unit->waiting_reason_id,
                                    'waiting_reason_other_message' => $unit->waiting_reason_other_message,
                                    'waiting_confirmation_code' => $unit->waiting_confirmation_code,
                                    'confirmation_code' => $unit->confirmation_code
                            ]);
                        }

                        $status = "";
                        if($composition->confirmation_status == 1){
                            $status = "OK";
                        }else{
                            if($composition->waiting_list == 1){
                                $status = "WL";
                            }else{
                                $status = "RQ";
                            }
                        }

                        $composition_add = [
                            'id'=> $composition->id,
                            'file_itinerary_id' => $itinerary->id,
                            'created_at'=> $itinerary->created_at,
                            // 'item_number'=> $composition->item_number,
                            'code' => $composition->code,
                            'name' => $composition->name,
                            'type_service' => $composition->type_service,
                            'date_in' => $composition->date_in,
                            'date_out' => $composition->date_out,
                            'city' => $composition->city_in_name,
                            'quantity' => count($composition->units),
                            'status' => $status,
                            'waiting_confirmation_code' => $composition->waiting_confirmation_code,
                            'rq_total' => $rq,
                            'unit_total' => count($units),
                            'supplier' => $composition->supplier,
                            'send_communication' => $composition->supplier ? $composition->supplier->send_communication : 'N',
                            'total_passengers' => $composition->total_adults + $composition->total_children,
                            'start_time' => $composition->start_time,
                            'departure_time' => $composition->departure_time,
                            'confirmation_code' => $composition->confirmation_code,
                            'confirmation_code_for_tickets' => $composition->confirmation_code ? true : false,
                            'requires_confirmation' => $composition->requires_confirmation,
                            'send_notification' => $composition->send_notification,
                            'units' => $units
                        ];


                        if(in_array($composition->type_service,['PKS', 'TRN', 'AEC', 'MUS', 'RES'])){
                            if($composition->type_service == 'PKS'){
                                array_push($results['cruises'], $composition_add);
                            }

                            if($composition->type_service == 'TRN'){
                                array_push($results['trains'], $composition_add);
                            }

                            if($composition->type_service == 'AEC'){
                                array_push($results['overflights'], $composition_add);
                            }

                            if($composition->type_service == 'MUS'){
                                array_push($results['tickets'], $composition_add);
                            }

                            if($composition->type_service == 'RES'){
                                array_push($results['restaurants'], $composition_add);
                            }
                        }else{
                            array_push($results['others'], $composition_add);
                        }

                    }
                }
            }

        }

        $totals = $this->totals($results);
        $results['total_ws'] = $totals['total_ws'];
        $results['total_rq'] = $totals['total_rq'];

        return $results;
    }

    public function totals($results)
    {
        $result_total = [
            'total_ws' => 0,
            'total_rq' => 0,
        ];

        $totals = $this->total_item($results['hotels']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['cruises']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['trains']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['overflights']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['tickets']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['restaurants']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        $totals = $this->total_item($results['others']);
        $result_total['total_ws'] = $result_total['total_ws'] + $totals['totalWL'];
        $result_total['total_rq'] = $result_total['total_rq'] + $totals['totalRq'];

        return $result_total;
    }

    public function total_item($results)
    {
        $totalRq = 0;
        $totalWL = 0;
        foreach($results as $result){
            if($result['status'] == 'RQ'){
               $totalRq++;
            }
            if($result['status'] == 'WL'){
               $totalWL++;
            }
       }
       return [
            'totalRq' => $totalRq,
            'totalWL' => $totalWL
       ];
    }

    public function update_hotels_nroite(int $file_id = null, int $file_number = null)
    {
        if($file_number == null){
            $file = FileEloquentModel::find($file_id);

            if(!$file)
            {
                throw new \DomainException("file number does not exist");
            }

            $file_number = $file->file_number;


        }
        
        $stella = new ApiGatewayExternal();
        $services_ifx = $stella->search_file_services($file_number, 'all', true, [], true);

        $hotels = [];
        if (count($services_ifx) > 0) {

            foreach ($services_ifx as $service_ifx) {
                if($service_ifx->tipsvs == 'HTL'){
                    $hotels[$service_ifx->codsvs."|".$service_ifx->fecin . "|" . $service_ifx->fecout][] = $service_ifx;
                }
            }
        }

        $fileEloquent = FileEloquentModel::query()->with([
            'itineraries'=> function ($query) {
                $query->with([
                    'rooms.units',
                    'services.compositions'=> function ($query) {
                        $query->with('units');
                    }
                ]);
            },
        ])->when(!empty($file_number), function ($query) use ($file_number) {
            return $query->where('file_number', $file_number);
        })->first();

        foreach($fileEloquent->itineraries as $itinerary)
        {
            if($itinerary->entity == 'hotel')
            {

                foreach($hotels as $index =>  $hotel_rooms){
                    $data = explode("|", $index);
                    $hotel = $data[0];
                    $fecin = $data[1];
                    $fecout = $data[2];

                    if($itinerary->object_code == $hotel and $itinerary->date_in == $fecin and  $itinerary->date_out == $fecout){
                        foreach($itinerary->rooms as $room)
                        {
                            foreach($hotel_rooms as $hotel_room){
                                if($this->transliterateString($room->room_name) == $this->transliterateString($hotel_room->desbas_inicial)){

                                    foreach($room->units as $unit)
                                    {
                                        if(!$unit->confirmation_code or $unit->confirmation_code == '' or $unit->confirmation_code == "0") // solo si no tiene asociado confirmation_code procesamos a validar si enviamos actualizar stella
                                        {
                                            $unit->confirmation_status = true;
                                            $unit->waiting_list = false;
                                            $unit->confirmation_code = $hotel_room->codcfm;
                                            $unit->save();
                                        }
                                    }

                                    $room->confirmation_status = true;
                                    $room->waiting_list = false;
                                    $room->item_number = $hotel_room->nroite;
                                    $room->confirmation_code = $hotel_room->codcfm;
                                    $room->save();

                                }
                            }
                        }
                    }

                }

            }
        }


    }

    public function transliterateString($txt) {
        $txt = trim($txt);
        $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        $txt = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
        return substr(strtoupper($txt), 0, 40);
    }

    public function searchInA2DetailsService(array $params): array{

        $params = [
            'reservations' => $params
        ];
//  dd(json_encode($params));
        $aurora = new AuroraExternalApiService();
        $response = $aurora->searchServicesDetails($params);

        return (array) $response->results;

    }

}
