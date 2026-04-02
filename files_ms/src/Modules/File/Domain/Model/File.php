<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\File\Adults;
use Src\Modules\File\Domain\ValueObjects\File\Applicant;
use Src\Modules\File\Domain\ValueObjects\File\BudgetNumber;
use Src\Modules\File\Domain\ValueObjects\File\Children;
use Src\Modules\File\Domain\ValueObjects\File\ClientId;
use Src\Modules\File\Domain\ValueObjects\File\ClientCode;
use Src\Modules\File\Domain\ValueObjects\File\ClientCreditLine;
use Src\Modules\File\Domain\ValueObjects\File\ClientHaveCredit;
use Src\Modules\File\Domain\ValueObjects\File\ClientName;
use Src\Modules\File\Domain\ValueObjects\File\Currency;
use Src\Modules\File\Domain\ValueObjects\File\DateIn;
use Src\Modules\File\Domain\ValueObjects\File\DateOut;
use Src\Modules\File\Domain\ValueObjects\File\Description;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCode;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeProcess;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeSale;
use Src\Modules\File\Domain\ValueObjects\File\FileCategories; 
use Src\Modules\File\Domain\ValueObjects\File\FileCodeAgency;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Modules\File\Domain\ValueObjects\File\FileNumber;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Modules\File\Domain\ValueObjects\File\FileVips;
use Src\Modules\File\Domain\ValueObjects\File\Group;
use Src\Modules\File\Domain\ValueObjects\File\HaveInvoice;
use Src\Modules\File\Domain\ValueObjects\File\HaveQuote;
use Src\Modules\File\Domain\ValueObjects\File\HaveTicket;
use Src\Modules\File\Domain\ValueObjects\File\HaveVoucher;
use Src\Modules\File\Domain\ValueObjects\File\Infants;
use Src\Modules\File\Domain\ValueObjects\File\Lang;
use Src\Modules\File\Domain\ValueObjects\File\MarkupClient;
use Src\Modules\File\Domain\ValueObjects\File\Observation;
use Src\Modules\File\Domain\ValueObjects\File\Status;
use Src\Modules\File\Domain\ValueObjects\File\Tariff;
use Src\Modules\File\Domain\ValueObjects\File\TotalPax;
use Src\Modules\File\Domain\ValueObjects\File\UseInvoice;
use Src\Modules\File\Domain\ValueObjects\File\OrderNumber;
use Src\Modules\File\Domain\ValueObjects\File\Promotion;
use Src\Modules\File\Domain\ValueObjects\File\ReservationId;
use Src\Modules\File\Domain\ValueObjects\File\ReservationNumber;
use Src\Modules\File\Domain\ValueObjects\File\RevisionStages;
use Src\Modules\File\Domain\ValueObjects\File\OpeAssignStages;
use Src\Modules\File\Domain\ValueObjects\File\SaleType;
use Src\Modules\File\Domain\ValueObjects\File\StatusReason;
use Src\Modules\File\Domain\ValueObjects\File\SectorCode;
use Src\Modules\File\Domain\ValueObjects\File\SerieReserveId;
use Src\Modules\File\Domain\ValueObjects\File\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\File\FileItinearyServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\File\GenerateStatement;
use Src\Modules\File\Domain\ValueObjects\File\PassengerChanges;
use Src\Modules\File\Domain\ValueObjects\File\FileReasonStatementId;
use Src\Modules\File\Domain\ValueObjects\File\FileStatusReasons;
use Src\Modules\File\Domain\ValueObjects\File\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationDbl;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationSgl;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationTpl;
use Src\Modules\File\Domain\ValueObjects\File\TypeClassId;
use Src\Modules\File\Domain\ValueObjects\File\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveId;
use Src\Modules\File\Domain\ValueObjects\File\Origin;
use Src\Modules\File\Domain\ValueObjects\File\Statement;
use Src\Modules\File\Domain\ValueObjects\File\StatusReasonId;
use Src\Modules\File\Domain\ValueObjects\File\StelaProcessing;
use Src\Modules\File\Domain\ValueObjects\File\StelaProcessingError;
use Src\Modules\File\Domain\ValueObjects\File\ViewProtectedRate;
use Src\Shared\Domain\Entity;

