<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('samples', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('position');
            $table->integer('dataset_id')->unsigned();
            $table->index('dataset_id', ['name' => 'samples_dataset_id_index']);
            $table->foreign('dataset_id')->references('id')->on('datasets')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('samples');
    }
}
