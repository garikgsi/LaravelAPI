<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerialNumsTable extends Migration
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
        $schema->create('serial_nums', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->string('number',64)->nullable()->index('number');
            $table->date('end_guarantee')->nullable()->default(null)->index('end_guarantee');
            $table->nullableMorphs("seriable");
            // $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            // $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            // $table->boolean('is_active')->default(0)->index('is_active');
            // $table->boolean('on_store')->default(0)->index('on_store');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('serial_nums');
    }
}
