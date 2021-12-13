<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeableMorphToSotrudniks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('sotrudniks', function (Blueprint $table) {
            $table->nullableMorphs("employeable");
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
            if (Schema::hasColumn('sotrudniks', 'employeable_type')) {
                $table->dropColumn('employeable');
            }
            if (Schema::hasColumn('sotrudniks', 'employeable_id')) {
                $table->dropColumn('employeable');
            }
        });
    }
}