<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\PoliciesCancelations;
use Faker\Generator as Faker;

$factory->define(PoliciesCancelations::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'hotel_id' => 1
    ];
});
