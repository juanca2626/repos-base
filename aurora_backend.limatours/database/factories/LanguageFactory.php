<?php

use Faker\Generator as Faker;

$factory->define(App\Language::class, function (Faker $faker) {
    return [
        'name' => $faker->iso8601(),
        'iso' => $faker->languageCode()
    ];
});
