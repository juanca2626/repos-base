<?php 
namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FileRoomExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {

       // dd($this->fileRoom);
        // Aquí mapearás los datos del archivo y los servicios, como hicimos antes.
       return [
            [
                'id' => $this->data['id'],
                'file_itinerary_id' => $this->data['file_itinerary_id'],
                'item_number' => $this->data['item_number'],
                'total_rooms' => $this->data['total_rooms'],
                'status' => $this->data['status'],
                'confirmation_status' => $this->data['confirmation_status'],
                'rate_plan_id' => $this->data['rate_plan_id'],
                'rate_plan_name' => $this->data['rate_plan_name'],
                'rate_plan_code' => $this->data['rate_plan_code'],
                'room_id' => $this->data['room_id'],
                'room_name' => $this->data['room_name'],
                'room_type' => $this->data['room_type'],
                'occupation' => $this->data['occupation'],
                'channel_id' => $this->data['channel_id'],
                'additional_information' => $this->data['additional_information'],
                'total_adults' => $this->data['total_adults'],
                'total_children' => $this->data['total_children'],
                'total_infants' => $this->data['total_infants'],
                'total_extra' => $this->data['total_extra'],
                'currency' => $this->data['currency'],
                'amount_sale' => $this->data['amount_sale'],
                'amount_cost' => $this->data['amount_cost'],
                'taxed_sale' => $this->data['taxed_sale'],
                'taxed_cost' => $this->data['taxed_cost'],
                'total_amount' => $this->data['total_amount'],
                'markup_created' => $this->data['markup_created'],
                'total_amount_created' => $this->data['total_amount_created'],
                'total_amount_provider' => $this->data['total_amount_provider'],
                'total_amount_invoice' => $this->data['total_amount_invoice'],
                'total_amount_taxed' => $this->data['total_amount_taxed'],
                'total_amount_exempt' => $this->data['total_amount_exempt'],
                'taxes' => $this->data['taxes'],
                'use_voucher' => $this->data['use_voucher'],
                'use_itinerary' => $this->data['use_itinerary'],
                'voucher_sent' => $this->data['voucher_sent'],
                'voucher_number' => $this->data['voucher_number'],
                'use_accounting_document' => $this->data['use_accounting_document'],
                'accounting_document_sent' => $this->data['accounting_document_sent'],
                'branch_number' => $this->data['branch_number'],
                'document_skeleton' => $this->data['document_skeleton'],
                'document_purchase_order' => $this->data['document_purchase_order'],
                'created_at' => $this->data['created_at'],
                'updated_at' => $this->data['updated_at'],
                'deleted_at' => $this->data['deleted_at'],
                'protected_rate' => $this->data['protected_rate'],
                'file_amount_type_flag_id' => $this->data['file_amount_type_flag_id'],
                // Puedes continuar agregando las demás columnas aquí...
            ],
        ];
    }

    public function headings(): array
    {
         return [
            'ID',
            'File Itinerary ID',
            'Item Number',
            'Total Rooms',
            'Status',
            'Confirmation Status',
            'Rate Plan ID',
            'Rate Plan Name',
            'Rate Plan Code',
            'Room ID',
            'Room Name',
            'Room Type',
            'Occupation',
            'Channel ID',
            'Additional Information',
            'Total Adults',
            'Total Children',
            'Total Infants',
            'Total Extra',
            'Currency',
            'Amount Sale',
            'Amount Cost',
            'Taxed Sale',
            'Taxed Cost',
            'Total Amount',
            'Markup Created',
            'Total Amount Created',
            'Total Amount Provider',
            'Total Amount Invoice',
            'Total Amount Taxed',
            'Total Amount Exempt',
            'Taxes',
            'Use Voucher',
            'Use Itinerary',
            'Voucher Sent',
            'Voucher Number',
            'Use Accounting Document',
            'Accounting Document Sent',
            'Branch Number',
            'Document Skeleton',
            'Document Purchase Order',
            'Created At',
            'Updated At',
            'Deleted At',
            'Protected Rate',
            'File Amount Type Flag ID',
        ];
    }
}