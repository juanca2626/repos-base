<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class ClientMailing
 * @package App
 */
class ClientMailing extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_mailing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['clients_id', 'weekly', 'day_before', 'daily', 'survey', 'whatsapp', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne('App\Client', 'clients_id');
    }
}
