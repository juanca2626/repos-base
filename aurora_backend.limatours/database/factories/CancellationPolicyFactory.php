<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\CancellationPolicy;
use Faker\Generator as Faker;

$factory->define(CancellationPolicy::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->name,
        'hotel_id' => $factory->create(App\Hotel::class)->id
    ];
});
