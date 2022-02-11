<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Nomenklatura;
use App\DocType;
use App\Manufacturer;
use App\NDS;
use App\EdIsm;
use Faker\Generator as Faker;
use Illuminate\Support\Str;


$attribues = ['test' => 'ok'];

$factory->define(Nomenklatura::class, function (Faker $faker) /*use ($doc_types, $manufacturers, $nds, $ed_ism) */ {
    $doc_types = DocType::select('id')->get()->pluck('id');
    $manufacturers = Manufacturer::select('id')->get()->pluck('id');
    $nds = NDS::where('name', 'БезНДС')->orWhere('name', 'НДС20')->get()->pluck('id');
    $ed_ism = EdIsm::select('id')->get()->pluck('id');
    $res = [
        "name" => Str::random(10),
        "doc_type_id" => $doc_types->random(), //"Вид номенклатуры"
        "ed_ism_id" => $ed_ism->random(), //"Единица измерения"
        "description" => Str::random(100), //"Описание"
        "part_num" => Str::random(10), //"Part №"
        "manufacturer_id" => $manufacturers->random(), //"Производитель"
        "artikul" => Str::random(5), //"Артикул"
        "price" => $faker->randomFloat(2), // "Цена без НДС"
        "nds_id" => $nds->random(), //"Ставка НДС"
        "is_usluga" => 0, //"Услуга"
    ];
    return $res;
});

$factory->afterMaking(Nomenklatura::class, function ($nomenklatura, $faker) {
    dd($nomenklatura->toArray());
});

// $factory->state(Nomenklatura::class, 'usluga', [
//     "is_usluga" => 1
// ]);