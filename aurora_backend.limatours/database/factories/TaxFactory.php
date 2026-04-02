<?php

use App\Country;
use App\Tax;
use Faker\Generator as Faker;

$factory->define(Tax::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->randomDigit,
        'country_id' => $factory->create(Country::class)->id
    ];
});
