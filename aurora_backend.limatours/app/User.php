<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasRoleAndPermission;

    CONST STATUS_ACTIVE = 1;
    CONST STATUS_INACTIVE = 0;
    const USER_TYPE_CLIENT = 1;
    const USER_TYPE_SUPPLIER = 2;
    const USER_TYPE_EMPLOYEE = 3;
    const USER_TYPE_SELLER = 4;

    protected $password_in = '';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'language_id',
        'user_type_id',
        'password',
        'password',
        'count_login',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['client_seller.client.markets'];

    public function setPassword($password_in)
    {
        $this->password_in = $password_in;
    }

    public function getPassword()
    {
        return $this->password_in;
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id');
    }

    public function executive_substitutes()
    {
        return $this->hasMany('App\ExecutiveSubstitute');
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }

    public function hotelUsers()
    {
        return $this->hasMany('App\HotelUser');
    }

    public function train_users()
    {
        return $this->hasMany('App\TrainUser');
    }

    public function clientUsers()
    {
        return $this->hasMany('App\ClientSeller');
    }

    public function hotels()
    {
        return $this->belongsToMany('\App\Hotel', 'hotel_users')
            ->withPivot('hotel_id', 'id');
    }

    public function markets()
    {
        return $this->belongsToMany('\App\Market', 'user_markets')
            ->withPivot('market_id', 'id');
    }

    public function channels()
    {
        return $this->belongsToMany('\App\Channel', 'channel_users')
            ->withPivot('channel_id', 'id');
    }

    public function channelUsers()
    {
        return $this->hasMany('App\ChannelUser');
    }

    public function login_logs()
    {
        return $this->hasMany('App\LoginLog');
    }

    public function client_seller()
    {
        return $this->hasOne('App\ClientSeller');
    }

    public function language()
    {
        return $this->belongsTo('App\Language', 'language_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientSellers()
    {
        return $this->belongsToMany('\App\Client', 'client_sellers')
            ->withPivot('client_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientExecutives()
    {
        return $this->belongsToMany('\App\Client', 'client_executives')
            ->withPivot('client_id', 'id');
    }

    /* Se elimino la relacion de la tabla client_executives ahora todo es atraves de user_markets */
    /* Listado de clientes atraves de user_markets */
    public function clients()
    {
        return $this->hasManyThrough(
            'App\Client',
            'App\UserMarket',
            'user_id', // Foreign key on user_markets table... Relacion  User => UserMarket
            'market_id', // Foreign key on clients table...  Relacion  Client => UserMarket
            'id', // Local key on users table...    Users
            'market_id' // Local key on user_markets table... Clients
        );
    }

    public function clientExecutive()
    {
        return $this->hasMany('App\ClientExecutive');
    }

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Override the mail body for reset password notification mail.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
    }

    public function userType()
    {
        return $this->belongsTo('App\UserTypes', 'user_type_id');
    }

    public function scopeSearch($query, $target)
    {
        if ($target != '') {
            return $query->where('name', 'like', "%$target%")->orWhere('code', 'like', "%$target%");
        }
    }

    public function scopeSearchExecutives($query, $target)
    {
        if ($target != '') {
            return $query->where('user_type_id',3)->where('name', 'like', "%$target%")->orWhere('code', 'like', "%$target%");
        }
    }

    public function scopeTypeUser($query, $target)
    {
        if ($target != '') {
            return $query->where('user_type_id', "$target");
        }
    }

    /**
     * Method to filter by categories
     * @param  Query  $query
     * @param $hotel_id
     * @return Query
     */
    public function scopeHotel($query, $hotel_id)
    {
        if ($hotel_id != '') {
            $chain_id = Hotel::where('id', $hotel_id)->first()->chain_id;
            $hotels = Hotel::where('chain_id', $chain_id)->get()->pluck('id');

            return $query->whereHas('hotels', function ($query) use ($hotels) {
                $query->whereIn('hotel_id', $hotels);
            });
        }
    }

    public function scopeMarket($query, $market)
    {
        if ($market != '') {
            return $query->whereHas('markets', function ($query) use ($market) {
                $query->where('market_id', $market);
            });
        }
    }

    public function scopeChannel($query, $channel_id)
    {
        if ($channel_id != '') {
            return $query->whereHas('channels', function ($query) use ($channel_id) {
                $query->where('channel_id', $channel_id);
            });
        }
    }

    public function scopeClientSeller($query, $client_id)
    {
        if ($client_id != '') {
            return $query->whereHas('clientUsers', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        }
    }

    public function scopeClientExecutive($query, $client_id)
    {
        if ($client_id != '') {
            return $query->whereHas('clientExecutive', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        }
    }

    public function scopeSearchExecutive($query, $target, $status)
    {
        if ($status != '') {
            $query->whereHas('clientExecutive', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        if ($target != '') {
            $query->where('name', 'like', "%$target%");
        }

        return $query;
    }

    public function scopeSearchSeller($query, $target, $status)
    {
        if ($status != '') {
            $query->whereHas('clientUsers', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        if ($target != '') {
            $query->where('name', 'like', "%$target%");
        }
        return $query;
    }

    public function employee()
    {
        return $this->hasOne('App\Employee', 'user_id');
    }

    public function role_user()
    {
        return $this->hasOne('App\RoleUser', 'user_id', 'id');
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function businessRegions()
    {
        return $this->belongsToMany('App\BusinessRegion', 'business_region_user');
    }
}
