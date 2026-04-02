<?php


use Faker\Generator as Faker;

$factory->define(App\Country::class, function (Faker $faker) {
    return [
        'local_tax' => $faker->boolean,
        'foreign_tax' => $faker->boolean,
        'local_service' => $faker->boolean,
        'foreign_service' => $faker->boolean
    ];
});
