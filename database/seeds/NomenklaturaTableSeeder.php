<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
// use App\Nomenklatura;

class NomenklaturaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::connection('db1')->table('nomenklatura')->insert([
            'uuid'=>'',
            'name'=>'Не выбрано',
            'comment'=>'Не выбрано',
            'created_at'=>date("Y-m-d H:i:s"),
            'created_by'=>1,
            // 'is_deleted'=>0,
            'doc_type_id'=>1,
            'ed_ism_id'=>1,
            'part_num'=>'',
            'manufacturer_id'=>1,
            'artikul'=>'',
            'price'=>0,
            'nds_id'=>1,
            'is_protected'=>true,
        ]);
    }
}
