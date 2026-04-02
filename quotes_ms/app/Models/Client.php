<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Client extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /*
     * Column status
     */

    public const ACTIVE = true;

    public const INACTIVE = true;

    protected $fillable = ['name'];

    public function generateTags(): array
    {
        return ['client'];
    }

    public function markets(): BelongsTo
    {
        return $this->belongsTo('App\Models\Market', 'market_id');
    }

    public function market_regions(): BelongsTo
    {
        return $this->belongsTo('App\Models\MarketRegion', 'market_id', 'market_id');
    }

    public function countries(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    public function languages(): BelongsTo
    {
        return $this->belongsTo('App\Models\Language', 'language_id');
    }

    public function galeries(): HasMany
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id');
    }

    public function markups(): HasMany
    {
        return $this->hasMany('App\Models\Markup', 'client_id');
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

    public function fromHotel(): HasMany
    {
        return $this->hasMany('App\Models\HotelClient', 'client_id', 'id');
    }

    public function fromService(): HasMany
    {
        return $this->hasMany('App\Models\ServiceClient', 'client_id', 'id');
    }

    public function fromTrain(): HasMany
    {
        return $this->hasMany('App\Models\TrainClient', 'client_id', 'id');
    }

    public function service_rate_plans(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\ServiceClientRatePlan', 'service_client_rate_plans', null, 'service_rate_id');
    }

    public function train_rate_plans(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\TrainClientRatePlan', 'train_client_rate_plans', null, 'train_rate_id');
    }

    public function rate_plans(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\ClientRatePlan', 'client_rate_plans', null, 'rate_plan_id');
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Hotel', 'hotel_clients', 'client_id', 'hotel_id');
    }

    public function rateplans($year): BelongsToMany
    {
        //        return $this->belongsToMany('App\Models\RatesPlans', 'client_rate_plans','client_id','rate_plan_id');
        return $this->belongsToMany('App\Models\RatesPlans', 'client_rate_plans', 'client_id', 'rate_plan_id')->where('period', '=', $year);
    }

    public function markup_rateplans(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\RatesPlans', 'markup_rate_plans', 'client_id', 'rate_plan_id');
        //        return $this->belongsToMany('App\Models\RatesPlans', 'client_rate_plans','client_id','rate_plan_id')->where('period', '=', $year);
    }

    public function allotments(): HasMany
    {
        return $this->hasMany('App\Models\Allotment', 'client_id', 'id');
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(ClientSeller::class)
            ->with('user');
    }

    public function client_mailing(): belongsTo
    {
        return $this->belongsTo(ClientMailing::class);
    }

    public function client_sellers(): BelongsToMany
    {
        return $this->belongsToMany('\App\Models\User', 'client_sellers')
            ->withPivot('user_id', 'id');
    }

    public function client_executives(): BelongsToMany
    {
        return $this->belongsToMany('\App\Models\User', 'client_executives')
            ->withPivot('user_id', 'id');
    }

    /* Se elimino la relacion de la tabla client_executives ahora todo es atraves de user_markets */
    /* Listado de usuarios  atraves de user_markets  esto cuando se use se debe de filtar  por tipo user_type_id = 3 que son los ejecutivos */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            'App\Models\User',
            'App\Models\UserMarket',
            'market_id', // Cliente Foreign key on user_markets table... Relacion  Cliente => UserMarket
            'id', // User Foreign key on users table...           Relacion  User => UserMarket
            'market_id', // Local key on clients table... Cliente
            'user_id' // Local key on user_markets  user_markets
        );
    }

    public function vista(): HasMany
    {
        return $this->hasMany('App\Models\Vista', 'client_id', 'id');
    }

    public function markup_hotel(): HasMany
    {
        return $this->hasMany('App\Models\MarkupHotel', 'client_id');
    }

    public function markup_service(): HasMany
    {
        return $this->hasMany('App\Models\MarkupService', 'client_id');
    }

    public function markup_rate_hotel(): HasMany
    {
        return $this->hasMany('App\Models\MarkupRatePlan', 'client_id');
    }

    public function markup_rate_service(): HasMany
    {
        return $this->hasMany('App\Models\ServiceMarkupRatePlan', 'client_id');
    }

    public function service_client(): HasMany
    {
        return $this->hasMany('App\Models\ServiceClient', 'client_id');
    }

    public function hotel_client(): HasMany
    {
        return $this->hasMany('App\Models\HotelClient', 'client_id');
    }

    public function service_client_rate_plans(): belongsTo
    {
        return $this->belongsTo('App\Models\ServiceClientRatePlan', 'client_id');
    }

    public function hotel_client_rate_plans(): belongsTo
    {
        return $this->belongsTo('App\Models\ClientRatePlan', 'client_id');
    }

    public function service_offer(): HasMany
    {
        return $this->hasMany('App\Models\ClientServiceOffer');
    }

    public function service_setting(): HasMany
    {
        return $this->hasMany('App\Models\ClientServiceSetting');
    }

    public function bdm(): hasOne
    {
        return $this->hasOne('App\Models\User', 'id', 'bdm_id');
    }
}
