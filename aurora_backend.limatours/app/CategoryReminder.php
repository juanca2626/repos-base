<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryReminder extends Model
{
    //
    public function times()
    {
        return $this->hasMany('App\TimeReminder', 'category_id', 'id')
            ->orderBy('time', 'ASC');
    }
}
