<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersSeeder::class);
        $this->call(DocTypeSeeder::class);
        $this->call(EdIsmSeeder::class);
        $this->call(ManufacturersSeeder::class);
        $this->call(NomenklaturaTableSeeder::class);
        $this->call(NDSSeeder::class);
        $this->call(KontragentSeeder::class);
        $this->call(FirmSeeder::class);
        $this->call(BankSeeder::class);
        $this->call(SkladSeeder::class);
        $this->call(DogovorSeeder::class);
        $this->call(FizLicoSeeder::class);
        $this->call(RSSeeder::class);
        $this->call(ValutaSeeder::class);
        $this->call(SiteMenuSeeder::class);
        $this->call(SiteModulesSeeder::class);
        $this->call(FileDriversSeeder::class);
        $this->call(FileTypesSeeder::class);

    }
}
