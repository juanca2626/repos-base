<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    /*
     * Todo Column: status
     *  Estados dentro de una cotizacion
     */

    // Todo Cotizacion en modo edicion (Borrador)
    public const STATUS_EDITING_DRAFT = 2;

    // Todo Cotizacion cerrada
    public const STATUS_CLOSE = 1;

    // Todo Cotizacion eliminada
    public const STATUS_DELETE = 0;

    protected $fillable = ['shared'];

    protected $casts = [
        'is_multiregion' => 'boolean',
    ];
    // /**
    //  * Get the quote status as an enum
    //  */
    // public function getStatusAttribute($value): ?QuoteStatus
    // {
    //     return QuoteStatus::fromValue($value);
    // }

    // /**
    //  * Establezce el estado de la cotizacion desde el enum o int
    //  */
    // public function setStatusAttribute($value): void
    // {
    //     if ($value instanceof QuoteStatus) {
    //         $this->attributes['status'] = $value->value;
    //     } elseif (is_int($value) || (is_string($value) && is_numeric($value))) {
    //         $this->attributes['status'] = (int) $value;
    //     }
    // }

    public function history_logs(): HasMany
    {
        return $this->hasMany('App\Models\QuoteHistoryLog');
    }

    public function logs(): HasMany
    {
        return $this->hasMany('App\Models\QuoteLog');
    }

    public function log_user(): HasMany
    {
        return $this->hasMany('App\Models\QuoteLog')
            ->whereIn('quote_logs.type', ['copy_to', 'copy_from']);
    }

    public function categories(): HasMany
    {
        return $this->hasMany('App\Models\QuoteCategory');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany('App\Models\QuoteDistribution');
    }

    public function ranges(): HasMany
    {
        return $this->hasMany('App\Models\QuoteRange');
    }

    public function notes(): HasMany
    {
        return $this->hasMany('App\Models\QuoteNote');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany('App\Models\QuotePassenger');
    }

    public function people(): HasMany
    {
        return $this->hasMany('App\Models\QuotePeople');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany('App\Models\QuoteDestination');
    }

    public function service_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    public function age_child(): HasMany
    {
        return $this->hasMany('App\Models\QuoteAgeChild');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function permission(): HasOne
    {
        return $this->hasOne('App\Models\ShareQuote', 'quote_id', 'id');
    }

    public function reservation(): HasOne
    {
        return $this->hasOne('App\Models\Reservation', 'object_id', 'id')
            ->where('reservations.entity', 'Quote')
            ->whereNotNull('reservations.type_class_id')
            ->orderBy('reservations.id', 'desc');
    }

    public function accommodation(): HasOne
    {
        return $this->hasOne('App\Models\QuoteAccommodation');
    }
}
