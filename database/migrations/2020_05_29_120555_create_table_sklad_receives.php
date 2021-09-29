<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSkladReceives extends Migration
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
        $schema->create('sklad_receives', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->string('doc_num',32)->nullable()->default('')->index('doc_num');
            $table->date('doc_date')->nullable()->index('doc_date');
            $table->boolean('is_active')->default(false)->index('is_active');
            $table->bigInteger('firm_id')->default(1)->index('firm_id');
            $table->bigInteger('sklad_id')->default(1)->index('sklad_id');
            $table->bigInteger('kontragent_id')->default(1)->index('kontragent_id');
            $table->bigInteger('dogovor_id')->default(1)->index('dogovor_id');
            $table->bigInteger('valuta_id')->default(1)->index('valuta_id');
            $table->string('in_doc_num',32)->nullable()->default('')->index('in_doc_num');
            $table->date('in_doc_date')->nullable()->index('in_doc_date');
            $table->bigInteger('kontragent_otpravitel_id')->default(1)->index('kontragent_otpravitel_id');
            $table->bigInteger('firm_poluchatel_id')->default(1)->index('firm_poluchatel_id');
            $table->boolean('price_include_nds')->default(true)->index('price_include_nds');
            $table->boolean('sum_include_nds')->default(true)->index('sum_include_nds');
            $table->decimal('summa',20,4)->default(0)->index('summa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('sklad_receives');
    }
}
