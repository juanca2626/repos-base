<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteMerge extends Model
{
    use SoftDeletes;
    protected $table = 'quote_merge';
}
