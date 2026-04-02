<?php

namespace Src\Modules\File\Domain\Model;
 
use Src\Modules\File\Domain\Model\FunctionGeneral;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\FileTemporaryMasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Code;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DocumentPurchaseOrder;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Name;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AccountingDocumentSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\BranchNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CompositionId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Currency;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DocumentSkeleton;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileClassificationId; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\ItemNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\RatePlanCode;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalExtra;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TypeComponentServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TypeCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\IsProgrammable;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Status;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Taxes;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TicketSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalServices;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseAccountingDocument;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseItinerary;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseTicket;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseVoucher;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\VoucherNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\VoucherSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DurationMinutes;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileCompositionSupplier;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileServiceUnit;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\SentToOpe;
use Src\Shared\Domain\Entity;
use Carbon\Carbon;

class FileTemporaryServiceComposition extends Entity
{

    // TODO: crear inferfas para hacer administrables los mensajes de la cancelacion $message
    public $mensajes_penalties = [
        'es' => [
            'NOT_REFOUNDABLE' => 'La tarifa no tiene reembolso por cancelaciones',            
            'MESSAGE_IN_PENALTY' => 'Puede cancelar con un cargo de USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'Puede cancelar sin penalidad hasta el [APPLY_DATE_FLAG]. Después de la fecha indicada se aplica un cargo de USD [PRICE_FLAG]',
        ],
        'en' => [
            'NOT_REFOUNDABLE' => 'Rate has no refound for cancelations',            
            'MESSAGE_IN_PENALTY' => 'Can by cancel with a penalty of USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'You have until the [APPLY_DATE_FLAG] to cancel without penalties, After that date will be charged USD [PRICE_FLAG]',
        ],
        'pt' => [
            'NOT_REFOUNDABLE' => 'Taxa não tem reembolso para cancelamentos',            
            'MESSAGE_IN_PENALTY' => 'Pode ser cancelado com uma multa de USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'Você tem até [APPLY_DATE_FLAG] para cancelar sem penalidades. Após essa data, será cobrado em USD [PRICE_FLAG]',
        ]
    ];

    public $REPACE_FLAGS = [
        '[PRICE_FLAG]',
        '[DAYS_REMAIN_FLAG]',
        '[APPLY_DATE_FLAG]',
        '[EXPIRE_DATE_FLAG]'
    ];
 
    public FunctionGeneral $functionGeneral;

    public function __construct(
        public readonly ?int $id,
        public readonly FileTemporaryMasterServiceId $fileTemporaryMasterServiceId,
        public readonly FileClassificationId $fileClassificationId,
        public readonly TypeCompositionId $typeCompositionId,
        public readonly TypeComponentServiceId $typeComponentServiceId,
        public readonly CompositionId $compositionId,
        public readonly Code $code,
        public readonly Name $name,
        public readonly ItemNumber $itemNumber,
        public readonly RatePlanCode $ratePlanCode,
        public readonly TotalAdults $totalAdults,
        public readonly TotalChildren $totalChildren,
        public readonly TotalInfants $totalInfants,
        public readonly TotalExtra $totalExtra,
        public readonly IsProgrammable $isProgrammable,
        public readonly IsInOpe $isInOpe,
        public readonly SentToOpe $sentToOpe,        
        public readonly CountryInIso $countryInIso,
        public readonly CountryInName $countryInName,
        public readonly CityInIso $cityInIso,
        public readonly CityInName $cityInName,
        public readonly CountryOutIso $countryOutIso,
        public readonly CountryOutName $countryOutName,
        public readonly CityOutIso $cityOutIso,
        public readonly CityOutName $cityOutName,
        public readonly StartTime $startTime,
        public readonly DepartureTime $departureTime,
        public readonly DateIn $dateIn,
        public readonly DateOut $dateOut,
        public readonly Currency $currency,
        public readonly AmountSale $amountSale,
        public readonly AmountCost $amountCost,
        public readonly AmountSale $amountSaleOrigin,
        public readonly AmountCost $amountCostOrigin,
        public readonly MarkupCreated $markupCreated,
        public readonly Taxes $taxes,
        public readonly TotalServices $totalServices,
        public readonly UseVoucher $useVoucher,
        public readonly UseItinerary $useItinerary,
        public readonly VoucherSent $voucherSent,
        public readonly VoucherNumber $voucherNumber,
        public readonly UseTicket $useTicket,
        public readonly UseAccountingDocument $useAccountingDocument,
        public readonly TicketSent $ticketSent,
        public readonly AccountingDocumentSent $accountingDocumentSent,
        public readonly BranchNumber $branchNumber,
        public readonly DocumentSkeleton $documentSkeleton,
        public readonly DocumentPurchaseOrder $documentPurchaseOrder,
        public readonly DurationMinutes $durationMinutes,
        public readonly Status $status,
        public readonly FileServiceUnit $units,
        public readonly FileCompositionSupplier $supplier,
        public readonly PoliciesCancellationService $policiesCancellationService,
        
                      
    ) {
        $this->functionGeneral = new FunctionGeneral();
    }

