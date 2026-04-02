<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceTranslation extends Model implements Auditable
{
    use SoftDeletes;
    use LogsActivity;
    use \OwenIt\Auditing\Auditable;

    protected static bool $logOnlyDirty = true;

    protected static bool $submitEmptyLogs = false;

    protected static string $logName = 'service_translation';

    protected static array $ignoreChangedAttributes = ['created_at', 'updated_at'];

    protected $fillable = ['name_commercial', 'description', 'itinerary', 'summary', 'language_id'];

    protected array $logAttributes = ['name_commercial', 'description', 'itinerary', 'summary', 'language_id'];

    public function generateTags(): array
    {
        return ['service'];
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function services(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service', 'service_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        // TODO: Implement getActivitylogOptions() method.
    }
}
