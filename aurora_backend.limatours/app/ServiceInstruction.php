<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceInstruction extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->belongsTo('App\Service','service_id');
    }

    public function instructions()
    {
        return $this->belongsTo('App\Instruction','instruction_id');
    }

}
