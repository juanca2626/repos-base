<?php

namespace Src\Modules\FileV2\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_OK = 'OK';
    const STATUS_CANCELED = 'XL';
    const STATUS_LOCKED = 'BL';
    const STATUS_CLOSED = 'CE';
    const STATUS_BY_BILL = 'PF';

    protected $table = 'files';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura
    // protected $appends = array('status_reason', 'status_reason_id', 'total_cost_amount', 'profitability');

    protected $fillable = [
        'serie_reserve_id',
        'client_id',
        'client_code',
        'client_name',
        'client_have_credit',
        'client_credit_line',
        'reservation_id',
        'order_number',
        'file_number',
        'reservation_number',
        'budget_number',
        'sector_code',
        'group',
        'sale_type',
        'tariff',
        'currency',
        'revision_stages',
        'executive_id',
        'executive_code',
        'executive_code_sale',
        'executive_code_process',
        'applicant',
        'file_code_agency',
        'description',
        'lang',
        'date_in',
        'date_out',
        'adults',
        'children',
        'infants',
        'use_invoice',
        'observation',
        'total_pax',
        'have_quote',
        'have_voucher',
        'have_ticket',
        'have_invoice',
        'status',
        'status_reason_id',
        'processing',
        'promotion',
        'total_amount',
        'type_class_id',
        'suggested_accommodation_sgl',
        'suggested_accommodation_dbl',
        'suggested_accommodation_tpl',
        'generate_statement',
        'reason_statement_id',
        'protected_rate',
        'view_protected_rate',
        'origin',
        'stela_processing',
        'stela_processing_error',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(FileCategory::class);
    }

 
}
