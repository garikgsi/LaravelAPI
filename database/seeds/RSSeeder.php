<?php

use Illuminate\Database\Seeder;

class RSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::connection('db1')->table('rs')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            's_num'=>'',
            'bank_id'=>1,
            'valuta_id'=>1,
            'rs_table_id'=>1,
            'rs_table_type'=>'NULL',
            // 'is_deleted'=>0,
            'is_protected'=>true,
        ]);
    }
}
