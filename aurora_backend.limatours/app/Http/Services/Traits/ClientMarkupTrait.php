<?php

namespace App\Http\Services\Traits;

use App\Markup;

trait ClientMarkupTrait
{
    public function getClientMarkup($client_id, $period): ?Markup
    {
        return Markup::where('client_id', $client_id)->where('period', $period)->first();
    }
}
