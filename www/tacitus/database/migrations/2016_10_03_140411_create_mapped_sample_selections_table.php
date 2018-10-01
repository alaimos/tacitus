<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMappedSampleSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapped_sample_selections', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status', ['pending', 'ready', 'failed']);
            $table->text('generated_files');
            $table->integer('selection_id')->unsigned()->index();
            $table->foreign('selection_id')->references('id')->on('sample_selections')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('platform_id')->unsigned()->index();
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('mapping_id')->unsigned()->index();
            $table->foreign('mapping_id')->references('id')->on('platform_mappings')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id', 'mapped_sample_selections_user_id_foreign_key')->references('id')->on('users')
                  ->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('mapped_sample_selections');
    }
}
