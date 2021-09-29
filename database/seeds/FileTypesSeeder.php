<?php

use Illuminate\Database\Seeder;

class FileTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('db1')->table('file_types')->insert([
            [
                'id' => 1,
                'comment' => 'Изображение',
                'name' => 'image',
                'is_protected' => 1,
            ],
            [
                'id' => 2,
                'comment' => 'Документ',
                'name' => 'document',
                'is_protected' => 1,
            ],
            [
                'id' => 3,
                'comment' => 'Список',
                'name' => 'list',
                'is_protected' => 1,
            ],
        ]);
    }
}
