<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ManageSearchHotel
{

    public function storeTokenSearchHotels($token_search, $hotels, $minutes)
    {
        Cache::put($token_search, $hotels, now()->addMinutes($minutes));
    }

    public function getHotelsByTokenSearch($token_search)
    {
        $contents = Cache::get($token_search);

        if ($contents !== null) {
            return $contents;
        } else {
            return [
                "error_code" => 1003,
                "error" => trans('validations.reservations.your_search_has_expired')
            ];
        }
    }

    public function getTokenRemainingTime($token_search)
    {
        return app('cache')->expiresAt($token_search);
    }

    public function checkTokenSearch($token_search)
    {
        $contents = Cache::get($token_search);

        if ($contents !== null) {
            return true;
        } else {
            return false;
        }
    }
}
