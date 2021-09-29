<?php

use Illuminate\Database\Seeder;

class NDSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('nds')->insert([
            [
                'id'=>1,
                'uuid'=>'',
                'name'=>'Не выбрано',
                'comment'=>'Не выбрано',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0
            ],
            [
                'id'=>2,
                'uuid'=>'',
                'name'=>'НДС18',
                'comment'=>'Ставка НДС 18%',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.18
            ],
            [
                'id'=>3,
                'uuid'=>'',
                'name'=>'НДС18_118',
                'comment'=>'Расчетная ставка НДС 18/118',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.1525423729
            ],
            [
                'id'=>4,
                'uuid'=>'',
                'name'=>'НДС10',
                'comment'=>'Ставка НДС 10%',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.10
            ],
            [
                'id'=>5,
                'uuid'=>'',
                'name'=>'НДС10_110',
                'comment'=>'Расчетная ставка НДС 10/110',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.0909090909
            ],
            [
                'id'=>6,
                'uuid'=>'',
                'name'=>'НДС0',
                'comment'=>'Ставка НДС 0%',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0
            ],
            [
                'id'=>7,
                'uuid'=>'',
                'name'=>'БезНДС',
                'comment'=>'БезНДС',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0
            ],
            [
                'id'=>8,
                'uuid'=>'',
                'name'=>'НДС20',
                'comment'=>'Ставка НДС 20%',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.20
            ],
            [
                'id'=>9,
                'uuid'=>'',
                'name'=>'НДС20_120',
                'comment'=>'Расчетная ставка НДС 20/120',
                'created_at'=>date("Y-m-d H:i:s"),
                'created_by'=>1,
                // 'is_deleted'=>0,
                'is_protected'=>true,
                'stavka'=>0.1666666667
            ],
      ]);
    }
}
