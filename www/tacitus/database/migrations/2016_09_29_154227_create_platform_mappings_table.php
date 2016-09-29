<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('platform_id')->unsigned()->index();
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
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
        Schema::drop('platform_mappings');
    }
}
