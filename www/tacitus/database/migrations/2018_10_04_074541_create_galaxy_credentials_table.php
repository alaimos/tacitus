<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGalaxyCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galaxy_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('hostname');
            $table->integer('port')->unsigned();
            $table->boolean('use_https');
            $table->text('api_key');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('galaxy_credentials');
    }
}
