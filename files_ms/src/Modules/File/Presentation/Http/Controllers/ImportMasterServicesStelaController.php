<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use File;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementDetailsQuery;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileClassificationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\TypeComponentServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\TypeCompositionEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileServicesCancellationStellaJob;
use Src\Modules\File\Presentation\Http\Traits\CalculateProfitability;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileCreateStatementJob;

class ImportMasterServicesStelaController extends Controller
{
    use ApiResponse;
    use CalculateProfitability;

    private string $fileId;
    private string $formatDate = 'd/m/Y';
    private array $equivalences;

    public function __invoke(Request $request, $file_id)
    {

        $file = (new FindFileByIdAllQuery($file_id))->handle();
        $fileItineraries = $file['itineraries'];
        $equivalences = [];

        if (count($fileItineraries) > 0) {

            foreach ($fileItineraries as $itinerary) {

                if ($itinerary['entity'] === 'service' and count($itinerary['services']) === 0) {

                    $totalAdults = $itinerary['total_adults'];
                    $totalChildren = $itinerary['total_children'];
                    $totalInfants = $itinerary['total_infants'];
                    $totalPassengers = $totalAdults + $totalChildren + $totalInfants;
                    $equivalences[] = [
                        "code" => $itinerary['object_code'],
                        "date_in" => Carbon::parse($itinerary['date_in'])->format('d/m/Y'),
                        "total_passengers" => $totalPassengers,
                        "total_children" => $totalChildren,
                        "start_time" => $itinerary['start_time'],
                    ];
                }
            }

            if(count($equivalences)>0){
 
                $auroraWS = new ApiGatewayExternal();
                $masterServices = $auroraWS->getMasterServices($this->equivalences);
                $fileItineraries = FileItineraryEloquentModel::with('file')->where('file_id', $this->fileId)
                    ->where('entity', 'service')
                    ->get();

                DB::transaction(function () use ($masterServices, $fileItineraries) {
                    $this->processMasterServices($masterServices, $fileItineraries);
                    $this->updatePassengerServices($fileItineraries);
                    $this->updateAmountItinerary();
                    $this->updateTypeRoomPassenger();
                    // $this->updateHourServices();
                });

                ProcessFileCreateStatementJob::dispatch($file_id)->onConnection('database');
            }
        }

        return $this->successResponse(ResponseAlias::HTTP_OK, [
            'processed_services' => $equivalences
        ]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateTypeRoomPassenger(): bool
    {
        $filePassengerEloquent = FilePassengerEloquentModel::query()->where('file_id', $this->fileId)->get();
        foreach($filePassengerEloquent as $filePassenger){

            $cost_by_passenger = 0;
            $roomAccommodationAll = FileRoomAccommodationEloquentModel::query()->with(['fileHotelRoomUnit'])->where('file_passenger_id', $filePassenger->id)->get();

            foreach($roomAccommodationAll as $roomAccommod){
                $fileHotelRoomUnit = $roomAccommod->fileHotelRoomUnit;
                $amount_cost = $fileHotelRoomUnit->amount_cost;
                $totalPax = $fileHotelRoomUnit->adult_num + $fileHotelRoomUnit->child_num;
                $imporByPax = $amount_cost/$totalPax;

                $cost_by_passenger = $cost_by_passenger + $imporByPax;
            }

            $serviceAccomodation = FileServiceAccommodationEloquentModel::query()->with(['file_service_unit.composition'])->where('file_passenger_id', $filePassenger->id)->get();
            foreach($serviceAccomodation as $serviceAccomoda){
                $fileServiceUnit = $serviceAccomoda->file_service_unit;
                if($fileServiceUnit){
                    $amount_cost = $fileServiceUnit->amount_cost;
                    $totalPax = 0;
                    $imporByPax = 0;
                    if($fileServiceUnit->composition){
                       $totalPax = $fileServiceUnit->composition->total_adults + $fileServiceUnit->composition->total_children;
                       $imporByPax = $amount_cost/$totalPax;
                    }


                    $cost_by_passenger = $cost_by_passenger + $imporByPax;
                }
            }

            FilePassengerEloquentModel::where("id", $filePassenger->id)->update([
                'cost_by_passenger' => $cost_by_passenger
            ]);
        }

        return true;
    }


    private function updateAmountItinerary(): void
    {
        $fileItineraries = FileItineraryEloquentModel::query()->with([
            'services'
        ])->where('file_id', $this->fileId)->where('entity', 'service')->get();

        $fileItineraries->each(function ($fileItineraryEloquent){

            $fileItineraryEloquent->total_cost_amount = $fileItineraryEloquent->services->sum('amount_cost');
            $fileItineraryEloquent->profitability = $fileItineraryEloquent->calculateProfitability();
            $fileItineraryEloquent->save();

        });

        $fileEloquent = FileEloquentModel::query()->findOrFail($this->fileId);
        $fileEloquent->total_amount = $fileEloquent->itineraries->sum('total_amount');
        $fileEloquent->save();
    }

    private function updatePassengerServices($fileItineraries): void
    {

        $fileItineraries = FileItineraryEloquentModel::query()->with([
            'services.compositions.units.accommodations'
        ])->where('file_id', $this->fileId)->where('entity', 'service')->get();

        $filePassengers = FilePassengerEloquentModel::query()->where('file_id', $this->fileId)->get();

        $fileItineraries->each(function ($fileItineraryEloquent) use ($filePassengers){

            $dataPassengers =  json_decode($fileItineraryEloquent->data_passengers);
            $dataPassengers = is_array($dataPassengers) ? $dataPassengers : [];

            if(count($dataPassengers) == 0){

                foreach($filePassengers as $filePassenger){
                    array_push($dataPassengers, [
                        'reservation_passenger_id' => $filePassenger->id,
                        'sequence_number' => $filePassenger->sequence_number
                    ]);
                }
            }

            $fileItineraryEloquent->services->each(function ($service) use ($dataPassengers, $filePassengers) {

                $service->compositions->each(function ($composition) use ($dataPassengers, $filePassengers) {

                    $composition->units->each(function ($unit) use ($dataPassengers, $filePassengers) {

                        foreach($dataPassengers as $passengers){

                            if(isset($passengers->sequence_number)){
                                $reservationPassenger = $filePassengers->filter(function ($value, $key) use ($passengers) {
                                    return $value['sequence_number'] == $passengers->sequence_number;
                                })->first();
                            }

                            if(isset($reservationPassenger)){
                               $fileServiceAccommodation = new FileServiceAccommodationEloquentModel();
                               $fileServiceAccommodation->file_service_unit_id = $unit->id;
                               $fileServiceAccommodation->file_passenger_id = $reservationPassenger->id;
                               $fileServiceAccommodation->save();
                            }
                        }
                    });
                });
            });
        });

    }

    private function updateHourServices(): void
    {

        $fileItineraries = FileItineraryEloquentModel::query()->with([
            'services.compositions'
        ])->where('file_id', $this->fileId)->where('entity', 'service')->get();


        $fileItineraries->each(function ($fileItineraryEloquent){

            $fileItinerarieStarTime = $fileItineraryEloquent->start_time;
            $fileItinerarie_start_time = strtotime($fileItinerarieStarTime);

            $fileItineraryEloquent->services->each(function ($service, $s) use ($fileItinerarieStarTime, $fileItinerarie_start_time) {

                $service->compositions->each(function ($composition, $c) use ($fileItinerarieStarTime, $fileItinerarie_start_time) {

                    $departureTimeComposition = date("H:i", strtotime("+{$composition->duration_minutes} minutes", $fileItinerarie_start_time));

                    $composition->start_time = $fileItinerarieStarTime;
                    $composition->departure_time = $departureTimeComposition ;
                    $composition->save();

                });

                $service->start_time = $fileItinerarieStarTime;
                $service->departure_time = $service->compositions->max('departure_time');
                $service->save();

                return $service;
            });

            $fileItineraryEloquent->start_time = $fileItinerarieStarTime;
            $fileItineraryEloquent->departure_time = $fileItineraryEloquent->services->max('departure_time');
            $fileItineraryEloquent->save();

        });

    }

    private function processMasterServices($masterServices, $fileItineraries): void
    {
        foreach ($masterServices as $masterService) {
            $this->processMasterService($masterService, $fileItineraries);
        }
    }

    private function parseDate($date) {
        if(strpos($date, '-'))
        {
            return Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y');
        }

        return $date;
    }

    private function processMasterService($masterService, $fileItineraries): void
    {
        $serviceEquivalence = $fileItineraries->first(function ($value) use ($masterService) {
            $date_in = $this->parseDate($masterService->date_in);

            $dateIn = Carbon::createFromFormat($this->formatDate, $date_in)->format('Y-m-d');
            return $value->object_code == $masterService->code && $value->date_in == $dateIn;
        });

        if ($serviceEquivalence) {
            $this->saveMasterService($masterService, $serviceEquivalence);
        }
    }

    private function saveMasterService($masterService, $serviceEquivalence): void
    {
        foreach ($masterService->master_services as $service) {

            $date_in = $this->parseDate($service->date_in);
            $date_out = $this->parseDate($service->date_out);

            $dateIn = Carbon::createFromFormat($this->formatDate, $date_in)->format('Y-m-d');
            $dateOut = Carbon::createFromFormat($this->formatDate, $date_out)->format('Y-m-d');

            $newMasterService = new FileServiceEloquentModel();
            $newMasterService->file_itinerary_id = $serviceEquivalence->id;
            $newMasterService->master_service_id = $service->master_service_id;
            $newMasterService->name = trim($service->name);
            $newMasterService->code = trim($service->code);
            $newMasterService->type_ifx = trim($service->type_ifx);
            $newMasterService->status = true;
            $newMasterService->confirmation_status = true;
            $newMasterService->date_in = $dateIn;
            $newMasterService->date_out = $dateOut;
            $startTime = trim($service->start_time) == ":" ? null : trim($service->start_time);
            $departureTime = trim($service->departure_time) == ":" ? null : trim($service->departure_time);
            $newMasterService->start_time = $startTime;
            $newMasterService->departure_time = $departureTime;
            $newMasterService->amount_cost = $service->amount_cost ? $service->amount_cost : 0;
            $newMasterService->is_in_ope = 0;
            $newMasterService->sent_to_ope = 0;
            $newMasterService->type_service = trim($service->type_service);
            $newMasterService->save();

            $this->saveDefaultFileServiceAmountLog($newMasterService->id, $newMasterService->amount_cost);

            foreach ($service->components as $component) {
                $this->saveComponent($newMasterService, $component, $serviceEquivalence);
            }
        }

    }

    public function saveDefaultFileServiceAmountLog($newMasterServiceId, $newMasterServiceAmount): void
    {
        $neFileServiceAmountLog = new FileServiceAmountLogEloquentModel();
        $neFileServiceAmountLog->file_amount_type_flag_id = 1;
        $neFileServiceAmountLog->file_amount_reason_id = 8;
        $neFileServiceAmountLog->file_service_id = $newMasterServiceId;
        $neFileServiceAmountLog->user_id = 1;
        $neFileServiceAmountLog->amount_previous = 0;
        $neFileServiceAmountLog->amount = $newMasterServiceAmount ? $newMasterServiceAmount: 0;
        $neFileServiceAmountLog->save();
    }

    private function saveComponent($newMasterService, $component, $serviceEquivalence): void
    {

        $date_in = $this->parseDate($component->date_in);
        $date_out = $this->parseDate($component->date_out);

        $dateIn = Carbon::createFromFormat($this->formatDate, $date_in)->format('Y-m-d');
        $dateOut = Carbon::createFromFormat($this->formatDate, $date_out)->format('Y-m-d');

        $classification = FileClassificationEloquentModel::where('iso', 'S')->first(['id']);
        $typeComposition = TypeCompositionEloquentModel::where('code', '3')->first(['id']);
        $typeComponentService = TypeComponentServiceEloquentModel::where('code', 'SVS')->first(['id']);

        $newComponent = new FileServiceCompositionEloquentModel();
        $newComponent->file_service_id = $newMasterService->id;
        $newComponent->file_classification_id = $classification->id;
        $newComponent->type_composition_id = $typeComposition->id;
        $newComponent->type_component_service_id = $typeComponentService->id;
        $newComponent->composition_id = $component->composition_id;
        $newComponent->code = $component->code;
        $newComponent->name = $component->name;
        $newComponent->item_number = 1;
        $newComponent->duration_minutes = $component->duration_minutes;
        $newComponent->rate_plan_code = $component->rate_plan_code;
        $newComponent->total_adults = $serviceEquivalence->total_adults;
        $newComponent->total_children = $serviceEquivalence->total_children;
        $newComponent->total_infants = $serviceEquivalence->total_infants;
        $newComponent->total_extra = 0;
        $newComponent->is_programmable = $component->is_programmable;
        $newComponent->is_in_ope = 0;
        $newComponent->sent_to_ope = 0;
        $newComponent->country_in_iso = $component->country_in_iso;
        $newComponent->country_in_name = $component->country_in_name;
        $newComponent->city_in_iso = $component->city_in_iso;
        $newComponent->city_in_name = $component->city_in_name;
        $newComponent->country_out_iso = $component->country_out_iso;
        $newComponent->country_out_name = $component->country_out_name;
        $newComponent->city_out_iso = $component->city_out_iso;
        $newComponent->city_out_name = $component->city_out_name;
        $startTime = trim($component->start_time) == ':' ? null : trim($component->start_time);
        $departureTime = trim($component->departure_time) == ':' ? null : trim($component->departure_time);
        $newComponent->start_time = $startTime;
        $newComponent->departure_time = $departureTime;
        $newComponent->date_in = $dateIn;
        $newComponent->date_out = $dateOut;
        $newComponent->currency = $component->currency;
        $newComponent->amount_sale = $component->amount_sale;
        $newComponent->amount_cost = $component->amount_cost;
        $newComponent->amount_sale_origin = $component->amount_sale_origin;
        $newComponent->amount_cost_origin = $component->amount_cost_origin;
        $newComponent->markup_created = $serviceEquivalence->markup_created;
        $newComponent->taxes = $component->taxes;
        $newComponent->total_services = $component->total_services;
        $newComponent->use_voucher = $component->use_voucher;
        $newComponent->use_itinerary = $component->use_itinerary;
        $newComponent->voucher_sent = $component->use_itinerary;
        $newComponent->voucher_number = null;
        $newComponent->use_ticket = $component->use_ticket;
        $newComponent->use_accounting_document = $component->use_accounting_document;
        $newComponent->ticket_sent = 0;
        $newComponent->accounting_document_sent = $component->accounting_document_sent;
        $newComponent->branch_number = null;
        $newComponent->document_skeleton = $component->document_skeleton;
        $newComponent->document_purchase_order = $component->document_purchase_order;
        $newComponent->status = 1;
        $newComponent->confirmation_status = true;
        $newComponent->waiting_list = false;
        $newComponent->type_service = trim($component->type_service);
        $newComponent->requires_confirmation = trim($component->requires_confirmation);
        $newComponent->save();
        $this->saveServiceUnit($newComponent, $component);
        $this->saveSupplier($newComponent, $component, $serviceEquivalence);

    }

    public function saveServiceUnit($newComponent, $component): void
    {
        $newServiceUnit = new FileServiceUnitEloquentModel();
        $newServiceUnit->file_service_composition_id = $newComponent->id;
        $newServiceUnit->status = 1;
        $newServiceUnit->amount_sale = $component->amount_sale;
        $newServiceUnit->amount_cost = $component->amount_cost;
        $newServiceUnit->amount_sale_origin = $component->amount_sale_origin;
        $newServiceUnit->amount_cost_origin = $component->amount_cost_origin;
        $newServiceUnit->confirmation_status = true;
        $newServiceUnit->waiting_list = false;
        $newServiceUnit->save();
    }

    public function saveSupplier($newComponent, $component, $serviceEquivalence): void
    {
        ProcessFileServicesCancellationStellaJob::dispatch($newComponent, $component, $serviceEquivalence)->onConnection('database');
    }

}
