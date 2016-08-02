<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->integer('sample_id')->unsigned()->index();
            $table->foreign('sample_id')->references('id')->on('samples')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name')->index();
            $table->text('value')->index();
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
