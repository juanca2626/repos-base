<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelTax extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id',
        'tax_id',
        'amount',
        'state'
    ];

    public function hotel()
    {
        return $this->belongsTo('App\Hotel');
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'hoteltaxes');
    }
}
