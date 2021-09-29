<?php

use Illuminate\Database\Seeder;

class KontragentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::connection('db1')->table('kontragents')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            'full_name'=>'',
            'type'=>'ЮридическоеЛицо',
            'inn'=>'',
            'kpp'=>'',
            'okpo'=>'',
            'passport'=>'',
            'rs_id'=>1,
            'ogrn'=>'',
            'svid_num'=>'',
            'svid_date'=>NULL,
            'reg_date'=>NULL,
            'is_protected'=>true,
        ]);

    }
}
