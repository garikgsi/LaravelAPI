<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionsTable extends Migration
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
        $schema->create('productions', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->documentFields();

            $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            $table->bigInteger('recipe_id')->default(1)->index('recipe_id');
            $table->decimal('kolvo',20,3)->default(1)->index('kolvo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('productions');
    }
}
