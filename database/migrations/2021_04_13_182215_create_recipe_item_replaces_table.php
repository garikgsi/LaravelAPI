<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipeItemReplacesTable extends Migration
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
        $schema->create('recipe_item_replaces', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('recipe_item_id')->default(1)->index('recipe_item_id');
            $table->bigInteger('nomenklatura_to_id')->default(1)->index('nomenklatura_to_id');
            $table->decimal('kolvo_from',20,3)->default(1)->index('kolvo_from');
            $table->decimal('kolvo_to',20,3)->default(1)->index('kolvo_to');
            $table->unique(['recipe_item_id','nomenklatura_to_id'],'unique_replace');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('recipe_item_replaces');
    }
}
