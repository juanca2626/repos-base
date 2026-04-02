<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Client;
use App\Market;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) use ($factory) {
    return [
        'code' => rand(5, 20),
        'name' => $faker->name,
        'status' => $faker->boolean,
        'credit_line' => rand(5, 20),
        'have_credit' => true,
        'market_id' => $factory->create(Market::class)->id
    ];
});
