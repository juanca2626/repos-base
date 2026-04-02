<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceTranslation extends Model implements Auditable
{
    use SoftDeletes, LogsActivity,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logName = 'service_translation';

    protected static $ignoreChangedAttributes = ['created_at', 'updated_at'];

    protected $fillable = ['name_commercial', 'description', 'itinerary', 'summary', 'language_id'];
    protected $logAttributes = ['name_commercial', 'description', 'itinerary', 'summary', 'language_id'];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function services()
    {
        return $this->belongsTo('App\Service','service_id');
    }
}
