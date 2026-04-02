<?php

/* @var $factory Factory */

use App\Room;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Room::class, function (Faker $faker) {
    return [
        'max_capacity' => $faker->randomDigit,
        'min_adults' => $faker->randomDigit,
        'max_adults' => $faker->randomDigit,
        'max_child' => $faker->randomDigit,
        'max_infants' => $faker->randomDigit,
        'min_inventory' => $faker->randomDigit,
        'state' => true,
        'hotel_id' => $faker->numberBetween(1, 10),
        'room_type_id' => 1
    ];
});
