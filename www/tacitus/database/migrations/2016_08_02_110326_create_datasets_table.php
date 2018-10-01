<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('original_id')->index();
            $table->integer('source_id')->unsigned()->index();
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->index(['original_id', 'source_id'], 'original_and_source_id_index');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->string('title');
            $table->boolean('private')->default(true);
            $table->enum('status', ['pending', 'ready', 'failed']);
            $table->text('error');
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
        Schema::drop('datasets');
    }
}
