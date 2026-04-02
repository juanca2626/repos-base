<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Penalty;
use Faker\Generator as Faker;

$factory->define(Penalty::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->numberBetween(0, 1)
    ];
});
