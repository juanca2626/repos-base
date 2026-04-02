<?php

use App\Country;
use App\State;
use Faker\Generator as Faker;

$factory->define(State::class, function (Faker $faker) use ($factory) {
    return [
        'country_id' => $factory->create(Country::class)->id
    ];
});
