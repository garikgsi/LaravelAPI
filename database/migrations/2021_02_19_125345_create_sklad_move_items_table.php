<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkladMoveItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $schema = DB::connection('db1')->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });
        $schema->create('sklad_move_items', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->unsignedBigInteger('sklad_move_id')->index('sklad_move_id')->default(1);
            $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            $table->decimal('kolvo',20,3)->default(1)->index('kolvo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::connection('db1')->dropIfExists('sklad_move_items');
    }
}
