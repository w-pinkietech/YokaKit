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
        Schema::table('sensor_events', function (Blueprint $table) {
            $table->dropColumn('device_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensor_events', function (Blueprint $table) {
            $table->string('device_name', 32)->after('ip_address')->comment(__('yokakit.target_name', ['target' => __('yokakit.device')]));
        });
    }
};
