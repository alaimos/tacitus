<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sample_id')->unsigned();
            $table->index('sample_id', ['name' => 'data_sample_id_index']);
            $table->foreign('sample_id')->references('id')->on('samples')->onDelete('cascade')->onUpdate('cascade');
            $table->string('probe');
            $table->index('probe', ['name' => 'data_probe_index']);
            $table->double('value');
            $table->index('value', ['name' => 'data_value_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('data');
    }
}
