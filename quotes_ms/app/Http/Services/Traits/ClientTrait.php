<?php

namespace App\Http\Services\Traits;

use App\Models\Client;

trait ClientTrait
{
    public $_client;

    protected array $type_pax = [
        1 => 'simple',
        2 => 'double',
        3 => 'triple',
    ];


    public function client(): Client
    {
        return $this->_client;
    }

}
