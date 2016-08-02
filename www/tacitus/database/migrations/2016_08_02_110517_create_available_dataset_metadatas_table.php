<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAvailableDatasetMetadatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('available_dataset_metadatas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('dataset_id')->unsigned()->index();
            $table->foreign('dataset_id')->references('id')->on('datasets')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('available_dataset_metadatas');
    }
}
