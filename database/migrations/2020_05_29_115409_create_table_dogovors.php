<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDogovors extends Migration
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
        $schema->create('dogovors', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('kontragent_id')->default(1)->index('kontragent_id');
            $table->bigInteger('valuta_id')->default(1)->index('valuta_id');
            $table->bigInteger('firm_id')->default(1)->index('firm_id');
            $table->enum('dog_type_1с',["СПоставщиком","СЗаказчиком"])->default("СПоставщиком")->index('dog_type_1с');
            $table->string('doc_num',32)->nullable()->default('')->index('doc_num');
            $table->date('doc_date')->nullable()->index('doc_date');
            $table->date('end_date')->nullable()->index('end_date');
            $table->boolean('is_write')->default(false)->index('is_write');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('dogovors');
    }
}
