<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
// use Src\Modules\File\Domain\Model\FileCompositionSupplier;
use Src\Modules\File\Domain\Model\FileServiceComposition;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileCompositionSupplier;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\AccountingDocumentSent;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Code;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Name;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\BranchNumber;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\CompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Currency;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DocumentPurchaseOrder;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DocumentSkeleton;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileClassificationId;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceId;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\IsProgrammable;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\ItemNumber;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\RatePlanCode;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Status;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Taxes;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TicketSent;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TotalExtra;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TotalServices;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TypeComponentServiceId;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\TypeCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\UseAccountingDocument;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\UseItinerary;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\UseTicket;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\UseVoucher;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\VoucherNumber;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\VoucherSent;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\DurationMinutes;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileServiceUnit;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\Markup;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\PoliciesCancellationService;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use stdClass;

class FileServiceCompositionMapper
{
    public static function fromRequestCreate(array $composition, int $file_service_id = NULL): FileServiceComposition
    {
        $composition_id = $composition['composition_id'] ? $composition['composition_id'] : 0;
        $file_classification_id = $composition['file_classification_id'] ? $composition['file_classification_id'] : 0;
        $type_composition_id = $composition['type_composition_id'] ? $composition['type_composition_id'] : 0;
        $type_component_service_id = $composition['type_component_service_id'] ? $composition['type_component_service_id']: 0;
        $file_service_id = $file_service_id;
        $item_number = $composition['item_number'] ? $composition['item_number'] : 0;
        $code = $composition['code'] ? $composition['code'] : null;
        $name = $composition['name'] ? $composition['name'] : null;
        $rate_plan_code = $composition['rate_plan_code'] ? $composition['rate_plan_code'] : null;
        $total_adults = $composition['total_adults'] ? $composition['total_adults'] : 0;
        $total_children = $composition['total_children'] ? $composition['total_children'] : 0;
        $total_infants = $composition['total_infants'] ? $composition['total_infants'] : 0;
        $total_extra = $composition['total_extra'] ? $composition['total_extra'] : 0;
        $is_programmable = $composition['is_programmable'] ? $composition['is_programmable'] : 0;
        $is_in_ope = $composition['is_in_ope'] ? $composition['is_in_ope'] : 0;
        $sent_to_ope = isset($composition['sent_to_ope']) ? $composition['sent_to_ope'] : 0;        
        $country_in_iso = $composition['country_in_iso'] ? $composition['country_in_iso'] : null;
        $country_in_name = $composition['country_in_name'] ? $composition['country_in_name'] : null;
        $city_in_iso = $composition['city_in_iso'] ? $composition['city_in_iso'] : null;
        $city_in_name = $composition['city_in_name'] ? $composition['city_in_name'] : null;
        $country_out_iso = $composition['country_out_iso'] ? $composition['country_out_iso'] : null;
        $country_out_name = $composition['country_out_name'] ? $composition['country_out_name'] : null;
        $city_out_iso = $composition['city_out_iso'] ? $composition['city_out_iso'] : null;
        $city_out_name = $composition['city_out_name'] ? $composition['city_out_name'] : null;
        $start_time = $composition['start_time'] ? $composition['start_time'] : null;
        $departure_time = $composition['departure_time'] ? $composition['departure_time'] : null;
        $date_in = $composition['date_in'];
        $date_out = $composition['date_out'];
        $currency = $composition['currency'] ? $composition['currency']  : 'USD';
        $amount_sale = $composition['amount_sale'] ? $composition['amount_sale'] : 0 ;
        $amount_cost = $composition['amount_cost'] ? $composition['amount_cost'] : 0;
        $amount_sale_origin = $composition['amount_sale_origin'] ? $composition['amount_sale_origin'] : 0;
        $amount_cost_origin = $composition['amount_cost_origin'] ? $composition['amount_cost_origin'] : 0;
        $markup_created = $composition['markup_created'] ? $composition['markup_created'] : 0;
        $taxes = $composition['taxes'] ? $composition['taxes'] : 0;
        $total_services = $composition['total_services'] ? $composition['total_services'] : 0;
        $use_voucher = $composition['use_voucher'] ? $composition['use_voucher'] : 0;
        $use_itinerary = $composition['use_itinerary'] ? $composition['use_itinerary'] : 0;
        $voucher_sent = $composition['voucher_sent'] ? $composition['voucher_sent'] : 0;
        $voucher_number = $composition['voucher_number'] ? $composition['voucher_number'] : null;
        $use_ticket = $composition['use_ticket'] ? $composition['use_ticket'] : 0;
        $use_accounting_document = $composition['use_accounting_document'] ? $composition['use_accounting_document'] : 0;
        $ticket_sent = $composition['ticket_sent'] ? $composition['ticket_sent'] : 0;
        $accounting_document_sent = $composition['accounting_document_sent'] ? $composition['accounting_document_sent'] : null;
        $branch_number = $composition['branch_number'] ? $composition['branch_number'] : null;
        $document_skeleton = $composition['document_skeleton'] ? $composition['document_skeleton'] : 0;
        $document_purchase_order = $composition['document_purchase_order'] ? $composition['document_purchase_order'] : 0; 
        $duration_minutes = $composition['duration_minutes'] ? $composition['duration_minutes'] : 0;
        $status = $composition['status'] ? $composition['status'] :  1;
        $units_array = $composition['units'] ? $composition['units'] :  [];

        $units = array_map(function ($unit) {
            return FileServiceUnitMapper::fromRequestCreate($unit);
        }, $units_array);
 
        return new FileServiceComposition(
            id: NULL,
            fileServiceId: new FileServiceId($file_service_id),
            fileClassificationId: new FileClassificationId($file_classification_id),
            typeCompositionId: new typeCompositionId($type_composition_id),
            typeComponentServiceId: new TypeComponentServiceId($type_component_service_id),
            compositionId: new CompositionId($composition_id),
            code: new Code($code),
            name: new Name($name),
            itemNumber: new ItemNumber($item_number),
            ratePlanCode: new RatePlanCode($rate_plan_code),
            totalAdults: new TotalAdults($total_adults),
            totalChildren: new TotalChildren($total_children),
            totalInfants: new TotalInfants($total_infants),
            totalExtra: new TotalExtra($total_extra),
            isProgrammable: new IsProgrammable($is_programmable),
            isInOpe: new IsInOpe($is_in_ope),
            sentToOpe: new SentToOpe($sent_to_ope), 
            countryInIso: new CountryInIso($country_in_iso),
            countryInName: new CountryInName($country_in_name),
            cityInIso: new CityInIso($city_in_iso),
            cityInName: new CityInName($city_in_name),
            countryOutIso: new CountryOutIso($country_out_iso),
            countryOutName: new CountryOutName($country_out_name),
            cityOutIso: new CityOutIso($city_out_iso),
            cityOutName: new CityOutName($city_out_name),
            startTime: new StartTime($start_time),
            departureTime: new DepartureTime($departure_time),
            dateIn: new DateIn($date_in),
            dateOut: new DateOut($date_out),
            currency: new Currency($currency),
            amountSale: new AmountSale($amount_sale),
            amountCost: new AmountCost($amount_cost),
            amountSaleOrigin: new AmountSale($amount_sale_origin),
            amountCostOrigin: new AmountCost($amount_cost_origin),
            markupCreated: new MarkupCreated($markup_created),
            taxes: new Taxes($taxes),
            totalServices: new TotalServices($total_services),
            useVoucher: new UseVoucher($use_voucher),
            useItinerary: new UseItinerary($use_itinerary),
            voucherSent: new VoucherSent($voucher_sent),
            voucherNumber: new VoucherNumber($voucher_number),
            useTicket: new UseTicket($use_ticket),
            useAccountingDocument: new UseAccountingDocument($use_accounting_document),
            ticketSent: new TicketSent($ticket_sent),
            accountingDocumentSent: new AccountingDocumentSent($accounting_document_sent),
            branchNumber: new BranchNumber($branch_number),
            documentSkeleton: new DocumentSkeleton($document_skeleton),
            documentPurchaseOrder: new DocumentPurchaseOrder($document_purchase_order),
            durationMinutes: new DurationMinutes($duration_minutes),
            status: new Status($status),
            units: new FileServiceUnit($units),
            supplier: new FileCompositionSupplier(collect()),
            policiesCancellationService: new PoliciesCancellationService(collect()),
        );
    }

