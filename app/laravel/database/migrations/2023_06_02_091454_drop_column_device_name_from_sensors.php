<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign('sensors_raspberry_pi_id_foreign');
            $table->dropUnique('sensors_raspberry_pi_id_device_name_unique');
            $table->dropIndex('sensors_raspberry_pi_id_device_name_index');
            $table->dropColumn('device_name');
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign('sensors_raspberry_pi_id_foreign');
            $table->string('device_name', 32)->after('raspberry_pi_id')->comment(__('yokakit.target_name', ['target' => __('yokakit.device')]));
            $table->unique(['raspberry_pi_id', 'device_name']);
            $table->index(['raspberry_pi_id', 'device_name']);
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
        });
    }
};
