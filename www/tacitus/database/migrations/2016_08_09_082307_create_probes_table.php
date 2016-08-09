<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProbesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('probes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->index('name', ['name' => 'probe_name_index']);
            $table->json('data');
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
        Schema::connection('mongodb')->drop('probes');
    }
}