    public static function fromRequestSearch(Request $request): array
    {
        $name = $request->input('name', '');

        return [
            'name' => $name,
        ];
    }

    public static function fromArray($fileServiceComposition): FileServiceComposition
    {
        $fileServiceCompositionEloquentModel = new FileServiceCompositionEloquentModel($fileServiceComposition);
        $fileServiceCompositionEloquentModel->id = $fileServiceComposition['id'] ?? null;
        $fileServiceCompositionEloquentModel->units = collect();
        // $fileServiceCompositionEloquentModel->file_itinerary = collect();
        
        if (isset($fileServiceComposition['units'])) {
            foreach($fileServiceComposition['units'] as $unit) {
                $fileServiceCompositionEloquentModel->units->add($unit);
            }
        }
  
        return self::fromEloquent($fileServiceCompositionEloquentModel);
    }

    public static function fromEloquent(
        FileServiceCompositionEloquentModel $fileServiceCompositionEloquentModel
    ): FileServiceComposition
    {
 
        $supplier = collect();
        $policiesCancellationService = "";
        if($fileServiceCompositionEloquentModel->supplier?->toArray()){
           $supplier = $fileServiceCompositionEloquentModel->supplier?->toArray();
           $supplier = FileCompositionSupplierMapper::fromArray($supplier);
           $policiesCancellationService = $supplier->policiesCancellationService->value() ? $supplier->policiesCancellationService->value() : ''; 
        }
 
        $units = array_map(function ($units) {
            return FileServiceUnitMapper::fromArray($units);
        }, $fileServiceCompositionEloquentModel->units?->toArray() ?? []);

        return new FileServiceComposition(
            id: $fileServiceCompositionEloquentModel->id,
            fileServiceId: new FileServiceId($fileServiceCompositionEloquentModel->file_service_id),
            fileClassificationId: new FileClassificationId(
                $fileServiceCompositionEloquentModel->file_classification_id
            ),
            typeCompositionId: new TypeCompositionId($fileServiceCompositionEloquentModel->type_composition_id),
            typeComponentServiceId: new TypeComponentServiceId(
                $fileServiceCompositionEloquentModel->type_component_service_id
            ),
            compositionId: new CompositionId($fileServiceCompositionEloquentModel->composition_id),
            code: new Code($fileServiceCompositionEloquentModel->code),
            name: new Name($fileServiceCompositionEloquentModel->name),
            itemNumber: new ItemNumber($fileServiceCompositionEloquentModel->item_number),
            ratePlanCode: new RatePlanCode($fileServiceCompositionEloquentModel->rate_plan_code),
            totalAdults: new TotalAdults($fileServiceCompositionEloquentModel->total_adults),
            totalChildren: new TotalChildren($fileServiceCompositionEloquentModel->total_children),
            totalInfants: new TotalInfants($fileServiceCompositionEloquentModel->total_infants),
            totalExtra: new TotalExtra($fileServiceCompositionEloquentModel->total_extra),
            isProgrammable: new IsProgrammable($fileServiceCompositionEloquentModel->is_programmable),
            isInOpe: new IsInOpe($fileServiceCompositionEloquentModel->is_in_ope),
            sentToOpe: new SentToOpe($fileServiceCompositionEloquentModel->sent_to_ope),            
            countryInIso: new CountryInIso($fileServiceCompositionEloquentModel->country_in_iso),
            countryInName: new CountryInName($fileServiceCompositionEloquentModel->country_in_name),
            cityInIso: new CityInIso($fileServiceCompositionEloquentModel->city_in_iso),
            cityInName: new CityInName($fileServiceCompositionEloquentModel->city_in_name),
            countryOutIso: new CountryOutIso($fileServiceCompositionEloquentModel->country_out_iso),
            countryOutName: new CountryOutName($fileServiceCompositionEloquentModel->country_out_name),
            cityOutIso: new CityOutIso($fileServiceCompositionEloquentModel->city_out_iso),
            cityOutName: new CityOutName($fileServiceCompositionEloquentModel->city_out_name),
            startTime: new StartTime($fileServiceCompositionEloquentModel->start_time),
            departureTime: new DepartureTime($fileServiceCompositionEloquentModel->departure_time),
            dateIn: new DateIn($fileServiceCompositionEloquentModel->date_in),
            dateOut: new DateOut($fileServiceCompositionEloquentModel->date_out),
            currency: new Currency($fileServiceCompositionEloquentModel->currency),
            amountSale: new AmountSale($fileServiceCompositionEloquentModel->amount_sale),
            amountCost: new AmountCost($fileServiceCompositionEloquentModel->amount_cost),
            amountSaleOrigin: new AmountSale($fileServiceCompositionEloquentModel->amount_sale_origin),
            amountCostOrigin: new AmountCost($fileServiceCompositionEloquentModel->amount_cost_origin),
            markupCreated: new MarkupCreated($fileServiceCompositionEloquentModel->markup_created),
            taxes: new Taxes($fileServiceCompositionEloquentModel->taxes),
            totalServices: new TotalServices($fileServiceCompositionEloquentModel->total_services),
            useVoucher: new UseVoucher($fileServiceCompositionEloquentModel->use_voucher),
            useItinerary: new UseItinerary($fileServiceCompositionEloquentModel->use_itinerary),
            voucherSent: new VoucherSent($fileServiceCompositionEloquentModel->voucher_sent),
            voucherNumber: new VoucherNumber($fileServiceCompositionEloquentModel->voucher_number),
            useTicket: new UseTicket($fileServiceCompositionEloquentModel->use_ticket),
            useAccountingDocument: new UseAccountingDocument(
                $fileServiceCompositionEloquentModel->use_accounting_document
            ),
            ticketSent: new TicketSent($fileServiceCompositionEloquentModel->ticket_sent),
            accountingDocumentSent: new AccountingDocumentSent(
                $fileServiceCompositionEloquentModel->accounting_document_sent
            ),
            branchNumber: new BranchNumber($fileServiceCompositionEloquentModel->branch_number),
            documentSkeleton: new DocumentSkeleton($fileServiceCompositionEloquentModel->document_skeleton),
            documentPurchaseOrder: new DocumentPurchaseOrder(
                $fileServiceCompositionEloquentModel->document_purchase_order
            ),
            durationMinutes: new DurationMinutes($fileServiceCompositionEloquentModel->duration_minutes),
            status: new Status($fileServiceCompositionEloquentModel->status),
            units: new FileServiceUnit($units),
            supplier: new FileCompositionSupplier($supplier),
            policiesCancellationService: new PoliciesCancellationService($policiesCancellationService) 
        );
    }
    
