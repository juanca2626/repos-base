<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileHotelRoom;
use Src\Modules\File\Domain\Model\FileRoomAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\UserId;
use Src\Modules\File\Domain\ValueObjects\FileCategory\FileId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AccountingDocumentSent;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AdditionalInformation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\BranchNumber;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Currency;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\DocumentPurchaseOrder;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\DocumentSkeleton; 
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileHotelRoomUnits;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileRoomAmountLog as FileRoomAmount;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileRoomAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanName;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Status; 
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TaxedCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TaxedSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Taxes;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmountCreated;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmountExempt;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmountInvoice;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmountProvider;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAmountTaxed;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalRooms;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\UseAccountingDocument;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\UseItinerary;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\UseVoucher;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\VoucherNumber;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\VoucherSent;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ItemNumber;
use \Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomName;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomType;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Occupation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalExtra;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ChannelId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileAmountTypeFlagId as FileHotelRoomFileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Padlock;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\ExecutiveId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Module;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Motive;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ChannelReservationCodeMaster;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ConfirmationCode;

class FileHotelRoomMapper
{
    public static function fromArray(array $fileHotelRooms): FileHotelRoom
    {
        $fileHotelRoomsEloquentModel = new FileHotelRoomEloquentModel($fileHotelRooms);
        $fileHotelRoomsEloquentModel->id = $fileHotelRooms['id'] ?? null;
        $fileHotelRoomsEloquentModel->file_room_amount = collect();
        $fileHotelRoomsEloquentModel->fileRoomAmountLogs = collect();
        $fileHotelRoomsEloquentModel->units = collect();

        if (isset($fileHotelRooms['file_room_amount_logs'])) {
            foreach($fileHotelRooms['file_room_amount_logs'] as $amount_logs) {
                $fileHotelRoomsEloquentModel->fileRoomAmountLogs->add($amount_logs);
            }
        }

        if (isset($fileHotelRooms['units'])) {
            foreach($fileHotelRooms['units'] as $unit) {
                $fileHotelRoomsEloquentModel->units->add($unit);
            }
        }
        
        if(isset($fileHotelRooms['file_room_amount'])){
           $fileHotelRoomsEloquentModel->file_room_amount = collect($fileHotelRooms['file_room_amount']);
        }

        return self::fromEloquent($fileHotelRoomsEloquentModel);
    }

