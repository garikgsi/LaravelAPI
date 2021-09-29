<?php

use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Common\CustomBlueprint;


class CreateTableNomenklatura extends Migration
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
        $schema->create('nomenklatura', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();
            $table->bigInteger('doc_type_id')->default(1)->index('doc_type_id');
            $table->bigInteger('ed_ism_id')->default(1)->index('ed_is_id');
            $table->text('description')->nullable();
            $table->string('part_num',255)->nullable()->index('part_num');
            $table->bigInteger('manufacturer_id')->default(1)->index('manufacturer_id');
            $table->string('artikul',11)->nullable()->index('artikul');
            $table->decimal('price',20,10)->default(0.00)->index('price');
            $table->bigInteger('nds_id')->default(1)->index('nds_id');
            $table->boolean('is_usluga')->default(false)->index('is_usluga');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('nomenklatura');
    }
}
