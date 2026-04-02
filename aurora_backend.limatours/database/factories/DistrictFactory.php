<?php

use App\City;
use App\District;
use Faker\Generator as Faker;

$factory->define(District::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->city,
        'city_id' => $factory->create(City::class)->id
    ];
});
