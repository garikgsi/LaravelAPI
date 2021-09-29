<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFirms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = DB::connection('db1')->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });
        $schema->create('firms', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->string('kpp',32)->nullable()->default('')->index('kpp');
            $table->string('inn',32)->nullable()->default('')->index('inn');
            $table->date('reg_date')->nullable()->index('reg_data');
            $table->bigInteger('rs_id')->default(1)->index('rs_id');
            $table->string('okpo',16)->nullable()->default('')->index('okpo');
            $table->string('full_name',512)->nullable()->default('')->index('full_name');
            $table->string('short_name',128)->nullable()->default('')->index('short_name');
            $table->string('ogrn',32)->nullable()->default('')->index('ogrn');
            $table->string('okved',16)->nullable()->default('')->index('okved');
            $table->string('okopf',16)->nullable()->default('')->index('okopf');
            $table->string('firstname_ip',128)->nullable()->default('')->index('firstname_ip');
            $table->string('name_ip',128)->nullable()->default('')->index('name_ip');
            $table->string('father_name_ip',128)->nullable()->default('')->index('father_name_ip');
            $table->string('dop_okved',128)->nullable()->default('')->index('dop_okved');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('firms');
    }
}
