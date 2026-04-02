<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class ClientMailing
 */
class ClientMailing extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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

    public function generateTags(): array
    {
        return ['client'];
    }

    public function client(): HasOne
    {
        return $this->hasOne('App\Models\Client', 'clients_id');
    }
}
