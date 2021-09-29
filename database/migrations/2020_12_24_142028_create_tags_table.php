<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
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
        $schema->create('tags', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('tags');
    }
}