    public function getUnits(): ?FileServiceUnit
    {
        return $this->units->getUnits();
    }
    
    public function getCancellationStella($policies_cancellation): array
    {
 
        $penalidad = [
            'codigoReserva' => 0, //por definir
            'idDetalleSvs' => 0, //por definir
            'totalCosto' => 0,
            'totalIgv' => 0,
            'totalVenta' => 0,
            'tipoCancel' => 'CANCEL'
        ];

        $igv = [
            'percent' => 0,
            'total_amount' => 0,
        ];
       
    
        if($policies_cancellation and count($policies_cancellation)>0){

            $policy_cancellation = (object)$policies_cancellation;   
            // if ($this->confirmationStatus->value() != 0)
            // {         
                $penalty_price = str_replace(',', '', $policy_cancellation->penalty_price);        
                $penalty_price = round($penalty_price, 2);
                $igv = round($igv['total_amount'], 2);
    
                $penalidad['totalCosto'] =  $penalty_price;
                $penalidad['totalIgv'] = $igv;
                $penalidad['totalVenta'] = $igv + $penalty_price;
                $penalidad['tipoCancel'] = 'GASCAN';
                
            // }

        }
 
        return $penalidad ;
    }

    public function getPenalty(): array
    {
        
        $selected_policies_cancelation = json_decode($this->policiesCancellationService->value(), true); 
        $selected_policies_cancelation = $this->getCancellationPoliticies($selected_policies_cancelation); 
        $penality = [
            // 'penality_price' => 0,
            // 'message' => '',
            'penality_cost' => 0,
            'penality_sale' => 0, 
            'message' => ''
        ];
        // if(count($selected_policies_cancelation)>0){

        //     $current_date = Carbon::now('America/Lima')->format('Y-m-d H:i:s');
        //     $current_date = Carbon::createFromFormat('Y-m-d H:i:s', $current_date, 'America/Lima'); 
    
        //     $total_amount_rate = $this->amountCost->value();
        //     $quantity_persons = $this->totalAdults->value() + $this->totalChildren->value();
            
        //     $startTime = $this->startTime->value() ? $this->startTime->value() : date('H:i:d') ;
        //     $service_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $this->dateIn->value().' '.$startTime, 'America/Lima');

        //     $policies_cancelation = $this->calculateCancellationPoliciesServices($current_date,$total_amount_rate,$selected_policies_cancelation,$quantity_persons,$service_start_time);
    
        //     // $penality = $this->getCancellationStella($policies_cancelation);

        //     $penality['penality_price'] = $policies_cancelation['penalty_price'];
        //     $penality['message'] = $policies_cancelation['message'];                
        // }


        return $penality;

    }

