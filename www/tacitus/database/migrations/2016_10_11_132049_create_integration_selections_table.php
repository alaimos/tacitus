<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_selections', function (Blueprint $table) {
            $table->integer('integration_id')->unsigned();
            $table->foreign('integration_id')->references('id')->on('integrations')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('selection_id')->unsigned();
            $table->foreign('selection_id')->references('id')->on('sample_selections')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['integration_id', 'selection_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('integration_selections');
    }
}
