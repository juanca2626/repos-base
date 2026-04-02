<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Contact;
use Faker\Generator as Faker;

$factory->define(Contact::class, function (Faker $faker) use ($factory) {
    return [
        'name' => $faker->name,
        'surname' => $faker->name,
        'lastname' => $faker->name,
        'position' => $faker->city,
        'status' => $faker->randomElement($array = array(true, false)),
        'hotel_id' => $factory->create(App\Hotel::class)->id
    ];
});
