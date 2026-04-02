<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogClientRatePlan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_client_rate_plans';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'action',
        'rate_plan_id',
        'client_id',
        'period',
        'source',
        'reason',
        'executed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'executed_at' => 'datetime',
    ];

    /**
     * Relationship with Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Scope to filter by type
     */
    public function scopeHotel($query)
    {
        return $query->where('type', 'hotel');
    }

    /**
     * Scope to filter by type
     */
    public function scopeService($query)
    {
        return $query->where('type', 'service');
    }

    /**
     * Scope to filter by action
     */
    public function scopeInserted($query)
    {
        return $query->where('action', 'inserted');
    }

    /**
     * Scope to filter by action
     */
    public function scopeDeleted($query)
    {
        return $query->where('action', 'deleted');
    }

    /**
     * Scope to filter by period
     */
    public function scopePeriod($query, $year)
    {
        return $query->where('period', $year);
    }

    /**
     * Scope to filter by source
     */
    public function scopeSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Helper method to log an insertion
     */
    public static function logInsertion($type, $ratePlanId, $clientId, $period, $source, $reason = null)
    {
        return self::create([
            'type' => $type,
            'action' => 'inserted',
            'rate_plan_id' => $ratePlanId,
            'client_id' => $clientId,
            'period' => $period,
            'source' => $source,
            'reason' => $reason ?? 'Client not in whitelist',
        ]);
    }

    /**
     * Helper method to log a deletion
     */
    public static function logDeletion($type, $ratePlanId, $clientId, $period, $source, $reason = null)
    {
        return self::create([
            'type' => $type,
            'action' => 'deleted',
            'rate_plan_id' => $ratePlanId,
            'client_id' => $clientId,
            'period' => $period,
            'source' => $source,
            'reason' => $reason ?? 'Client now in whitelist',
        ]);
    }
}
