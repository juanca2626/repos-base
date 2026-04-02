<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'country_id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function hotelTaxes(): HasMany
    {
        return $this->hasMany('App\Models\HotelTax');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'tax');
    }

    public function serviceTaxes(): HasMany
    {
        return $this->hasMany('App\Models\ServiceTax');
    }

    public function hotel(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
