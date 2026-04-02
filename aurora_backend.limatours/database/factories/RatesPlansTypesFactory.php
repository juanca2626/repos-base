<?php

/* @var $factory Factory */

use App\RatesPlansTypes;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(RatesPlansTypes::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});
