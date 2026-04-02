<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfigMarkup extends Model
{
    protected $fillable = [
        'type',
        'markup',
        'category_id',
        'status',
        'percent',
        'prev',
    ];

    public function service_category()
    {
        return $this->belongsTo('App\ServiceCategory', 'category_id', 'id');
    }

    public function hotel_category()
    {
        return $this->belongsTo('App\TypeClass', 'category_id', 'id');
    }
}