class File extends Entity
{
    public function __construct(
        public readonly ?FileId $id,
        public readonly SerieReserveId $serieReserveId,
        public readonly ClientId $clientId,
        public readonly ClientCode $clientCode,
        public readonly ClientName $clientName,
        public readonly ClientHaveCredit $clientHaveCredit,
        public readonly ClientCreditLine $clientCreditLine,        
        public readonly ReservationId $reservationId,
        public readonly OrderNumber $orderNumber,
        public readonly FileNumber $fileNumber,
        public readonly ReservationNumber $reservationNumber,
        public readonly BudgetNumber $budgetNumber,
        public readonly SectorCode $sectorCode,
        public readonly Group $group,
        public readonly SaleType $saleType,
        public readonly Tariff $tariff,
        public readonly Currency $currency,
        public readonly RevisionStages $revisionStages,
        public readonly OpeAssignStages $opeAssignStages,   
        public readonly ExecutiveId $executiveId,      
        public readonly ExecutiveCode $executiveCode,
        public readonly ExecutiveCodeSale $executiveCodeSale,
        public readonly ExecutiveCodeProcess $executiveCodeProcess,
        public readonly Applicant $applicant,
        public readonly FileCodeAgency $fileCodeAgency,
        public readonly Description $description,
        public readonly Lang $lang,
        public readonly DateIn $dateIn,
        public readonly DateOut $dateOut,
        public readonly Adults $adults,
        public readonly Children $children,
        public readonly Infants $infants,
        public readonly UseInvoice $useInvoice,
        public readonly Observation $observation,
        public readonly TotalPax $totalPax,
        public readonly HaveQuote $haveQuote,
        public readonly HaveVoucher $haveVoucher,
        public readonly HaveTicket $haveTicket,
        public readonly HaveInvoice $haveInvoice,
        public readonly Status $status,
        public readonly StatusReason $statusReason,
        public readonly StatusReasonId $statusReasonId, 
        public readonly Promotion $promotion,
        public readonly TotalAmount $totalAmount,
        public readonly MarkupClient $markupClient,
        public readonly TypeClassId $typeClassId,   
        public readonly SuggestedAccommodationSgl $suggestedAccommodationSgl,
        public readonly SuggestedAccommodationDbl $suggestedAccommodationDbl,
        public readonly SuggestedAccommodationTpl $suggestedAccommodationTpl, 
        public readonly GenerateStatement $generateStatement,
        public readonly ProtectedRate $protectedRate,    
        public readonly ViewProtectedRate $viewProtectedRate,     
        public readonly FileReasonStatementId $fileReasonStatementId,        
        public readonly PassengerChanges $passengerChanges,  
        public readonly FileItineraries $itineraries,
        public readonly FilePassengers $passengers,
        public readonly FileVips $vips,
        public readonly FileItinearyServiceAmountLogs $serviceAmountLogs,
        public readonly FileCategories $categories,
        public readonly FileStatusReasons $fileStatusReasons,
        public readonly CreatedAt $createdAt,
        public readonly Statement $statement,       
        public readonly Origin $origin,  
        public readonly StelaProcessing $stelaProcessing,  
        public readonly StelaProcessingError $stelaProcessingError 
         
    ) {
    }

    public function addItinerary(FileItinerary $fileItinerary): void
    {
        $this->itineraries->add($fileItinerary);
    }

    public function addPassenger(FilePassenger $filePassenger): void
    {
        $this->passengers->add($filePassenger);
    }

    public function getItineraries(): ?FileItineraries
    {
        return $this->itineraries->getItineraries();
    }

    public function getVips(): ?FileVips
    {
        return $this->vips->getFileVips();
    }

    public function getPassengers(): FilePassengers
    {
        return $this->passengers->getPassengers();
    }

    public function getCategories(): FileCategories
    {
        return $this->categories->getCategories();
    }

    public function getFileStatusReasons(): FileStatusReasons
    {
        return $this->fileStatusReasons->getFileStatusReasons();
    }

