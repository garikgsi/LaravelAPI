<?php

use Illuminate\Database\Seeder;

class FirmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('firms')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            'kpp'=>'',
            'inn'=>'',
            'reg_date'=>NULL,
            'rs_id'=>1,
            'okpo'=>'',
            'full_name'=>'',
            'short_name'=>'',
            'ogrn'=>'',
            'okved'=>'',
            'okopf'=>'',
            'firstname_ip'=>'',
            'name_ip'=>'',
            'father_name_ip'=>'',
            'dop_okved'=>'',
            'is_protected'=>true,
        ]);
    }
}
