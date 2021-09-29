<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProductionComponentReplacedProperty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('production_components', function (Blueprint $table) {
            $table->boolean('is_replaced')->default(0)->index('is_replaced');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('production_components', function (Blueprint $table) {
            if (Schema::hasColumn('production_components', 'is_replaced')) {
                $table->dropColumn('is_replaced');
            }
        });
    }
}
