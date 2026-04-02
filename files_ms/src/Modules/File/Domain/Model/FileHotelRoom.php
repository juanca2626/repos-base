<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileHotelRoomUnits;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanName;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RatePlanCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TaxedSale;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AccountingDocumentSent;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AdditionalInformation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\BranchNumber;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ChannelId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ChannelReservationCodeMaster;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ConfirmationCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Currency;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\DocumentPurchaseOrder;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\DocumentSkeleton;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileRoomAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileRoomAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ItemNumber;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Occupation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\MarkupCreated; 
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomName;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\RoomType;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\Status;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TaxedCost;
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
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalExtra;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TotalInfants;
use Src\Shared\Domain\Entity;

class FileHotelRoom extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileItineraryId $fileItineraryId,
        public readonly ItemNumber $itemNumber,
        public readonly TotalRooms $totalRooms,
        public readonly Status $status,
        public readonly ConfirmationStatus $confirmationStatus,
        public readonly RatePlanId $ratePlanId,
        public readonly RatePlanName $ratePlanName,
        public readonly RatePlanCode $ratePlanCode,        
        public readonly RoomId $roomId,
        public readonly RoomName $roomName,
        public readonly RoomType $roomType,
        public readonly Occupation $occupation,        
        public readonly ChannelId $channelId,
        public readonly AdditionalInformation $additionalInformation,
        public readonly TotalAdults $totalAdults,
        public readonly TotalChildren $totalChildren,
        public readonly TotalInfants $totalInfants,
        public readonly TotalExtra $totalExtra,
        public readonly Currency $currency,
        public readonly AmountSale $amountSale,
        public readonly AmountCost $amountCost,
        public readonly TaxedSale $taxedSale,
        public readonly TaxedCost $taxedCost,
        public readonly TotalAmount $totalAmount,
        public readonly MarkupCreated $markupCreated,
        public readonly TotalAmountCreated $totalAmountCreated,
        public readonly TotalAmountProvider $totalAmountProvider,
        public readonly TotalAmountInvoice $totalAmountInvoice,
        public readonly TotalAmountTaxed $totalAmountTaxed,
        public readonly TotalAmountExempt $totalAmountExempt,
        public readonly Taxes $taxes,
        public readonly UseVoucher $useVoucher,
        public readonly UseItinerary $useItinerary,
        public readonly VoucherSent $voucherSent,
        public readonly VoucherNumber $voucherNumber,
        public readonly UseAccountingDocument $useAccountingDocument,
        public readonly AccountingDocumentSent $accountingDocumentSent,
        public readonly BranchNumber $branchNumber,
        public readonly DocumentSkeleton $documentSkeleton,
        public readonly DocumentPurchaseOrder $documentPurchaseOrder,
        public readonly FileHotelRoomUnits $units,
        public readonly FileRoomAmountLogs $fileRoomAmountLogs,
        public readonly FileRoomAmountLog $roomAmount,
        public readonly ProtectedRate $protectedRate,
        public readonly FileAmountTypeFlagId $fileAmountTypeFlagId,
        public readonly ConfirmationCode $confirmationCode, 
        public readonly ChannelReservationCodeMaster $channelReservationCodeMaster 
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_itinerary_id' => $this->fileItineraryId->value(),
            'total_rooms' => $this->totalRooms->value(),
            'status' => $this->status->value(),
            'confirmation_status' => $this->confirmationStatus->value(),
            'rate_plan_id' => $this->ratePlanId->value(),
            'rate_plan_name' => $this->ratePlanName->value(),
            'rate_plan_code' => $this->ratePlanCode->value(),
            'room_id' => $this->roomId->value(), 
            'room_name' => $this->roomName->value(),
            'room_type' => $this->roomType->value(),
            'occupation' => $this->occupation->value(),
            'channel_id' => $this->channelId->value(),
            'additional_information' => $this->additionalInformation->value(),
            'total_adults' => $this->totalAdults->value(),
            'total_children' => $this->totalChildren->value(),
            'total_infants' => $this->totalInfants->value(),
            'total_extra' => $this->totalExtra->value(),
            'currency' => $this->currency->value(),
            'amount_sale' => $this->amountSale->value(),
            'amount_cost' => $this->amountCost->value(),
            'taxed_sale' => $this->taxedSale->value(),
            'taxed_cost' => $this->taxedCost->value(),
            'total_amount' => $this->totalAmount->value(),
            'markup_created' => $this->markupCreated->value(),
            'total_amount_created' => $this->totalAmountCreated->value(),
            'total_amount_provider' => $this->totalAmountProvider->value(),
            'total_amount_invoice' => $this->totalAmountInvoice->value(),
            'total_amount_taxed' => $this->totalAmountTaxed->value(),
            'total_amount_exempt' => $this->totalAmountExempt->value(),
            'taxes' => $this->taxes->value(),
            'use_voucher' => $this->useVoucher->value(),
            'use_itinerary' => $this->useItinerary->value(),
            'voucher_sent' => $this->voucherSent->value(),
            'voucher_number' => $this->voucherNumber->value(),
            'use_accounting_document' => $this->useAccountingDocument->value(),
            'accounting_document_sent' => $this->accountingDocumentSent->value(),
            'branch_number' => $this->branchNumber->value(),
            'document_skeleton' => $this->documentSkeleton->value(),
            'document_purchase_order' => $this->documentPurchaseOrder->value(),
            'units' => $this->units->jsonSerialize(),
            'file_room_amount_logs' => $this->fileRoomAmountLogs->jsonSerialize(),
            'room_amount' => $this->roomAmount->jsonSerialize(),
            'protected_rate' => $this->protectedRate->jsonSerialize(),
            'file_amount_type_flag_id' => $this->fileAmountTypeFlagId->jsonSerialize(),
            'confirmation_code' => $this->confirmationCode->value(),
            'channel_reservation_code_master' => $this->channelReservationCodeMaster->value()
        ];
    }

}