    public static function fromEloquent(FileHotelRoomEloquentModel $fileHotelRoomEloquentModel): FileHotelRoom
    {
        $roomAmount = collect();
        
        if($fileHotelRoomEloquentModel->file_room_amount?->toArray()) {
           $roomAmount = $fileHotelRoomEloquentModel->file_room_amount?->toArray();
           $roomAmount = FileRoomAmountLogMapper::fromArray($roomAmount);
        }
    
        $amountLogs = array_map(function ($amount_logs) {
            return FileRoomAmountLogMapper::fromArray($amount_logs);
        }, $fileHotelRoomEloquentModel->fileRoomAmountLogs?->toArray() ?? []);

        $units = array_map(function ($units) {
            return FileHotelRoomUnitMapper::fromArray($units);
        }, $fileHotelRoomEloquentModel->units?->toArray() ?? []);

        return new FileHotelRoom(
            id: $fileHotelRoomEloquentModel->id,
            fileItineraryId: new FileItineraryId($fileHotelRoomEloquentModel->file_itinerary_id),
            itemNumber: new ItemNumber($fileHotelRoomEloquentModel->item_number),
            totalRooms: new TotalRooms($fileHotelRoomEloquentModel->total_rooms),
            status: new Status($fileHotelRoomEloquentModel->status),
            confirmationStatus: new ConfirmationStatus($fileHotelRoomEloquentModel->confirmation_status),
            ratePlanId: new RatePlanId($fileHotelRoomEloquentModel->rate_plan_id),
            ratePlanName: new RatePlanName($fileHotelRoomEloquentModel->rate_plan_name),
            ratePlanCode: new RatePlanCode($fileHotelRoomEloquentModel->rate_plan_code),
            roomId: new RoomId($fileHotelRoomEloquentModel->room_id),
            roomName: new RoomName($fileHotelRoomEloquentModel->room_name),
            roomType: new RoomType($fileHotelRoomEloquentModel->room_type),
            occupation: new Occupation($fileHotelRoomEloquentModel->occupation),
            channelId: new ChannelId($fileHotelRoomEloquentModel->channel_id),
            additionalInformation: new AdditionalInformation($fileHotelRoomEloquentModel->additional_information),
            totalAdults: new TotalAdults($fileHotelRoomEloquentModel->total_adults),
            totalChildren: new TotalChildren($fileHotelRoomEloquentModel->total_children),
            totalInfants: new TotalInfants($fileHotelRoomEloquentModel->total_infants),
            totalExtra: new TotalExtra($fileHotelRoomEloquentModel->total_extra),
            currency: new Currency($fileHotelRoomEloquentModel->currency),
            amountSale: new AmountSale($fileHotelRoomEloquentModel->amount_sale),
            amountCost: new AmountCost($fileHotelRoomEloquentModel->amount_cost),
            taxedSale: new TaxedSale($fileHotelRoomEloquentModel->taxed_sale),
            taxedCost: new TaxedCost($fileHotelRoomEloquentModel->taxed_cost),
            totalAmount: new TotalAmount($fileHotelRoomEloquentModel->total_amount),
            markupCreated: new MarkupCreated($fileHotelRoomEloquentModel->markup_created),
            totalAmountCreated: new TotalAmountCreated($fileHotelRoomEloquentModel->total_amount_created),
            totalAmountProvider: new TotalAmountProvider($fileHotelRoomEloquentModel->total_amount_provider),
            totalAmountInvoice: new TotalAmountInvoice($fileHotelRoomEloquentModel->total_amount_invoice),
            totalAmountTaxed: new TotalAmountTaxed($fileHotelRoomEloquentModel->total_amount_taxed),
            totalAmountExempt: new TotalAmountExempt($fileHotelRoomEloquentModel->total_amount_exempt),
            taxes: new Taxes($fileHotelRoomEloquentModel->taxes),
            useVoucher: new UseVoucher($fileHotelRoomEloquentModel->use_voucher),
            useItinerary: new UseItinerary($fileHotelRoomEloquentModel->use_itinerary),
            voucherSent: new VoucherSent($fileHotelRoomEloquentModel->voucher_sent),
            voucherNumber: new VoucherNumber($fileHotelRoomEloquentModel->voucher_number),
            useAccountingDocument: new UseAccountingDocument($fileHotelRoomEloquentModel->use_accounting_document),
            accountingDocumentSent: new AccountingDocumentSent($fileHotelRoomEloquentModel->accounting_document_sent),
            branchNumber: new BranchNumber($fileHotelRoomEloquentModel->branch_number),
            documentSkeleton: new DocumentSkeleton($fileHotelRoomEloquentModel->document_skeleton),
            documentPurchaseOrder: new DocumentPurchaseOrder($fileHotelRoomEloquentModel->document_purchase_order),
            units: new FileHotelRoomUnits($units),
            fileRoomAmountLogs: new FileRoomAmountLogs($amountLogs),
            roomAmount: new FileRoomAmount($roomAmount),
            protectedRate: new ProtectedRate($fileHotelRoomEloquentModel->protected_rate),
            fileAmountTypeFlagId: new FileHotelRoomFileAmountTypeFlagId($fileHotelRoomEloquentModel->file_amount_type_flag_id),
            confirmationCode: new ConfirmationCode($fileHotelRoomEloquentModel->confirmation_code),
            channelReservationCodeMaster: new ChannelReservationCodeMaster($fileHotelRoomEloquentModel->channel_reservation_code_master)
            
        );
    }

