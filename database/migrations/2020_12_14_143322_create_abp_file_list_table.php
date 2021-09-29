<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbpFileListTable extends Migration
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
        $schema->create('file_lists', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('file_id')->default(1)->index('file_id');
            $table->morphs("table");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('file_lists');
    }
}
