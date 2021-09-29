<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerialNumRegistersTable extends Migration
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
        $schema->create('serial_num_registers', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('serial_num_move_id')->default(1)->index('serial_num_move_id');
            $table->date('doc_date')->nullable()->index('doc_date');
            $table->date('ou_date')->nullable()->index('ou_date');
            $table->bigInteger('serial_id')->default(1)->index('serial_id');
            $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            $table->boolean('in_out')->nullable()->index('in_out');
            $table->integer('kolvo')->default(1)->index('kolvo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('serial_num_registers');
    }
}