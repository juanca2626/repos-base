<?php

use App\City;
use App\State;
use Faker\Generator as Faker;

$factory->define(City::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->city,
        'state_id' => $factory->create(State::class)->id
    ];
});
