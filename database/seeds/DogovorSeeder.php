<?php

use Illuminate\Database\Seeder;

class DogovorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('dogovors')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            'kontragent_id'=>1,
            'valuta_id'=>1,
            'firm_id'=>1,
            'dog_type_1с'=>"СПоставщиком",
            'doc_num'=>'',
            'doc_date'=>NULL,
            'end_date'=>NULL,
            'is_write'=>false,
            // 'is_deleted'=>0,
            'is_protected'=>true,
        ]);    }
}
