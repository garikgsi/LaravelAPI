<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManagerToSotrudnikOption extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    //
    public function up()
    {
        Schema::table('sotrudniks', function (Blueprint $table) {
            $table->boolean('is_manager')->default(0)->index('is_manager');
            $table->string('firm_position_text')->nullable()->default('')->index('firm_position_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sotrudniks', function (Blueprint $table) {
            if (Schema::hasColumn('sotrudniks', 'is_manager')) {
                $table->dropColumn('is_manager');
            }
            if (Schema::hasColumn('sotrudniks', 'firm_position_text')) {
                $table->dropColumn('firm_position_text');
            }
        });
    }
}