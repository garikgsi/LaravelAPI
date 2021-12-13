<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateActsTable extends Migration
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
        $schema->create('acts', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();
            $table->documentFields();

            $table->bigInteger('order_id')->default(1)->index('order_id');
            $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            $table->decimal('summa', 20, 4)->default(0)->index('summa');
            $table->decimal('summa_nds', 20, 4)->default(0)->index('summa_nds');
            $table->date('period_start_date')->nullable()->index('period_start_date');
            $table->date('period_end_date')->nullable()->index('period_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acts');
    }
}