    public static function toEloquent(FileHotelRoom $fileHotelRoom): FileHotelRoomEloquentModel
    {
        $fileHotelRoomEloquent = new FileHotelRoomEloquentModel();
        if ($fileHotelRoom->id) {
            $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->findOrFail($fileHotelRoom->id);
        }

        $fileHotelRoomEloquent->file_itinerary_id = $fileHotelRoom->fileItineraryId->value();
        $fileHotelRoomEloquent->item_number = $fileHotelRoom->itemNumber->value();
        $fileHotelRoomEloquent->total_rooms = $fileHotelRoom->totalRooms->value();
        $fileHotelRoomEloquent->status = $fileHotelRoom->status->value();
        $fileHotelRoomEloquent->confirmation_status = $fileHotelRoom->confirmationStatus->value();
        $fileHotelRoomEloquent->rate_plan_id = $fileHotelRoom->ratePlanId->value();
        $fileHotelRoomEloquent->rate_plan_name = $fileHotelRoom->ratePlanName->value();
        $fileHotelRoomEloquent->rate_plan_code = $fileHotelRoom->ratePlanCode->value();
        $fileHotelRoomEloquent->room_id = $fileHotelRoom->roomId->value();
        $fileHotelRoomEloquent->room_name = $fileHotelRoom->roomName->value();
        $fileHotelRoomEloquent->room_type = $fileHotelRoom->roomType->value();
        $fileHotelRoomEloquent->occupation = $fileHotelRoom->occupation->value();        
        $fileHotelRoomEloquent->channel_id = $fileHotelRoom->channelId->value();
        $fileHotelRoomEloquent->additional_information = $fileHotelRoom->additionalInformation->value();
        $fileHotelRoomEloquent->total_adults = $fileHotelRoom->totalAdults->value();
        $fileHotelRoomEloquent->total_children = $fileHotelRoom->totalChildren->value();
        $fileHotelRoomEloquent->total_infants = $fileHotelRoom->totalInfants->value();
        $fileHotelRoomEloquent->total_extra = $fileHotelRoom->totalExtra->value();
        $fileHotelRoomEloquent->currency = $fileHotelRoom->currency->value();
        $fileHotelRoomEloquent->amount_sale = $fileHotelRoom->amountSale->value();
        $fileHotelRoomEloquent->amount_cost = $fileHotelRoom->amountCost->value();
        $fileHotelRoomEloquent->taxed_sale = $fileHotelRoom->taxedSale->value();
        $fileHotelRoomEloquent->taxed_cost = $fileHotelRoom->taxedCost->value();
        $fileHotelRoomEloquent->total_amount = $fileHotelRoom->totalAmount->value();
        $fileHotelRoomEloquent->markup_created = $fileHotelRoom->markupCreated->value();
        $fileHotelRoomEloquent->total_amount_created = $fileHotelRoom->totalAmountCreated->value();
        $fileHotelRoomEloquent->total_amount_provider = $fileHotelRoom->totalAmountProvider->value();
        $fileHotelRoomEloquent->total_amount_invoice = $fileHotelRoom->totalAmountInvoice->value();
        $fileHotelRoomEloquent->total_amount_taxed = $fileHotelRoom->totalAmountTaxed->value();
        $fileHotelRoomEloquent->total_amount_exempt = $fileHotelRoom->totalAmountExempt->value();
        $fileHotelRoomEloquent->taxes = $fileHotelRoom->taxes->value();
        $fileHotelRoomEloquent->use_voucher = $fileHotelRoom->useVoucher->value();
        $fileHotelRoomEloquent->use_itinerary = $fileHotelRoom->useItinerary->value();
        $fileHotelRoomEloquent->voucher_sent = $fileHotelRoom->voucherSent->value();
        $fileHotelRoomEloquent->voucher_number = $fileHotelRoom->voucherNumber->value();
        $fileHotelRoomEloquent->use_accounting_document = $fileHotelRoom->useAccountingDocument->value();
        $fileHotelRoomEloquent->accounting_document_sent = $fileHotelRoom->accountingDocumentSent->value();
        $fileHotelRoomEloquent->branch_number = $fileHotelRoom->branchNumber->value();
        $fileHotelRoomEloquent->document_skeleton = $fileHotelRoom->documentSkeleton->value();
        $fileHotelRoomEloquent->document_purchase_order = $fileHotelRoom->documentPurchaseOrder->value();
        $fileHotelRoomEloquent->protected_rate = $fileHotelRoom->protectedRate->value();
        $fileHotelRoomEloquent->file_amount_type_flag_id = $fileHotelRoom->fileAmountTypeFlagId->value();
        $fileHotelRoomEloquent->confirmation_code = $fileHotelRoom->confirmationCode->value();
        $fileHotelRoomEloquent->channel_reservation_code_master = $fileHotelRoom->channelReservationCodeMaster->value();
        
        
        return $fileHotelRoomEloquent;
    }

    public static function fromRequestUpdateAmountCost(Request $request): array
    { 
        $file_amount_reason_id = $request->input('file_amount_reason_id', 0); 
        $file_amount_type_flag_id = $request->input('file_amount_type_flag_id', 0); 
        $amount_cost = $request->input('new_amount', 0); 
        $user_id = $request->input('user_id', 1);
        return [
            'file_amount_reason_id' => new FileAmountReasonId($file_amount_reason_id),
            'file_amount_type_flag_id' => new FileAmountTypeFlagId($file_amount_type_flag_id),
            'amount_cost' => new AmountCost($amount_cost),
            'user_id' => new UserId($user_id)
        ];
 
    }  

    public static function fromArrayUpdateAmountCost(array $data): array
    { 
        $file_amount_reason_id = $data['file_amount_reason_id']; 
        $file_amount_type_flag_id = $data['file_amount_type_flag_id'];   
        $executive_id = isset($data['executive_id']) ? $data['executive_id'] : null ; 
        $file_id = isset($data['file_id']) ? $data['file_id'] : null ; 
        $motive = isset($data['motive']) ? $data['motive'] : "" ; 
        
        $amount_cost = $data['new_amount'];   
        $user_id = isset($data['user_id']) ? $data['user_id'] : 1;
        $module = isset($data['module']) ? $data['module'] : "";

        return [
            'file_amount_reason_id' => new FileAmountReasonId($file_amount_reason_id),
            'file_amount_type_flag_id' => new FileAmountTypeFlagId($file_amount_type_flag_id),
            'executive_id' => new ExecutiveId($executive_id),
            'file_id' => new FileId($file_id),
            'motive' => new Motive($motive),
            'amount_cost' => new AmountCost($amount_cost),
            'user_id' => new UserId($user_id),
            'module' => new Module($module)
        ];
 
    }  


    public static function fromRequestUpdatePassengers(Request $request): array
    { 
        $passengers = $request->input('passengers', []);
         
        return $passengers;
 
    }  

}
