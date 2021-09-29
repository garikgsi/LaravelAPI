<?php

use Illuminate\Database\Seeder;

class RecipesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::connection('db1')->table('recipes')->insert([
            [
                'id' => 1,
                'name' => 'Не выбрано',
                'nomenklatura_id' => '1',
            ],
        ]);
    }
}
