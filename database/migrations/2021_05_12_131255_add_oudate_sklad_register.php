<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOudateSkladRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('sklad_register', function (Blueprint $table) {
            $table->date('ou_date')->nullable()->default(null)->after('doc_date')->index('ou_date');
            $table->decimal('ou_kolvo',20,3)->default(0)->after('kolvo')->index('ou_kolvo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->table('sklad_register', function (Blueprint $table) {
            $cols = ['ou_date','ou_kolvo'];
            foreach($cols as $col) {
                if (Schema::hasColumn('sklad_register', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
