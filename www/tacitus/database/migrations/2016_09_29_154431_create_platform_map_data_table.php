<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlatformMapDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('platform_map_data', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('platform_id')->unsigned();
            $table->index('platform_id', 'platform_map_data_platform_id_index');
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade')->onUpdate('cascade');
            $table->string('probe');
            $table->index('probe', 'platform_map_data_probe_index');
            //this collection contains a dynamic number of fields depending on the mappings of the platform
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('platform_map_datas');
    }
}
