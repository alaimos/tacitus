<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationMappedSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_mapped_selections', function (Blueprint $table) {
            $table->integer('integration_id')->unsigned();
            $table->foreign('integration_id')->references('id')->on('integrations')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('selection_id')->unsigned();
            $table->foreign('selection_id')->references('id')->on('mapped_sample_selections')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['integration_id', 'selection_id'], 'integration_mapped_selection_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('integration_mapped_selections');
    }
}
