<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelBackup extends Model
{
    protected $fillable = ['year', 'value','hotel_id'];

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }

}
