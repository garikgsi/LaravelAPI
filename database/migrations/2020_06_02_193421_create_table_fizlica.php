<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFizlica extends Migration
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
        $schema->create('fizlica', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->date('birthday')->nullable()->index('birthday');
            $table->enum('gender',["Мужской","Женский"])->default("Мужской")->index('gender');
            $table->string('inn',32)->nullable()->default('')->index('inn');
            $table->string('snils',32)->nullable()->default('')->index('snils');
            $table->string('birth_place',512)->nullable()->default('')->index('birth_place');
            $table->string('fio',255)->nullable()->default('')->index('fio');
            $table->bigInteger('rs_id')->default(1)->index('rs_id');
            $table->string('firstname',128)->nullable()->default('')->index('firstname');
            $table->string('namefl',128)->nullable()->default('')->index('namefl');
            $table->string('fathername',128)->nullable()->default('')->index('fathername');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('fizlica');
    }

}
