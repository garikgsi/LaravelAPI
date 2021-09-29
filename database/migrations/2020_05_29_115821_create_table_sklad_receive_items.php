<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSkladReceiveItems extends Migration
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
        $schema->create('sklad_receive_items', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->integer('npp')->default(0)->index('npp');
            $table->bigInteger('sklad_receive_id')->default(1)->index('sklad_receive_id');
            $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            $table->string('nomenklatura_name',1024)->default('')->index('nomenklatura_name');
            $table->decimal('kolvo',20,3)->default(1)->index('kolvo');
            $table->decimal('price',20,4)->default(0)->index('price');
            $table->decimal('summa',20,4)->default(0)->index('summa');
            $table->decimal('summa_nds',20,4)->default(0)->index('summa_nds');
            $table->bigInteger('nds_id')->default(1)->index('nds_id');
            $table->string('stavka_nds',16)->default('')->index('stavka_nds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('sklad_receive_items');
    }
}
