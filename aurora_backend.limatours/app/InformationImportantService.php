<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class InformationImportantService extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['informationImportantService'];
    }

    public function service_info_important()
    {
        return $this->hasMany('App\ServiceInformationImportant');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'information_important_service');
    }

    public function client_info_important()
    {
        return $this->hasMany('App\ClientInformationImportantService','info_important_service_id');
    }
}
