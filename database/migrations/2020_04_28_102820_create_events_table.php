<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Common\CustomBlueprint;


class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });

        $schema->create('events', function (CustomBlueprint $table) {
            $table->commonFields();
            $table->mySQL();
            $table->softDeletes();
            $table->timestamp('start_event')->default(DB::raw('CURRENT_TIMESTAMP'))->index('start_event');
            $table->timestamp('end_event')->default(DB::raw('CURRENT_TIMESTAMP'))->index('end_event');
            $table->boolean('all_day')->default(false)->index('all_day');
            $table->boolean('is_done')->default(false)->index('is_done');
            $table->bigInteger('calendar_id')->index('calendar_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
