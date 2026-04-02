<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSheetService extends Model
{
    use SoftDeletes;

    public function day()
    {
        return $this->belongsTo('App\MasterSheetDay', 'master_sheet_day_id', 'id');
    }

}
