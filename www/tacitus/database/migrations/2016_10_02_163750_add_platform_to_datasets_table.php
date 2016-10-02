<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlatformToDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->integer('platform_id')->unsigned()->index()->nullable()->default(null);
            $table->foreign('platform_id', 'dataset_platform_id_foreign_key')->references('id')->on('platforms')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropForeign('dataset_platform_id_foreign_key');
            $table->dropColumn('platform_id');
        });
    }
}
