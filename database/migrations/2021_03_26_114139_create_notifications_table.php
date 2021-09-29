<?php

use Illuminate\Database\Migrations\Migration;
use App\Common\CustomBlueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
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
        $schema->create('notifications', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();

            $table->string('text')->nullable()->index('text');
            $table->bigInteger('user_id')->default(1)->index('user_id');
            $table->bigInteger('notification_type_id')->default(1)->index('notification_type_id');
            $table->boolean('is_readed',255)->default(0)->index('is_readed');
            $table->nullableMorphs("documentable");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('db1')->dropIfExists('notifications');
    }
}
