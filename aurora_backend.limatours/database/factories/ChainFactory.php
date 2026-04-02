<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Chain;
use Faker\Generator as Faker;

$factory->define(Chain::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => 1,
    ];
});
