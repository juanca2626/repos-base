<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Package extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['package'];
    }

    public function tag(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Tag');
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function physical_intensity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\PhysicalIntensity');
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackagePermission');
    }

    public function translations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageTranslation');
    }

    public function itineraries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageItinerary');
    }

    public function itineraries_all(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageItinerary');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageChild');
    }

    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageSchedule');
    }

    public function rates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageRate');
    }

    public function plan_rates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackagePlanRate');
    }

    public function plan_rates_service_type(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackagePlanRate');
    }

    public function package_destinations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageDestination');
    }

    public function extension_recommended(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageExtension', 'extension_id');
    }

    public function fixed_outputs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\FixedOutput');
    }

    public function tax(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageTax');
    }

    public function galleries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id');
    }

    public function rated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\ClientPackageRated', 'package_id', 'id');
    }

    public function inclusions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageInclusion');
    }

    public function client_package_setting(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\ClientPackageSetting', 'package_id');
    }

    public function getInformationPackages(array $ids = null, $language_id = 1, $first = false)
    {
        $packages = self::select([
            'id',
            'code',
            'nights',
            'country_id',
            'extension',
            'map_link',
            'image_link',
            'physical_intensity_id',
            'reference',
            'tag_id',
            'destinations',
        ])
            ->with([
                'package_destinations.state.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'state');
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'tag.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'tag');
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'galleries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'package');
                },
            ])
            ->where('status', 1)->whereIn('id', $ids)->get();

        return $first ? $packages->first() : $packages;
    }

    public function highlights(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PackageHighlight', 'package_id', 'id');
    }
}
