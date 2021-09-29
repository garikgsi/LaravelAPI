<?php

use Illuminate\Database\Seeder;

class FileDriversSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('file_drivers')->insert([
            [
                'id' => 1,
                'comment' => 'БД',
                'name' => 'local',
                'is_protected' => 1,
            ],
            [
                'id' => 2,
                'comment' => 'Google Drive',
                'name' => 'google',
                'is_protected' => 1,
            ],
        ]);
    }
}
