<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasiFileDetail extends Model
{
    protected $table = 'masi_file_detail';

    public function notifications()
    {
        return $this->hasMany('App\MasiActivityJobLogs', 'file', 'file');
    }

    public function reservation()
    {
        return $this->hasOne('App\Reservation', 'file_code', 'file')
            ->with(['executive']);
    }
}
