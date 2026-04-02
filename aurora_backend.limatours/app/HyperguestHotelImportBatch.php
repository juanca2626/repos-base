<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HyperguestHotelImportBatch extends Model
{
    use SoftDeletes;

    protected $table = 'hyperguest_hotel_import_batches';

    protected $fillable = [
        'user_id',
        'country',
        'hotel_ids',
        'status',
        'total_hotels',
        'completed_hotels',
        'failed_hotels',
        'hotel_results',
        'error_message',
        'viewed'
    ];

    protected $casts = [
        'hotel_ids' => 'array',
        'hotel_results' => 'array',
        'error_message' => 'array',
        'viewed' => 'boolean'
    ];

    /**
     * Get the user that initiated the import
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the country by ISO code
     */
    public function countryRelation()
    {
        return $this->belongsTo('App\Country', 'country', 'iso');
    }

    /**
     * Get the percentage of completed hotels
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_hotels == 0) {
            return 0;
        }
        return round(($this->completed_hotels / $this->total_hotels) * 100, 2);
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if batch failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if batch is processing
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }
}

