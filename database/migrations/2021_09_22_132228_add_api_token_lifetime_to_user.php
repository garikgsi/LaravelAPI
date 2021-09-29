<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiTokenLifetimeToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'api_token_lifetime')) {
            Schema::table('users', function (Blueprint $table) {
                $table->datetime('api_token_lifetime')->after('api_token')
                    ->nullable()
                    ->index('api_token_lifetime')
                    ->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_token_lifetime')) {
                $table->dropColumn('api_token_lifetime');
            }
        });
    }
}