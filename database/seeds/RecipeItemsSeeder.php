<?php

use Illuminate\Database\Seeder;

class RecipeItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('recipe_items')->insert([
            [
                'id' => 1,
                'name' => 'Не выбрано',
                'recipe_id' => 1,
                'nomenklatura_id' => '1',
                'kolvo' => 0
            ],
        ]);
    }
}
