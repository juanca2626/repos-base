<?php

namespace Src\Modules\FileV2\Application\UseCases;

use Src\Modules\FileV2\Infrastructure\Persistence\FileRepository;

class GetFile
{
    private $repo;

    public function __construct(FileRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id): array
    {
        $file = $this->repo->find($id);

        if (!$file) {
            throw new \Exception('File no encontrado');
        }

        return [
            'serie_reserve_id' => $file->serie_reserve_id,
            'client_id' => $file->client_id,
            'client_code' => $file->client_code,
            'client_name' => $file->client_name,
            'client_have_credit' => $file->client_have_credit,
            'client_credit_line' => $file->client_credit_line,
            'reservation_id' => $file->reservation_id,
            'order_number' => $file->order_number,
            'file_number' => $file->file_number,
            'reservation_number' => $file->reservation_number,
            'budget_number' => $file->budget_number,
            'sector_code' => $file->sector_code,
            'group' => $file->group,
            'sale_type' => $file->sale_type,
            'tariff' => $file->tariff,
            'currency' => $file->currency,
            'revision_stages' => $file->revision_stages,
            'ope_assign_stages' => $file->ope_assign_stages,
            'executive_id' => $file->executive_id,
            'executive_code' => $file->executive_code,
            'executive_code_sale' => $file->executive_code_sale,
            'executive_code_process' => $file->executive_code_process,
            'applicant' => $file->applicant,
            'file_code_agency' => $file->file_code_agency,
            'description' => $file->description,
            'lang' => $file->lang,
            'date_in' => $file->date_in,
            'date_out' => $file->date_out,
            'adults' => $file->adults,
            'children' => $file->children,
            'infants' => $file->infants,
            'use_invoice' => $file->use_invoice,
            'observation' => $file->observation,
            'total_pax' => $file->total_pax,
            'have_quote' => $file->have_quote,
            'have_voucher' => $file->have_voucher,
            'have_ticket' => $file->have_ticket,
            'have_invoice' => $file->have_invoice,
            'status' => $file->status,
            'promotion' => $file->promotion,
            'total_amount' => $file->total_amount,
            'markup_client' => $file->markup_client,
            'type_class_id' => $file->type_class_id,
            'suggested_accommodation_sgl' => $file->suggested_accommodation_sgl,
            'suggested_accommodation_dbl' => $file->suggested_accommodation_dbl,
            'suggested_accommodation_tpl' => $file->suggested_accommodation_tpl,
            'generate_statement' => $file->generate_statement,
            'file_reason_statement_id' => $file->file_reason_statement_id,
            'protected_rate' => $file->protected_rate,
            'view_protected_rate' => $file->view_protected_rate,
            'created_at' => $file->created_at,
            'origin' => $file->origin,
            'stela_processing' => $file->stela_processing,
            'stela_processing_error' => $file->stela_processing_error,
        ];
    }
}