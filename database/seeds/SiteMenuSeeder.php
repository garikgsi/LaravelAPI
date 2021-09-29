<?php

use Illuminate\Database\Seeder;

class SiteMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('site_menu_points')->insert([
            'id' => 2,
            'name' => 'Корень сайта',
            'content' => '',
            'parent_menu_point'=> 0,
            'num_order' => 1,
            'surl' => '/',
            'module_id' => 2,
            'is_show_in_menu' => true
        ]);
    }
}
