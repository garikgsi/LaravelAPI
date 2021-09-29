<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSotrudniksTable extends Migration
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
        $schema->create('sotrudniks', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->date('birthday')->nullable()->index('birthday');
            $table->enum('gender',["Мужской","Женский"])->default("Мужской")->index('gender');
            $table->string('inn',32)->nullable()->default('')->index('inn');
            $table->string('snils',32)->nullable()->default('')->index('snils');
            $table->string('first_name',255)->nullable()->default('')->index('fio');
            $table->string('patronymic',255)->nullable()->default('')->index('patronymic');
            $table->string('sure_name',255)->nullable()->default('')->index('sure_name');
            $table->boolean('fired')->default(0)->index('fired');
            $table->bigInteger('firm_position_id')->default(1)->index('firm_position_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('sotrudniks');
    }
}
