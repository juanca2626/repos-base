<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Client;
use App\Hotel;
use App\HotelClient;
use Faker\Generator as Faker;

$factory->define(HotelClient::class, function (Faker $faker) use ($factory) {
    return [
        'markup' => 5,
        'period' => rand(2000, 2019),
        'client_id' => $factory->create(Client::class)->id,
        'hotel_id' => $factory->create(Hotel::class)->id
    ];
});
