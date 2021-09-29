<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkladRegister extends Migration
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
        $schema->create('sklad_register', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->date('doc_date')->nullable()->index('doc_date');
            $table->bigInteger('nomenklatura_id')->default(1)->index('nomenklatura_id');
            $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            $table->bigInteger('firm_id')->default(1)->index('firm_id');
            $table->bigInteger('kontragent_id')->default(1)->index('kontragent_id');
            $table->decimal('kolvo',20,3)->default(1)->index('kolvo');
            $table->decimal('price',20,4)->default(0)->index('price');
            $table->decimal('summa',20,4)->default(0)->index('summa');
            $table->bigInteger('nds_id')->default(1)->index('nds_id');
            $table->boolean('saldo')->index('saldo');
            $table->morphs("registrable");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('sklad_register');
    }
}
