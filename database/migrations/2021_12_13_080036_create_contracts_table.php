<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
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
        $schema->create('contracts', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger('firm_id')->default(1)->index('firm_id');
            $table->nullableMorphs("contractable");
            $table->bigInteger('contract_type_id')->default(1)->index('contract_type_id');
            $table->bigInteger('rs_id')->default(1)->index('rs_id');
            $table->string('contract_num', 32)->nullable()->default('')->index('contract_num');
            $table->date('contract_date')->nullable()->index('contract_date');
            $table->boolean('is_written')->default(0)->index('is_written');
            $table->date('end_date')->nullable()->index('end_date');
            $table->date('write_date')->nullable()->index('write_date');
            $table->decimal('summa', 20, 4)->default(0)->index('summa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}