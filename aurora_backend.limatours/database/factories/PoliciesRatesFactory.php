<?php

/* @var $factory Factory */

use App\PoliciesRates;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(PoliciesRates::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->words(3, true),
        'status' => true,
        'days_apply' => $faker->word,
        'max_ab_offset' => 0,
        'min_ab_offset' => 0,
        'min_length_stay' => 0,
        'max_length_stay' => 0,
        'max_occupancy' => 0,
        'policies_cancelation_id' => 1,
        'hotel_id' => 1,

    ];
});
