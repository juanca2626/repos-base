<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequirement extends Model
{
    use SoftDeletes;
    //public $incrementing = true;
}