    public static function toEloquent(
        FileServiceComposition $fileServiceComposition
    ): FileServiceCompositionEloquentModel
    {
        $fileServiceCompositionEloquent = new FileServiceCompositionEloquentModel();
        if ($fileServiceComposition->id) {
            $fileServiceCompositionEloquent = FileServiceCompositionEloquentModel::query()
                ->findOrFail($fileServiceComposition->id);
        }
        $fileServiceCompositionEloquent->file_service_id = $fileServiceComposition->fileServiceId->value();
        $fileServiceCompositionEloquent
            ->file_classification_id = $fileServiceComposition->fileClassificationId->value();
        $fileServiceCompositionEloquent
            ->type_composition_id = $fileServiceComposition->typeCompositionId->value();
        $fileServiceCompositionEloquent
            ->type_component_service_id = $fileServiceComposition->typeComponentServiceId->value();

        $fileServiceCompositionEloquent->composition_id = $fileServiceComposition->compositionId->value();
        $fileServiceCompositionEloquent->code = $fileServiceComposition->code->value();
        $fileServiceCompositionEloquent->name = $fileServiceComposition->name->value();
        $fileServiceCompositionEloquent->item_number = $fileServiceComposition->itemNumber->value();
        $fileServiceCompositionEloquent->rate_plan_code = $fileServiceComposition->ratePlanCode->value();
        $fileServiceCompositionEloquent->total_adults = $fileServiceComposition->totalAdults->value();
        $fileServiceCompositionEloquent->total_children = $fileServiceComposition->totalChildren->value();
        $fileServiceCompositionEloquent->total_infants = $fileServiceComposition->totalInfants->value();
        $fileServiceCompositionEloquent->total_extra = $fileServiceComposition->totalExtra->value();
        $fileServiceCompositionEloquent->is_programmable = $fileServiceComposition->isProgrammable->value();
        $fileServiceCompositionEloquent->is_in_ope = $fileServiceComposition->isInOpe->value();
        $fileServiceCompositionEloquent->sent_to_ope = $fileServiceComposition->sentToOpe->value();
        $fileServiceCompositionEloquent->country_in_iso = $fileServiceComposition->countryInIso->value();
        $fileServiceCompositionEloquent->country_in_name = $fileServiceComposition->countryInName->value();
        $fileServiceCompositionEloquent->city_in_iso = $fileServiceComposition->cityInIso->value();
        $fileServiceCompositionEloquent->city_in_name = $fileServiceComposition->cityInName->value();
        $fileServiceCompositionEloquent->country_out_iso = $fileServiceComposition->countryOutIso->value();
        $fileServiceCompositionEloquent->country_out_name = $fileServiceComposition->countryOutName->value();
        $fileServiceCompositionEloquent->city_out_iso = $fileServiceComposition->cityOutIso->value();
        $fileServiceCompositionEloquent->city_out_name = $fileServiceComposition->cityOutName->value();
        $fileServiceCompositionEloquent->start_time = $fileServiceComposition->startTime->value();
        $fileServiceCompositionEloquent->departure_time = $fileServiceComposition->departureTime->value();
        $fileServiceCompositionEloquent->date_in = $fileServiceComposition->dateIn->value();
        $fileServiceCompositionEloquent->date_out = $fileServiceComposition->dateOut->value();
        $fileServiceCompositionEloquent->currency = $fileServiceComposition->currency->value();
        $fileServiceCompositionEloquent->amount_sale = $fileServiceComposition->amountSale->value();
        $fileServiceCompositionEloquent->amount_cost = $fileServiceComposition->amountCost->value();
        $fileServiceCompositionEloquent->amount_sale_origin = $fileServiceComposition->amountSaleOrigin->value();
        $fileServiceCompositionEloquent->amount_cost_origin = $fileServiceComposition->amountCostOrigin->value();
        $fileServiceCompositionEloquent->markup_created = $fileServiceComposition->markupCreated->value();
        $fileServiceCompositionEloquent->taxes = $fileServiceComposition->taxes->value();
        $fileServiceCompositionEloquent->total_services = $fileServiceComposition->totalServices->value();
        $fileServiceCompositionEloquent->use_voucher = $fileServiceComposition->useVoucher->value();
        $fileServiceCompositionEloquent->use_itinerary = $fileServiceComposition->useItinerary->value();
        $fileServiceCompositionEloquent->voucher_sent = $fileServiceComposition->voucherSent->value();
        $fileServiceCompositionEloquent->voucher_number = $fileServiceComposition->voucherNumber->value();
        $fileServiceCompositionEloquent->use_ticket = $fileServiceComposition->useTicket->value();
        $fileServiceCompositionEloquent
            ->use_accounting_document = $fileServiceComposition->useAccountingDocument->value();
        $fileServiceCompositionEloquent->ticket_sent = $fileServiceComposition->ticketSent->value();
        $fileServiceCompositionEloquent
            ->accounting_document_sent = $fileServiceComposition->accountingDocumentSent->value();
        $fileServiceCompositionEloquent->branch_number = $fileServiceComposition->branchNumber->value();
        $fileServiceCompositionEloquent->document_skeleton = $fileServiceComposition->documentSkeleton->value();
        $fileServiceCompositionEloquent
            ->document_purchase_order = $fileServiceComposition->documentPurchaseOrder->value();
        $fileServiceCompositionEloquent->status = $fileServiceComposition->status->value();
        return $fileServiceCompositionEloquent;
    }

    public static function fromRequestUpdateSchedule(Request $request): array
    {
        $new_time_start = $request->input('start_time', null);
        $new_time_end = $request->input('departure_time', null);

        if($new_time_start === null  and $new_time_end === null){
            throw new \DomainException("null hours");
        }

        if(strtotime($new_time_start)  >  strtotime($new_time_end)){
            throw new \DomainException("departure time cannot be greater than start time");
        }
 
        return [
            'start_time' => new StartTime($new_time_start),
            'departure_time' => new DepartureTime($new_time_end),
        ];
    }
}
