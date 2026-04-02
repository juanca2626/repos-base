<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Package extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['package'];
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag');
    }

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function physical_intensity()
    {
        return $this->belongsTo('App\PhysicalIntensity');
    }

    public function permissions()
    {
        return $this->hasMany('App\PackagePermission');
    }

    public function translations()
    {
        return $this->hasMany('App\PackageTranslation');
    }

    public function translations_gtm()
    {
        return $this->hasMany('App\PackageTranslation');
    }

    public function itineraries()
    {
        return $this->hasMany('App\PackageItinerary');
    }

    public function itineraries_all()
    {
        return $this->hasMany('App\PackageItinerary');
    }


    public function children()
    {
        return $this->hasMany('App\PackageChild');
    }

    public function schedules()
    {
        return $this->hasMany('App\PackageSchedule');
    }

    public function rates()
    {
        return $this->hasMany('App\PackageRate');
    }

    public function plan_rates()
    {
        return $this->hasMany('App\PackagePlanRate');
    }

    public function plan_rates_service_type()
    {
        return $this->hasMany('App\PackagePlanRate');
    }

    public function package_destinations()
    {
        return $this->hasMany('App\PackageDestination');
    }

    public function extension_recommended()
    {
        return $this->hasMany('App\PackageExtension', 'extension_id');
    }

    public function fixed_outputs()
    {
        return $this->hasMany('App\FixedOutput');
    }

    public function tax()
    {
        return $this->hasMany('App\PackageTax');
    }

    public function galleries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id')
            ->where('type', '=', 'package');
    }

    public function rated()
    {
        return $this->hasMany('App\ClientPackageRated', 'package_id', 'id');
    }

    public function inclusions()
    {
        return $this->hasMany('App\PackageInclusion');
    }

    public function client_package_setting()
    {
        return $this->hasMany('App\ClientPackageSetting', 'package_id');
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

    public function highlights()
    {
        return $this->hasMany('App\PackageHighlight', 'package_id', 'id');
    }

    public function duplicationInfo()
    {
       return $this->hasOne(\App\PackageDuplicationInfo::class, 'package_id', 'id');
    }

    public function getIsProcessingPlanRatesAttribute()
    {
        if (!$this->relationLoaded('duplicationInfo') || $this->duplicationInfo === null) {
            return false;
        }

        return $this->duplicationInfo->isProcessing();
    }
}
