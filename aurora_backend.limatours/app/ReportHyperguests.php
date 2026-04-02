<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportHyperguests extends Model
{

    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_FAILED = 'FAILED';

    use SoftDeletes;

    // protected $table = 'report_hyperguest';

   protected $fillable = [
       'month',
       'year',
       'fee',
       'total_hyperguest',
       'fees_hyperguest',
       'total_aurora',
       'fees_aurora',
       'status',
       'total_rows',
       'processed_rows',
       'error_message',
       'file_path'
   ];

}
