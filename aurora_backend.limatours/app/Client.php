<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    /*
     * Column status
     */

    const ACTIVE = true;
    const INACTIVE = true;

    protected $fillable = ['name'];

    public function generateTags(): array
    {
        return ['client'];
    }

    public function markets()
    {
        return $this->belongsTo('App\Market', 'market_id');
    }

    public function market_regions()
    {
        return $this->belongsTo('App\MarketRegion', 'market_id', 'market_id');
    }

    public function countries()
    {
        return $this->belongsTo('App\Country', 'country_id');
    }

    public function languages()
    {
        return $this->belongsTo('App\Language', 'language_id');
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }

    public function markups()
    {
        return $this->hasMany('App\Markup', 'client_id');
    }

    public function scopeSearch($query, $target)
    {
        if ($target != '') {
            return $query->where('clients.name', 'like', "%$target%")->orWhere('clients.code', 'like', "%$target%");
        }
    }
    public function scopeMarket($query, $target)
    {
        if ($target != '0' and $target != null) {
            return $query->where('clients.market_id', '=', "$target");
        }
    }
    public function scopeStatus($query, $target)
    {
        if ($target != '') {
            return $query->where('clients.status', "$target");
        }
    }

    public function fromHotel()
    {
        return $this->hasMany('App\HotelClient', 'client_id', 'id');
    }

    public function fromService()
    {
        return $this->hasMany('App\ServiceClient', 'client_id', 'id');
    }

    public function fromTrain()
    {
        return $this->hasMany('App\TrainClient', 'client_id', 'id');
    }

    public function service_rate_plans()
    {
        return $this->belongsToMany('App\ServiceClientRatePlan', 'service_client_rate_plans', null,'service_rate_id');
    }

    public function train_rate_plans()
    {
        return $this->belongsToMany('App\TrainClientRatePlan', 'train_client_rate_plans', null,'train_rate_id');
    }

    public function rate_plans()
    {
        return $this->belongsToMany('App\ClientRatePlan', 'client_rate_plans', null,'rate_plan_id');
    }


    public function hotels()
    {
        return $this->belongsToMany('App\Hotel', 'hotel_clients','client_id','hotel_id');
    }

    public function rateplans($year)
    {
//        return $this->belongsToMany('App\RatesPlans', 'client_rate_plans','client_id','rate_plan_id');
        return $this->belongsToMany('App\RatesPlans', 'client_rate_plans','client_id','rate_plan_id')->where('period', '=', $year);
    }

    public function markup_rateplans()
    {
        return $this->belongsToMany('App\RatesPlans', 'markup_rate_plans','client_id','rate_plan_id');
//        return $this->belongsToMany('App\RatesPlans', 'client_rate_plans','client_id','rate_plan_id')->where('period', '=', $year);
    }

    public function allotments()
    {
        return $this->hasMany('App\Allotment', 'client_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function sellers()
    {
        return $this->hasMany(ClientSeller::class)
            ->with('user');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client_mailing()
    {
        return $this->belongsTo('App\ClientMailing', 'id', 'clients_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function client_sellers()
    {
        return $this->belongsToMany('\App\User', 'client_sellers')
            ->withPivot('user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function client_executives()
    {
        return $this->belongsToMany('\App\User', 'client_executives')
            ->wherePivot('deleted_at', null)
            ->where('users.user_type_id', 3)
            ->withPivot('user_id', 'id');
    }

    /* Se elimino la relacion de la tabla client_executives ahora todo es atraves de user_markets */
    /* Listado de usuarios  atraves de user_markets  esto cuando se use se debe de filtar  por tipo user_type_id = 3 que son los ejecutivos */
    public function users()
    {
        return $this->hasManyThrough(
            'App\User',
            'App\UserMarket',
            'market_id', // Cliente Foreign key on user_markets table... Relacion  Cliente => UserMarket
            'id', // User Foreign key on users table...           Relacion  User => UserMarket
            'market_id', // Local key on clients table... Cliente
            'user_id' // Local key on user_markets  user_markets
        );
    }

    public function vista()
    {
        return $this->hasMany('App\Vista', 'client_id', 'id');
    }

    public function markup_hotel()
    {
        return $this->hasMany('App\MarkupHotel', 'client_id');
    }

    public function markup_service()
    {
        return $this->hasMany('App\MarkupService', 'client_id');
    }

    public function markup_rate_hotel()
    {
        return $this->hasMany('App\MarkupRatePlan', 'client_id');
    }

    public function markup_rate_service()
    {
        return $this->hasMany('App\ServiceMarkupRatePlan', 'client_id');
    }

    public function service_client()
    {
        return $this->hasMany('App\ServiceClient', 'client_id');
    }

    public function hotel_client()
    {
        return $this->hasMany('App\HotelClient', 'client_id');
    }

    public function service_client_rate_plans()
    {
        return $this->belongsTo('App\ServiceClientRatePlan', 'client_id');
    }

    public function hotel_client_rate_plans()
    {
        return $this->belongsTo('App\ClientRatePlan', 'client_id');
    }

    public function service_offer()
    {
        return $this->hasMany('App\ClientServiceOffer');
    }

    public function service_setting()
    {
        return $this->hasMany('App\ClientServiceSetting');
    }

    public function bdm()
    {
        return $this->hasOne('App\User', 'id', 'bdm_id');
    }

    public function configuration()
    {
        return $this->belongsTo('App\ClientConfiguration', 'id', 'client_id');
    }

    public function businessRegions()
    {
        return $this->belongsToMany(BusinessRegion::class, 'business_region_client', 'client_id', 'business_region_id')
                    ->whereNull('business_region_client.deleted_at')
                    ->withTimestamps();
    }

    public function syncBusinessRegionsWithRestore(array $regions)
    {
        // Primero sincroniza normalmente
        $this->businessRegions()->sync($regions);

        // Luego restaura los registros eliminados
        DB::table('business_region_client')
            ->where('client_id', $this->id)
            ->whereIn('business_region_id', $regions)
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null]);
    }
}
