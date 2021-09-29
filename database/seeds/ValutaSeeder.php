<?php

use Illuminate\Database\Seeder;

class ValutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('valuta')->insert([
            'id'=>1,
            'uuid'=>'',
            'name'=>'руб.',
            'comment'=>'Рубль',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            'code'=>643,
            // 'is_deleted'=>0,
            'is_protected'=>true,
        ]);
    }
}
