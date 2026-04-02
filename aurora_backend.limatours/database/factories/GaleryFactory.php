<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Galery;
use Faker\Generator as Faker;

$factory->define(Galery::class, function (Faker $faker) {
    return [
        'type' => $faker->randomElement(['hotel', 'room', 'client']),
        'object_id' => $faker->randomDigit,
        'url' => $faker->imageUrl(40, 40),
        'position' => $faker->unique()->randomDigit,
        'state' => $faker->numberBetween(0, 1)
    ];
});
