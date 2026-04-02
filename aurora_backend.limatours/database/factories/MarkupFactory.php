<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Markup;
use Faker\Generator as Faker;

$factory->define(Markup::class, function (Faker $faker) {
    return [
        'holtel' => rand(500, 8000),
        'service' => rand(500, 8000),
        'period' => rand(2000, 2019),
        'status' => $faker->randomElement($array = array(true, false)),
        'client_id' => $factory->create(App\Client::class)->id
    ];
});
