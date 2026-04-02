<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Market::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->randomElement($array = array(true, false)),
    ];
});
