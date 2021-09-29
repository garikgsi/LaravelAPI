<?php

use Illuminate\Database\Seeder;

class DocTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('doc_types')->insert([
            [
                'id'=>1,
                'uuid'=>'',
                'name'=>'Не выбрано',
                'comment'=>'Не выбрано',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                'is_protected'=>true,
            ],
            [
                'id'=>2,
                'uuid'=>'',
                'name'=>'Товары',
                'comment'=>'Товары',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                'is_protected'=>true,
            ],
            [
                'id'=>3,
                'uuid'=>'',
                'name'=>'Услуги',
                'comment'=>'Услуги',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                'is_protected'=>true,
            ],
            [
                'id'=>4,
                'uuid'=>'',
                'name'=>'Материалы',
                'comment'=>'Материалы',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                'is_protected'=>true,
            ],
        ]);
    }
}