    public function calculateCancellationPoliciesServices(
        Carbon $current_date, 
        float $total_amount_rate,
        $selected_policies_cancelation,
        $quantity_persons,
        Carbon $service_start_time
    ): array
    {            
        $apply_politics = collect();
        $selected_policy_cancelation = $this->getCancellationPolicyByTypeFitService($selected_policies_cancelation,
            $quantity_persons);
        $argsPenalty = collect($selected_policy_cancelation);
       
        $argsPenalty = $argsPenalty->map(function ($penalty) use ($current_date, $service_start_time) {
            if ($penalty["unit_duration"] === 1) { //Horas
                $days_before_check_in = $this->functionGeneral->difDateHours($current_date, $service_start_time);
                if (($penalty["from"] <= (int)$days_before_check_in and $penalty["to"] >= (int)$days_before_check_in)) {
                    $penalty['days_remain'] = $days_before_check_in;
                    $penalty['days_min_policy'] = $service_start_time->subHours($penalty["from"]);                     
                    return $penalty;
                }

            } elseif ($penalty["unit_duration"] === 2) { //Dias
                $days_before_check_in = $this->functionGeneral->difDateDays($current_date, $service_start_time);  
                if (($penalty["from"] <= (int)$days_before_check_in and $penalty["to"] >= (int)$days_before_check_in)) {
                    $penalty['days_remain'] = $days_before_check_in;
                    $penalty['days_min_policy'] = $service_start_time->subDays($penalty["from"]);  
                    return $penalty;
                }
            }
        });



        $penalty_price = 0;
        $apply_date = $service_start_time;
        $expire_date = $service_start_time;
        foreach ($argsPenalty as $policies_rate) {
            if(!$policies_rate)continue;
 
            $penalty_code = strtoupper($policies_rate["penalty_name"]);
            switch ($penalty_code) {
                case "PAX":
                    $penalty_price = $this->functionGeneral->priceRound($total_amount_rate); 
                    break;
                case "PERCENTAGE":
                    $penalty_price = $this->functionGeneral->pricePercent($total_amount_rate, $policies_rate["amount"]);
                    break;
                case "AMOUNT":
                    $penalty_price = $policies_rate["amount"];
                    break;
            }

            if($penalty_price>0){
                $message = $this->getMassageInPenaltyService($penalty_price, $policies_rate['days_remain']);
            }else{ 
                $message = $this->getMassagePenaltyService($total_amount_rate, $policies_rate['days_remain'], $policies_rate['days_min_policy']);
            }
            

            $apply_politics->add(collect([
                'from' => $policies_rate["from"],
                'to' => $policies_rate["to"],
                'min_num' => $policies_rate["min_num"],
                'max_num' => $policies_rate["max_num"],
                'unit_duration' => ($policies_rate["unit_duration"] === 1) ? 'hour' : 'day',
                'apply_date' => $policies_rate['days_min_policy']->format('d-m-Y H:i'),
                'expire_date' => $policies_rate['days_min_policy']->format('d-m-Y H:i'),
                'days_remain' => $policies_rate['days_remain'],
                'penalty_price' => $this->functionGeneral->priceRound($penalty_price),
                'penalty_code' => $penalty_code,
                'message' => $message,
            ]));

        }

        if ($apply_politics->count() == 0) {
            $apply_politics->add(collect([
                'from' => 0,
                'to' => 0,
                'min_num' => 0,
                'max_num' => 0,
                'unit_duration' => 0,
                'apply_date' => $current_date->format('d-m-Y'),
                'expire_date' => $expire_date->format('d-m-Y'),
                'days_remain' => 0,
                'penalty_price' => $this->functionGeneral->priceRound($total_amount_rate),
                'penalty_code' => 'NOT',
                'message' => $this->getMassageNotRefoundable($total_amount_rate, 0, $current_date->format('d-m-Y'), $service_start_time->format('d-m-Y')),
            ]));
        }

        return $apply_politics->values()->first()->toArray(); 
    }

    public function getCancellationPolicyByTypeFitService($selected_policies_cancelation, $quantity_persons)
    { 
        // se le da prioridad a los tipo pasajero
        $selected_policy_cancelation = $selected_policies_cancelation->filter(function ($item, $key) use (
            $quantity_persons
        ) {
            // type_fit == 1 | PAX
            return ($item['min_num'] <= $quantity_persons and $item['max_num'] >= $quantity_persons);
        });

        // si no se encuentra una cancelacion tipo pasajeros se devuelve todas
        if ($selected_policy_cancelation->count() == 0) {
            $selected_policy_cancelation = $selected_policies_cancelation;
        }

        return $selected_policy_cancelation;
    }

