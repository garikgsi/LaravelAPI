<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteContentsTable extends Migration
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
        $schema->create('site_contents', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->bigInteger("menu_point_id")->index("menu_point_id");
            $table->text("content")->nullable();
            $table->text("preview")->nullable();
            $table->text("index_page_text")->nullable();
            $table->dateTime("start_from")->useCurrent()->index("start_from");
            $table->string('meta_title',255)->nullable()->index("meta_title");
            $table->string('meta_keywords',255)->nullable()->index("meta_keywords");
            $table->string('meta_description',255)->nullable()->index("meta_description");
            $table->string('surl',255)->nullable()->index("surl");
            $table->string('img')->nullable()->index("img");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('site_contents');
    }
}
