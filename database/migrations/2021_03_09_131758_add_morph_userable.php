<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMorphUserable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('user_info', function (Blueprint $table) {
            $table->nullableMorphs("userable");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('user_info', function (Blueprint $table) {
            if (Schema::hasColumn('user_info', 'userable')) {
                $table->dropColumn('userable');
            }
        });
    }
}
