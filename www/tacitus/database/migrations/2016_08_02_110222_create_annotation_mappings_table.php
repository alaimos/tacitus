<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnnotationMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annotation_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('annotation_id')->unsigned()->index();
            $table->integer('mapping_type_id')->unsigned()->index();
            $table->string('map_to');
            $table->foreign('annotation_id')->references('id')->on('annotations')->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('mapping_type_id')->references('id')->on('mapping_types')->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::drop('annotation_mappings');
    }
}
