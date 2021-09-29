<?php

use Illuminate\Database\Seeder;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('notification_types')->insert([
            [
                'id' => 1,
                'name' => 'Не выбрано',
                'color' => '',
                'is_protected' => '1'
            ],
            [
                'id' => 2,
                'name' => 'уведомление',
                'color' => 'success',
                'is_protected' => '1'
            ],
            [
                'id' => 3,
                'name' => 'предупреждение',
                'color' => 'warning',
                'is_protected' => '1'
            ],
            [
                'id' => 4,
                'name' => 'важное сообщение',
                'color' => 'alert',
                'is_protected' => '1'
            ],
        ]);
    }
}
