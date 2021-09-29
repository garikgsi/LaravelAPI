<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionComponentsTable extends Migration
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
        $schema->create('production_components', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('production_item_id')->default(1)->index('production_item_id');
            $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            $table->decimal('kolvo',20,3)->default(1)->index('kolvo');
            $table->decimal('price',20,4)->default(1)->index('price');
            $table->decimal('summa',20,4)->default(1)->index('summa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('production_components');
    }
}
