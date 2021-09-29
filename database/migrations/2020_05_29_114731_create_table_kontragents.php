<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableKontragents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = DB::connection('db1')->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });
        $schema->create('kontragents', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->string('full_name',512)->nullable()->default('')->index('full_name');
            $table->enum('type',["ЮридическоеЛицо","ФизическоеЛицо"])->default("ЮридическоеЛицо")->index('type');
            $table->string('kpp',32)->nullable()->default('')->index('kpp');
            $table->string('inn',32)->nullable()->default('')->index('inn');
            $table->string('okpo',16)->nullable()->default('')->index('okpo');
            $table->string('passport',255)->nullable()->default('')->index('passport');
            $table->bigInteger('rs_id')->default(1)->index('rs_id');
            $table->string('ogrn',32)->nullable()->default('')->index('ogrn');
            $table->string('svid_num',64)->nullable()->default('')->index('svid_num');
            $table->date('svid_date')->nullable()->index('svid_date');
            $table->date('reg_date')->nullable()->index('reg_data');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('kontragents');
    }
}
