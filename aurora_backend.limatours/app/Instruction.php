<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instruction extends Model
{
    use SoftDeletes;

    public function service_instruction()
    {
        return $this->hasMany('App\ServiceInstruction');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'instruction');
    }

    public function client_instructions()
    {
        return $this->hasMany('App\ClientInstruction');
    }
}
