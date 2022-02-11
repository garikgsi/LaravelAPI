<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToProductionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_items', function (Blueprint $table) {
            $table->boolean('is_active')->default(0)->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_items', function (Blueprint $table) {
            if (Schema::hasColumn('production_items', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}