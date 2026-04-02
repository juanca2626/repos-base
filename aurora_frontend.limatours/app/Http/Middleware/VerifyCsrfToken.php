<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        "logout",
        "order_services_file",
        "cart/service/reservation_time",
        "filter_hotels_file",
        "contents",
        "cart",
        "search_flights",
        "search_passengers",
        "board/*"
    ];
}
