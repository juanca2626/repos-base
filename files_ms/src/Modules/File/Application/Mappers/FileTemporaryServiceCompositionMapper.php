<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr; 
use Src\Modules\File\Domain\Model\FileTemporaryServiceComposition;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileCompositionSupplier;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AccountingDocumentSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Code;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Name;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\BranchNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\CompositionId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Currency;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DocumentPurchaseOrder;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DocumentSkeleton;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileClassificationId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService\FileTemporaryMasterServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\IsProgrammable;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\ItemNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\RatePlanCode;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Status;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\Taxes;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TicketSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalExtra;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TotalServices;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TypeComponentServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\TypeCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseAccountingDocument;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseItinerary;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseTicket;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\UseVoucher;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\VoucherNumber;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\VoucherSent;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\DurationMinutes;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileServiceUnit;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\PoliciesCancellationService; ;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceCompositionEloquentModel;
use stdClass;

class FileTemporaryServiceCompositionMapper
{
 
    public static function fromArray($fileServiceComposition): FileTemporaryServiceComposition
    {
        $fileServiceCompositionEloquentModel = new FileTemporaryServiceCompositionEloquentModel($fileServiceComposition);
        $fileServiceCompositionEloquentModel->id = $fileServiceComposition['id'] ?? null;
       
        

        if(isset($fileServiceComposition['supplier'])){
            $fileServiceCompositionEloquentModel->supplier = collect();
            $fileServiceCompositionEloquentModel->supplier = collect($fileServiceComposition['supplier']);
        }

        if (isset($fileServiceComposition['units'])) {
            $fileServiceCompositionEloquentModel->units = collect();
            foreach($fileServiceComposition['units'] as $unit) {
                $fileServiceCompositionEloquentModel->units->add($unit);
            }
        }
 
        return self::fromEloquent($fileServiceCompositionEloquentModel);
    }

    public static function fromEloquent(
        FileTemporaryServiceCompositionEloquentModel $fileServiceCompositionEloquentModel
    ): FileTemporaryServiceComposition
    {

        $supplier = collect();
        $policiesCancellationService = "";
        if($fileServiceCompositionEloquentModel->supplier?->toArray()){
           $supplier = $fileServiceCompositionEloquentModel->supplier?->toArray();
           $supplier = FileTemporaryServiceCompositionSupplierMapper::fromArray($supplier);
           $policiesCancellationService = $supplier->policiesCancellationService->value() ? $supplier->policiesCancellationService->value() : ''; 
        }
 
        $units = array_map(function ($units) {
            return FileTemporaryServiceUnitMapper::fromArray($units);
        }, $fileServiceCompositionEloquentModel->units?->toArray() ?? []);

        return new FileTemporaryServiceComposition(
            id: $fileServiceCompositionEloquentModel->id,
            fileTemporaryMasterServiceId: new FileTemporaryMasterServiceId($fileServiceCompositionEloquentModel->file_service_id),
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
        FileTemporaryServiceComposition $fileServiceComposition
    ): FileTemporaryServiceCompositionEloquentModel
    {
        $fileServiceCompositionEloquent = new FileTemporaryServiceCompositionEloquentModel();
        if ($fileServiceComposition->id) {
            $fileServiceCompositionEloquent = FileTemporaryServiceCompositionEloquentModel::query()
                ->findOrFail($fileServiceComposition->id);
        }
        $fileServiceCompositionEloquent->file_temporary_master_service_id = $fileServiceComposition->fileTemporaryMasterServiceId->value();
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
 
}
