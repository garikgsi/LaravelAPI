<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->define(\App\EdIsm::class, function (Faker $faker, $params) {
    $res = [
        "name" => $faker->words(1, true),
        // "comment" => $faker->words(1, true),
        "okei" => $faker->unique()->numberBetween(100, 999)
    ];
    // $res = array_merge(factory(\App\ABPTable::class)->raw(), $res, $params);
    $res = array_merge($res, $params);
    return $res;
});