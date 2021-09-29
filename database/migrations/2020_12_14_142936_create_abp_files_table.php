<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbpFilesTable extends Migration
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
        $schema->create('files', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('file_driver_id')->default(1)->index('file_driver_id');
            $table->bigInteger('file_type_id')->default(1)->index('file_type_id');
            $table->string("folder",255)->nullable()->index("folder");
            $table->string("filename",255)->index("filename");
            $table->string("uid",1024)->nullable()->index("uid");
            $table->string("extension",5)->nullable()->default("")->index("extension");
            $table->boolean('is_main')->default(false)->index('is_main');
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
        Schema::connection('db1')->dropIfExists('files');
    }
}
