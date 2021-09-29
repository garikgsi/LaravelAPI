<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionReplacesTable extends Migration
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
        $schema->create('production_replaces', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('production_id')->default(1)->index('production_id');
            $table->bigInteger('component_id')->default(1)->index('component_id');
            $table->bigInteger('nomenklatura_from_id')->default(1)->index('nomenklatura_from_id');
            $table->bigInteger('nomenklatura_to_id')->default(1)->index('nomenklatura_to_id');
            $table->decimal('kolvo_from',20,3)->default(1)->index('kolvo_from');
            $table->decimal('kolvo_to',20,3)->default(1)->index('kolvo_to');
            $table->boolean('save_to_recipe')->default(0)->index('save_to_recipe');
            $table->unique(['production_id','component_id','nomenklatura_from_id','nomenklatura_to_id'],'unique_replace');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('production_replaces');
    }
}
