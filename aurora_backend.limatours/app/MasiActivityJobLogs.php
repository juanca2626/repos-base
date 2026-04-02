<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasiActivityJobLogs extends Model
{
    public function detail()
    {
        return $this->hasOne('App\MasiFileDetail', 'file', 'file');
    }

    public function reservation()
    {
        return $this->hasOne('App\Reservation', 'file_code', 'file')
            ->with(['executive.markets']);
    }

    public function job()
    {
        return $this->hasOne('App\MasiActivityJobLogs', 'file', 'file');
    }
}
