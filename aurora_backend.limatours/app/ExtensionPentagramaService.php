<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionPentagramaService extends Model
{
    /**
     * Fillable attributes for mass assignment when creating from service.
     * passenger: string extracted from payload
     * header: full header array (cast to json)
     */
    protected $fillable = [
        'user_id',
        'passenger',
        'status',
    ];

    protected $casts = [
        'passenger' => 'string',
    ];

    /**
     * Relation to detail records
     */
    public function details()
    {
        return $this->hasMany(ExtensionPentagramaDetailService::class, 'extension_pentagrama_service_id');
    }
}
