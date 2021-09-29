<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkladMovesTable extends Migration
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
        $schema->create('sklad_moves', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();
            $table->documentFields();

            $table->bigInteger('firm_id')->default(1)->index('firm_id');
            $table->bigInteger('sklad_out_id')->default(1)->index('sklad_out_id');
            $table->bigInteger('sklad_in_id')->default(1)->index('sklad_in_id');
            $table->boolean('is_out')->default(0)->index('is_out');
            $table->boolean('is_in')->default(0)->index('is_in');
            $table->morphs("transitable");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('sklad_moves');
    }
}
