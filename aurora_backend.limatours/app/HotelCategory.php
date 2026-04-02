<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelCategory extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')->where('translations.type', '=', 'hotelcategory');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }
}
