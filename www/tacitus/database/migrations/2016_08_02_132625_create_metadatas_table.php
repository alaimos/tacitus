<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;

class CreateMetadatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('metadatas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sample_id')->unsigned();
            $table->index('sample_id', 'metadatas_sample_id_index');
            $table->foreign('sample_id')->references('id')->on('samples')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->index('name', 'metadatas_name_index');
            $table->text('value');
            $table->index('value', 'metadatas_value_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('metadatas');
    }
}
