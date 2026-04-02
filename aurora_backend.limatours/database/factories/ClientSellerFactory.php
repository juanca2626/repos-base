<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\App\User;
use App\Client;
use App\ClientSeller;
use Faker\Generator as Faker;

$factory->define(ClientSeller::class, function (Faker $faker) use ($factory) {
    return [
        'status' => $faker->randomElement($array = array(true, false)),
        'client_id' => $factory->create(Client::class)->id,
        'user_id' => $factory->create(User::class)->id
    ];
});
