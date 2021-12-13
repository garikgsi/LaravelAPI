<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOldIdToTables extends Migration
{
    private $tables = [
        "sklad_receives", "sklad_receive_items", "firm_positions",
        "manufacturers", "nomenklatura", "recipes", "recipe_items", "sotrudniks", "sklads",
        "tags", "table_tags", "files", "file_lists", "firms", "ed_ism", "kontragents"
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table_name) {
            if (!Schema::hasColumn($table_name, 'old_id')) {
                Schema::table($table_name, function (Blueprint $table) {
                    $table->bigInteger('old_id')->after('id')
                        ->nullable()
                        ->index('old_id')
                        ->default(null);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table_name) {
            if (Schema::hasColumn($table_name, 'old_id')) {
                Schema::table($table_name, function (Blueprint $table) {
                    $table->dropColumn('old_id');
                });
            }
        }
    }
}