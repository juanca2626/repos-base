<?php

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServicesScheduleExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->flatMap(function ($service) {
            return collect($service['compositions'])->map(function ($composition) use ($service) {
                return [
                    // Datos del servicio
                    'service_id' => $service['id'] ?? null,
                    'file_itinerary_id' => $service['file_itinerary_id'] ?? null,
                    'master_service_id' => $service['master_service_id'] ?? null,
                    'service_name' => $service['name'] ?? null,
                    'service_code' => $service['code'] ?? null,
                    'type_ifx' => $service['type_ifx'] ?? null,
                    'status' => $service['status'] ?? null,
                    'confirmation_status' => $service['confirmation_status'] ?? null,
                    'date_in' => $service['date_in'] ?? null,
                    'date_out' => $service['date_out'] ?? null,
                    'start_time' => $service['start_time'] ?? null,
                    'departure_time' => $service['departure_time'] ?? null,
                    'amount_cost' => $service['amount_cost'] ?? null,
                    'rate_plan_code' => $service['rate_plan_code'] ?? null,
                    'is_in_ope' => $service['is_in_ope'] ?? null,
                    'sent_to_ope' => $service['sent_to_ope'] ?? null,
                    'created_at' => $service['created_at'] ?? null,
                    'updated_at' => $service['updated_at'] ?? null,
                    'deleted_at' => $service['deleted_at'] ?? null,
                    
                    // Datos de la composición
                    'composition_id' => $composition['id'] ?? null,
                    'file_service_id' => $composition['file_service_id'] ?? null,
                    'file_classification_id' => $composition['file_classification_id'] ?? null,
                    'type_composition_id' => $composition['type_composition_id'] ?? null,
                    'type_component_service_id' => $composition['type_component_service_id'] ?? null,
                    'composition_code' => $composition['code'] ?? null,
                    'composition_name' => $composition['name'] ?? null,
                    'composition_item_number' => $composition['item_number'] ?? null,
                    'composition_duration_minutes' => $composition['duration_minutes'] ?? null,
                    'composition_rate_plan_code' => $composition['rate_plan_code'] ?? null,
                    'composition_total_adults' => $composition['total_adults'] ?? null,
                    'composition_total_children' => $composition['total_children'] ?? null,
                    'composition_total_infants' => $composition['total_infants'] ?? null,
                    'composition_total_extra' => $composition['total_extra'] ?? null,
                    'composition_is_programmable' => $composition['is_programmable'] ?? null,
                    'composition_is_in_ope' => $composition['is_in_ope'] ?? null,
                    'composition_sent_to_ope' => $composition['sent_to_ope'] ?? null,
                    'composition_country_in_iso' => $composition['country_in_iso'] ?? null,
                    'composition_country_in_name' => $composition['country_in_name'] ?? null,
                    'composition_city_in_iso' => $composition['city_in_iso'] ?? null,
                    'composition_city_in_name' => $composition['city_in_name'] ?? null,
                    'composition_country_out_iso' => $composition['country_out_iso'] ?? null,
                    'composition_country_out_name' => $composition['country_out_name'] ?? null,
                    'composition_city_out_iso' => $composition['city_out_iso'] ?? null,
                    'composition_city_out_name' => $composition['city_out_name'] ?? null,
                    'composition_start_time' => $composition['start_time'] ?? null,
                    'composition_departure_time' => $composition['departure_time'] ?? null,
                    'composition_date_in' => $composition['date_in'] ?? null,
                    'composition_date_out' => $composition['date_out'] ?? null,
                    'composition_currency' => $composition['currency'] ?? null,
                    'composition_amount_sale' => $composition['amount_sale'] ?? null,
                    'composition_amount_cost' => $composition['amount_cost'] ?? null,
                    'composition_amount_sale_origin' => $composition['amount_sale_origin'] ?? null,
                    'composition_amount_cost_origin' => $composition['amount_cost_origin'] ?? null,
                    'composition_markup_created' => $composition['markup_created'] ?? null,
                    'composition_taxes' => $composition['taxes'] ?? null,
                    'composition_total_services' => $composition['total_services'] ?? null,
                    'composition_use_voucher' => $composition['use_voucher'] ?? null,
                    'composition_use_itinerary' => $composition['use_itinerary'] ?? null,
                    'composition_voucher_sent' => $composition['voucher_sent'] ?? null,
                    'composition_voucher_number' => $composition['voucher_number'] ?? null,
                    'composition_use_ticket' => $composition['use_ticket'] ?? null,
                    'composition_use_accounting_document' => $composition['use_accounting_document'] ?? null,
                    'composition_ticket_sent' => $composition['ticket_sent'] ?? null,
                    'composition_accounting_document_sent' => $composition['accounting_document_sent'] ?? null,
                    'composition_branch_number' => $composition['branch_number'] ?? null,
                    'composition_document_skeleton' => $composition['document_skeleton'] ?? null,
                    'composition_document_purchase_order' => $composition['document_purchase_order'] ?? null,
                    'composition_status' => $composition['status'] ?? null,
                    'composition_created_at' => $composition['created_at'] ?? null,
                    'composition_updated_at' => $composition['updated_at'] ?? null,
                    'composition_deleted_at' => $composition['deleted_at'] ?? null,
                ];
            });
        });
    }

    public function headings(): array
    {
        return [
            // Encabezados para los servicios
            'Service ID',
            'File Itinerary ID',
            'Master Service ID',
            'Service Name',
            'Service Code',
            'Type IFX',
            'Status',
            'Confirmation Status',
            'Date In',
            'Date Out',
            'Start Time',
            'Departure Time',
            'Amount Cost',
            'Rate Plan Code',
            'Is In OPE',
            'Sent To OPE',
            'Created At',
            'Updated At',
            'Deleted At',
            
            // Encabezados para las composiciones
            'Composition ID',
            'File Service ID',
            'File Classification ID',
            'Type Composition ID',
            'Type Component Service ID',
            'Composition Code',
            'Composition Name',
            'Composition Item Number',
            'Composition Duration (Minutes)',
            'Composition Rate Plan Code',
            'Composition Total Adults',
            'Composition Total Children',
            'Composition Total Infants',
            'Composition Total Extra',
            'Composition Is Programmable',
            'Composition Is In OPE',
            'Composition Sent To OPE',
            'Composition Country In ISO',
            'Composition Country In Name',
            'Composition City In ISO',
            'Composition City In Name',
            'Composition Country Out ISO',
            'Composition Country Out Name',
            'Composition City Out ISO',
            'Composition City Out Name',
            'Composition Start Time',
            'Composition Departure Time',
            'Composition Date In',
            'Composition Date Out',
            'Composition Currency',
            'Composition Amount Sale',
            'Composition Amount Cost',
            'Composition Amount Sale Origin',
            'Composition Amount Cost Origin',
            'Composition Markup Created',
            'Composition Taxes',
            'Composition Total Services',
            'Composition Use Voucher',
            'Composition Use Itinerary',
            'Composition Voucher Sent',
            'Composition Voucher Number',
            'Composition Use Ticket',
            'Composition Use Accounting Document',
            'Composition Ticket Sent',
            'Composition Accounting Document Sent',
            'Composition Branch Number',
            'Composition Document Skeleton',
            'Composition Document Purchase Order',
            'Composition Status',
            'Composition Created At',
            'Composition Updated At',
            'Composition Deleted At',
        ];
    }
}