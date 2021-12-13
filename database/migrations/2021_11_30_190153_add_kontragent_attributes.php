<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKontragentAttributes extends Migration
{
    public $fields = [
        'address',
        'phone',
        'email',
        'www'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kontragents', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                $table->string($field)->nullable()->index($field)->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kontragents', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                if (Schema::hasColumn('kontragents', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
}