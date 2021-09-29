<?php

use Illuminate\Database\Seeder;

class SiteModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('site_modules')->insert([
            [
                'id' => 2,
                'title' => 'HTML',
                'name' => 'html',
                'view' => '',
                'controller' => '',
                'model' => '',
            ],
            [
                'id' => 3,
                'title' => 'Новости',
                'name' => 'news',
                'view'=> '',
                'controller' => '',
                'model' => '',
            ],
            [
                'id' => 4,
                'title' => 'Статьи',
                'name' => 'stories',
                'view'=> '',
                'controller' => '',
                'model' => '',
            ],
            [
                'id' => 5,
                'title' => 'Каталог',
                'name' => 'catalog',
                'view'=> '',
                'controller' => '',
                'model' => '',
            ],
        ]);
    }
}
