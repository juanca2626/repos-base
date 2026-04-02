<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ChargeTypes;
use Faker\Generator as Faker;

$factory->define(ChargeTypes::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});
