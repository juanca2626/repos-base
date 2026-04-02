<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionPentagramaDetailService extends Model
{
    // use SoftDel
    // etes;
    /**
     * Attributes allowed for mass assignment.
     * The fields below mirror the sanitized detail structure used in the trait.
     */
    protected $fillable = [
        'extension_pentagrama_service_id',
        'executive_name',
        'city',
        'single_date',
        'single_hour',
        'type_service', // ('hotel','train','flight','transfer','tour','restaurant','other')
        'original_service_id',
        'external_service_id',
        'external_service_description',
        'original_type',
        'recheck_status_service',
        'status',
    ];

    /**
     * Some fields may be boolean or JSON, cast appropriately.
     */
    protected $casts = [
        'recheck_status_service' => 'boolean',
        'single_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(ExtensionPentagramaService::class, 'extension_pentagrama_service_id');
    }
}
