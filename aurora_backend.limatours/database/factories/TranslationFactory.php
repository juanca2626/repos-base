<?php

use App\Language;
use App\Translation;
use Faker\Generator as Faker;

$factory->define(Translation::class, function (Faker $faker) use ($factory) {
    return [
        'type' => $faker->randomElement(["h", "r"]),
        'object_id' => $faker->randomDigit,
        'slug' => $faker->slug,
        'value' => $faker->sentence,
        'language_id' => $factory->create(Language::class)->id
    ];
});
