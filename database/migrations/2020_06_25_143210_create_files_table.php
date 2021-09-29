<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->index("uuid");
            $table->timestamps();
            $table->enum("driver",["local","google"])->default("local")->index("driver");
            $table->string("filename",255)->index("filename");
            $table->string("name",255)->default("")->index("name");
            $table->string("extension",5)->default("")->index("extension");
            $table->text("description")->nullable();
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
