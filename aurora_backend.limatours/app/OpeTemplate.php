<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpeTemplate extends Model
{
    use SoftDeletes;

    public function content_extension()
    {
        return $this->hasOne('App\OpeTemplateContent', 'ope_template_id', 'id')
            ->whereNotNull('content_wsp');
    }

    public function contents()
    {
        return $this->hasMany('App\OpeTemplateContent', 'ope_template_id', 'id');
    }
}
