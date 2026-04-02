<?php

use App\Hotel;
use Faker\Generator as Faker;

$factory->define(Hotel::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->name . ' ' . $faker->randomElement(['Hotel', 'Resort', 'Hotel & Spa']),
        'web_site' => $faker->url,
        'status' => 1,
        'latitude' => $faker->latitude(-90, 90),
        'longitude' => $faker->longitude(-180, 180),
        'check_in_time' => $faker->time('H:i:s', 'now'),
        'check_out_time' => $faker->time('H:i:s', 'now'),
        'percentage_completion' => $faker->numberBetween(0, 100),
        'typeclass_id' => 1,
        'chain_id' => 1,
        'currency_id' => 1,
        'hotel_type_id' => 1,
        'hotelcategory_id' => 1,
        'country_id' => 89,
        'state_id' => 1610,
        'city_id' => 128,
        'district_id' => $faker->numberBetween($min = 1, $max = 40),
        'chain_id' => $factory->create(App\Chain::class)->id
    ];
});
