<?php

namespace App\Http\Controllers\Pentagrama\Models;

use App\Models\ChannelHotel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ExtensionPentagramaDetailService extends Model
{
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

    public function external_type_service()
    {
        return $this->belongsTo(Service::class, 'external_service_id', 'aurora_code')->with('service_rate')->select('id', 'name', 'aurora_code', 'service_type_id');
    }

    public function type_hotel()
    {
        return $this->belongsTo(ChannelHotel::class, 'external_service_id', 'code')->where('channel_id', 1)->where('state', 1)->with('hotel.rates_plans_rooms.room')->select('id', 'hotel_id', 'code', 'state');
    }
}
