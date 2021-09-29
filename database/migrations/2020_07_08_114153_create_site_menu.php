<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteMenu extends Migration
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
        $schema->create('site_menu_points', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->text('content')->nullable();
            $table->text('content2')->nullable();
            $table->integer('parent_menu_point')->default(0)->index('parent_menu_point');
            $table->integer('num_order')->default(1)->index('num_order');
            $table->string('meta_title',255)->nullable()->index("meta_title");
            $table->string('meta_keywords',255)->nullable()->index("meta_keywords");
            $table->string('meta_description',255)->nullable()->index("meta_description");
            $table->string('surl',255)->nullable()->index("surl");
            $table->integer('module_id')->default(1)->index('module_id');
            $table->boolean('is_popular')->default(0)->index('is_popular');
            $table->boolean('is_show_in_menu')->default(1)->index('is_show_in_menu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('site_menu_points');
    }
}
