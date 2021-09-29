<?php

use Illuminate\Database\Seeder;

class EdIsmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('ed_ism')->insert([
            [
                'id'=>1,
                'uuid'=>'',
                'name'=>'Не выбрано',
                'comment'=>'Не выбрано',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'okei'=>null,
                'is_protected'=>true,
            ],
            [
                'id'=>2,
                'uuid'=>'',
                'name'=>'шт',
                'comment'=>'Штука',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'okei'=> 796,
                'is_protected'=>true,
                ]
        ]);
    }
}
