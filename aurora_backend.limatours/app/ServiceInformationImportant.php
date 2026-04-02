<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceInformationImportant extends Model
{
    use SoftDeletes;
    protected $table = 'service_information_important';

    public function services()
    {
        return $this->belongsTo('App\Service','service_id');
    }

    public function featured()
    {
        return $this->belongsTo('App\InformationImportantService','info_important_service_id');
    }

}
