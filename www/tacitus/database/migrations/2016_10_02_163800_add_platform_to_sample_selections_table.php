<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPlatformToSampleSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_selections', function (Blueprint $table) {
            $table->integer('platform_id')->unsigned()->index()->nullable()->default(null);
            $table->foreign('platform_id', 'sample_selections_platform_id_foreign_key')->references('id')
                  ->on('platforms')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_selections', function (Blueprint $table) {
            $table->dropForeign('sample_selections_platform_id_foreign_key');
            $table->dropColumn('platform_id');
        });
    }
}
