<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirmToProduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('productions', function (Blueprint $table) {
            $table->bigInteger('firm_id')->default(1)->index('firm_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('productions', function (Blueprint $table) {
            if (Schema::connection('db1')->hasColumn('productions', 'firm_id')) {
                $table->dropColumn('firm_id');
            }
        });
    }
}