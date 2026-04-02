<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Service;
use Faker\Generator as Faker;

$factory->define(Service::class, function (Faker $faker) {
    return [
        'aurora_code' => $faker->unique()->randomNumber(6),
        'name' => $faker->name(),
        'currency_id' => 1,
        'latitude' => $faker->latitude(-90, 90),
        'longitude' => $faker->longitude(-180, 180),
        'qty_reserve' => $faker->numberBetween($min = 1, $max = 40),
        'equivalence_aurora' => $faker->randomNumber(6),
        'affected_igv' => 1,
        'apply_igv' => 1,
        'igv' => 18,
        'allow_guide' => 1,
        'allow_child' => 1,
        'allow_infant' => $faker->boolean($chanceOfGettingTrue = 50),
        'limit_confirm_hours' => $faker->numberBetween(1, 24),
        'infant_min_age' => 0,
        'infant_max_age' => 5,
        'include_accommodation' => $faker->boolean($chanceOfGettingTrue = 50),
        'unit_id' => 1,
        'unit_duration_id' => 1,
        'service_type_id' => $faker->numberBetween(1, 2),
        'classification_id' => 1,
        'service_sub_category_id' => 1,
        'user_id' => 1,
        'date_solicitude' => $faker->dateTime(),
        'stela_code' => $faker->unique()->randomNumber(6),
        'pax_min' => 1,
        'pax_max' => $faker->numberBetween($min = 1, $max = 20),
        'min_age' => $faker->numberBetween($min = 1, $max = 50),
        'equivalence_stela' => $faker->randomNumber(6),
        'require_itinerary' => false,
        'require_image_itinerary' => false,
        'status' => true,
    ];
});
