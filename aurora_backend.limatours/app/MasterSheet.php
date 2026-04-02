<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSheet extends Model
{
    use SoftDeletes;

    public function users()
    {
        return $this->hasMany('App\MasterSheetUser');
    }

    public function messages()
    {
        return $this->hasMany('App\Message', 'object_id', 'id')
            ->where('messages.entity', 'master_sheet');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
