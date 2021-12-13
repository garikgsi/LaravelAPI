<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
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
        $schema->create('orders', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();
            $table->documentFields();

            $table->bigInteger('contract_id')->default(1)->index('contract_id');
            $table->date('start_date')->nullable()->index('start_date');
            $table->date('end_date')->nullable()->index('end_date');
            $table->bigInteger('manager_id')->default(1)->index('manager_id');
            $table->bigInteger('executer_id')->default(1)->index('executer_id');
            $table->boolean('is_written')->default(0)->index('is_written');
            $table->date('write_date')->nullable()->index('write_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}