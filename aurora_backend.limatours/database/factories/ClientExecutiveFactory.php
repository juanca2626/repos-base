<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ClientExecutive;
use Faker\Generator as Faker;

$factory->define(ClientExecutive::class, function (Faker $faker) use ($factory) {
    return [
        'status' => $faker->randomElement($array = array(true, false)),
        'client_id' => $factory->create(Client::class)->id,
        'user_id' => $factory->create(User::class)->id
    ];
});
