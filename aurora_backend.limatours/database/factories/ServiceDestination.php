<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */


use App\ServiceDestination;
use Faker\Generator as Faker;

$factory->define(ServiceDestination::class, function (Faker $faker) {
    return [
        'country_id' => 89,
        'state_id' => 1610,
        'city_id' => 128,
        'zone_id' => 1,
    ];
});
