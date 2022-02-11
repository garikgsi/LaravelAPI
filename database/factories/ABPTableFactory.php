<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ABPTable;
use Faker\Generator as Faker;

$factory->define(\App\ABPTable::class, function (Faker $faker) {
    return [
        "comment" => "test comment"
    ];
});