<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::insert([
            [
                'id'=>1,
                'name'=>'guest',
                'comment'=>'Гость',
                'guard_name'=>'api'
            ],
            [
                'id'=>2,
                'name'=>'super admin',
                'comment'=>'Администратор с возможностью управления правами доступа',
                'guard_name'=>'api'
            ],
            [
                'id'=>3,
                'name'=>'admin',
                'comment'=>'Администратор',
                'guard_name'=>'api'
            ],
            [
                'id'=>4,
                'name'=>'manager',
                'comment'=>'Менеджер',
                'guard_name'=>'api'
            ],
            [
                'id'=>5,
                'name'=>'top',
                'comment'=>'Директор',
                'guard_name'=>'api'
            ],
            [
                'id'=>6,
                'name'=>'keeper',
                'comment'=>'Кладовщик',
                'guard_name'=>'api'
            ],
            [
                'id'=>7,
                'name'=>'webmaster',
                'comment'=>'Контент-менеджер сайта и SEO-оптимизатор',
                'guard_name'=>'api'
            ],
        ]);
    }
}
