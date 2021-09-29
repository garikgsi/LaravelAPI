<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComissionMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db1')->table('productions', function (Blueprint $table) {
            $table->bigInteger('commission_member1')->default(1)->index('commission_member1');
            $table->bigInteger('commission_member2')->default(1)->index('commission_member2');
            $table->bigInteger('commission_chairman')->default(1)->index('commission_chairman');
        });
        Schema::connection('db1')->table('sklads', function (Blueprint $table) {
            $table->bigInteger('commission_member1')->default(1)->index('commission_member1');
            $table->bigInteger('commission_member2')->default(1)->index('commission_member2');
            $table->bigInteger('commission_chairman')->default(1)->index('commission_chairman');
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
            if (Schema::connection('db1')->hasColumn('productions', 'commission_member1')) {
                $table->dropColumn('commission_member1');
            }
            if (Schema::connection('db1')->hasColumn('productions', 'commission_member2')) {
                $table->dropColumn('commission_member2');
            }
            if (Schema::connection('db1')->hasColumn('productions', 'commission_chairman')) {
                $table->dropColumn('commission_chairman');
            }
        });
        Schema::connection('db1')->table('sklads', function (Blueprint $table) {
            if (Schema::connection('db1')->hasColumn('sklads', 'commission_member1')) {
                $table->dropColumn('commission_member1');
            }
            if (Schema::connection('db1')->hasColumn('sklads', 'commission_member2')) {
                $table->dropColumn('commission_member2');
            }
            if (Schema::connection('db1')->hasColumn('sklads', 'commission_chairman')) {
                $table->dropColumn('commission_chairman');
            }
        });
    }
}