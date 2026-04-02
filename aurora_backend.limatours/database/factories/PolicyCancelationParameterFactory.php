<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\PolicyCancelationParameter;
use Faker\Generator as Faker;

$factory->define(PolicyCancelationParameter::class, function (Faker $faker) use ($factory) {
    return [
        'min_day' => $faker->numberBetween(0, 10),
        'max_day' => $faker->numberBetween(0, 10),
        'policy_cancellation_id' => $factory->create(Country::class)->id,
        'penalty_id' => $faker->numberBetween(1, 3)
    ];
});
