<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Sklad;
use Faker\Generator as Faker;
use Illuminate\Support\Str;


$factory->define(Sklad::class, function (Faker $faker) {
    return [
        "name" => Str::random(10),
        "keeper_id" => 1,
    ];
});