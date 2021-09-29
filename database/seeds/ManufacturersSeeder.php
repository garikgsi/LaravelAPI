<?php

use Illuminate\Database\Seeder;

class ManufacturersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('manufacturers')->insert([
            [
                'id'=>1,
                'uuid'=>'',
                'name'=>'Не выбрано',
                'comment'=>'Не выбрано',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                ]
        ]);
    }
}
