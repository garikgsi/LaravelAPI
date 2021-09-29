<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeeperToSklad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('sklads', function (Blueprint $table) {
            $table->bigInteger('keeper_id')->default(1)->index('keeper_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('sklads', function (Blueprint $table) {
            if (Schema::hasColumn('sklads', 'keeper_id')) {
                $table->dropColumn('keeper_id');
            }
        });
    }
}
