<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->index('platform_id', ['name' => 'platform_map_data_platform_id_index']);
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('mapping_id')->unsigned()->index();
            $table->index('mapping_id', ['name' => 'platform_map_data_mapping_id_index']);
            $table->foreign('mapping_id')->references('id')->on('platform_mappings')->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('mapFrom');
            $table->string('mapTo');
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
