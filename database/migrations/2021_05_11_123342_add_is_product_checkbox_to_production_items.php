<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProductCheckboxToProductionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('production_items', function (Blueprint $table) {
            $table->boolean('is_producted')->default(0)->index('is_producted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('production_items', function (Blueprint $table) {
            if (Schema::hasColumn('production_items', 'is_producted')) {
                $table->dropColumn('is_producted');
            }
        });
    }
}