    public function getMassagePenaltyService($penalty_price, $days_remain, $apply_date, $expire_date = '')
    {
        $lang_iso = \Config::get('app.locale');
        
        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_BEFORE_PENALTY']
        );
    }


    public function getMassageInPenaltyService($penalty_price, $days_remain)
    {
        $lang_iso = \Config::get('app.locale');
        
        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_IN_PENALTY']
        );
    }

    /**
     * @param $penalty_price
     * @param $days_remain
     * @param $apply_date
     * @param $expire_date
     * @return mixed
     */
    public function getMassageNotRefoundable($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');
        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['NOT_REFOUNDABLE']
        );
    }    

    public function getCancellationPoliticies($selected_policies_cancelation){
    
        $details_params_cancelations = [];
        if(isset($selected_policies_cancelation)){
            foreach ($selected_policies_cancelation as $item) {
                $details_params_cancelations[] = [ 
                    'unit_duration' => $item['unit_duration'],
                    'min_num' => $item['min_pax'],
                    'max_num' => $item['max_pax'],
                    'to' => $item['max_hour'],
                    'from' => $item['min_hour'],
                    'tax' => $item['tax'],
                    'amount' => $item['amount'],
                    'service' => $item['service'],
                    'penalty_id' => $item['penalty_id'],
                    'penalty_name' => $item['penalty_name'],
                ];
            }
        }
        return collect($details_params_cancelations);
    }




    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_temporary_master_service_id' => $this->fileTemporaryMasterServiceId->value(),
            'file_classification_id' => $this->fileClassificationId->value(),
            'type_composition_id' => $this->typeCompositionId->value(),
            'type_component_service_id' => $this->typeComponentServiceId->value(),
            'composition_id' => $this->compositionId->value(),
            'code' => $this->code->value(),
            'name' => $this->name->value(),
            'item_number' => $this->itemNumber->value(),
            'rate_plan_code' => $this->ratePlanCode->value(),
            'total_adults' => $this->totalAdults->value(),
            'total_children' => $this->totalChildren->value(),
            'total_infants' => $this->totalInfants->value(),
            'total_extra' => $this->totalExtra->value(),
            'is_programmable' => $this->isProgrammable->value(),
            'is_in_ope' => $this->isInOpe->value(),
            'sent_to_ope' => $this->sentToOpe->value(),            
            'country_in_iso' => $this->countryInIso->value(),
            'country_in_name' => $this->countryInName->value(),
            'city_in_iso' => $this->cityInIso->value(),
            'city_in_name' => $this->cityInName->value(),
            'country_out_iso' => $this->countryOutIso->value(),
            'country_out_name' => $this->countryOutName->value(),
            'city_out_iso' => $this->cityOutIso->value(),
            'city_out_name' => $this->cityOutName->value(),
            'start_time' => $this->startTime->value(),
            'departure_time' => $this->departureTime->value(),
            'date_in' => $this->dateIn->value(),
            'date_out' => $this->dateOut->value(),
            'currency' => $this->currency->value(),
            'amount_sale' => $this->amountSale->value(),
            'amount_cost' => $this->amountCost->value(),
            'amount_sale_origin' => $this->amountSaleOrigin->value(),
            'amount_cost_origin' => $this->amountCostOrigin->value(),
            'markup_created' => $this->markupCreated->value(),
            'taxes' => $this->taxes->value(),
            'total_services' => $this->totalServices->value(),
            'use_voucher' => $this->useVoucher->value(),
            'use_itinerary' => $this->useItinerary->value(),
            'voucher_sent' => $this->voucherSent->value(),
            'voucher_number' => $this->voucherNumber->value(),
            'use_ticket' => $this->useTicket->value(),
            'use_accounting_document' => $this->useAccountingDocument->value(),
            'ticket_sent' => $this->ticketSent->value(),
            'account_document_sent' => $this->accountingDocumentSent->value(),
            'branch_number' => $this->branchNumber->value(),
            'document_skeleton' => $this->documentSkeleton->value(),
            'document_purchase_order' => $this->documentPurchaseOrder->value(),
            'duration_minutes' => $this->durationMinutes->value(),
            'status' => $this->status->value(),
            'units' => $this->units->jsonSerialize(),
            'supplier' => $this->supplier->jsonSerialize(),
            'policies_cancellation_service' => $this->policiesCancellationService->jsonSerialize(),
        ];
    }

}
