<?php

use Illuminate\Database\Seeder;

class FizLicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('fizlica')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            'birthday'=>NULL,
            'gender'=>"Мужской",
            'inn'=>'',
            'snils'=>'',
            'birth_place'=>'',
            'fio'=>'',
            'rs_id'=>1,
            'firstname'=>'',
            'namefl'=>'',
            'fathername'=>'',
            'is_protected'=>true,
        ]);
    }
}