    public function getTotalCostAmount(): float
    {
        $totalCost = 0;
        if(count($this->itineraries)>0) {
            foreach($this->itineraries as $itinerary) {
                foreach($itinerary->services as $service) {
                    $totalCost = $totalCost + $service->amountCost->value();
                }

                foreach($itinerary->rooms as $room) {
                    $totalCost = $totalCost + $room->amountCost->value();
                }
            }
        }
        
        return round($totalCost, 2) ;
    }

    public function profitability(): float
    {
        $import = $this->getTotalCostAmount();
        $profitability = 0;
        if($import > 0){
            $profitability = round((($this->totalAmount->value() - $import ) / $import) * 100, 2);
        }
         
        return $profitability;
    }

    public function deadline(): string
    {
        $time = strtotime(date('Y-m-d'));    
        $max = date('Y-m-d', strtotime('+7 days', $time));
        return $max;
    }

     

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'serie_reserve_id' => $this->serieReserveId->value(),
            'client_id' => $this->clientId->value(),
            'client_code' => $this->clientCode->value(),
            'client_name' => $this->clientName->value(),
            'client_have_credit' => $this->clientHaveCredit->value(),  
            'client_credit_line' => $this->clientCreditLine->value(),                      
            'reservation_id' => $this->reservationId->value(),
            'order_number' => $this->orderNumber->value(),
            'file_number' => $this->fileNumber->value(),
            'reservation_number' => $this->reservationNumber->value(),
            'budget_number' => $this->budgetNumber->value(),
            'sector_code' => $this->sectorCode->value(),
            'group' => $this->group->value(),
            'sale_type' => $this->saleType->value(),
            'tariff' => $this->tariff->value(),
            'currency' => $this->currency->value(),
            'revision_stages' => $this->revisionStages->value(),
            'ope_assign_stages' => $this->opeAssignStages->value(),                          
            'executive_id' => $this->executiveId->value(),
            'executive_code' => $this->executiveCode->value(),
            'executive_code_sale' => $this->executiveCodeSale->value(),
            'executive_code_process' => $this->executiveCodeProcess->value(),
            'applicant' => $this->applicant->value(),
            'file_code_agency' => $this->fileCodeAgency->value(),
            'description' => $this->description->value(),
            'lang' => $this->lang->value(),
            'date_in' => $this->dateIn->value(),
            'date_out' => $this->dateOut->value(),
            'adults' => $this->adults->value(),
            'children' => $this->children->value(),
            'infants' => $this->infants->value(),
            'use_invoice' => $this->useInvoice->value(),
            'observation' => $this->observation->value(),
            'total_pax' => $this->totalPax->value(),
            'have_quote' => $this->haveQuote->value(),
            'have_voucher' => $this->haveVoucher->value(),
            'have_ticket' => $this->haveTicket->value(),
            'have_invoice' => $this->haveInvoice->value(),
            'status' => $this->status->value(),
            'status_reason' => $this->statusReason->value(),
            'status_reason_id' => $this->statusReasonId->value(),
            'promotion' => $this->promotion->value(),
            'total_amount' => $this->totalAmount->value(),
            'markup_client' => $this->markupClient->value(),
            'type_class_id' => $this->typeClassId->value(),
            'suggested_accommodation_sgl' => $this->suggestedAccommodationSgl->value(),
            'suggested_accommodation_dbl' => $this->suggestedAccommodationDbl->value(),
            'suggested_accommodation_tpl' => $this->suggestedAccommodationTpl->value(),           
            'generate_statement' => $this->generateStatement->value(),
            'protected_rate' => $this->protectedRate->value(),
            'view_protected_rate' => $this->viewProtectedRate->value(),            
            'file_reason_statement_id' => $this->fileReasonStatementId->value(),
            'passenger_changes' => $this->passengerChanges->value(),
            'itineraries' => $this->itineraries->getItineraries(),
            'passengers' => $this->passengers->getPassengers(),
            'vips' => $this->vips->getFileVips(),
            'serviceAmountLogs' => $this->serviceAmountLogs->getFileItinearyServiceAmountLogs(),
            'created_at' => $this->createdAt->value(),  
            'statement' => $this->statement->value(), 
            'origin' => $this->origin->value(), 
            'stela_processing' => $this->stelaProcessing->value(), 
        ];
    }
}
