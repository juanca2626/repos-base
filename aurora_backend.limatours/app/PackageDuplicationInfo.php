<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageDuplicationInfo extends Model
{
    const DUPLICATION_STATUS_PROCESSING = 'processing';
    const DUPLICATION_STATUS_PROCESSED = 'processed';

    protected $casts = [
        'processed_plan_rate_ids' => 'array',
    ];

    protected $fillable = [
        'duplicated_package_id',
        'processed_plan_rate_ids',
    ];

    public function isProcessing()
    {
        return $this->duplication_status === self::DUPLICATION_STATUS_PROCESSING;
    }

    public function getIsProcessingPlanRatesAttribute()
    {
        return $this->isProcessing();
    }

    public function duplicatedPackage()
    {
        return $this->belongsTo(Package::class, 'duplicated_package_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
