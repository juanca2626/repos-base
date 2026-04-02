<?php

namespace App\Observers;

use App\Client;
use App\Jobs\SyncNewClientRestrictions;

class ClientObserver
{
    /**
     * Handle the client "created" event.
     *
     * @param  \App\Client  $client
     * @return void
     */
    public function created(Client $client)
    {
        SyncNewClientRestrictions::dispatch($client->id);
    }
}
