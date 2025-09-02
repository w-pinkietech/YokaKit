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
            $table->integer('identification_number', false, true)->after('ip_address')->comment(__('yokakit.identification_number'));
            $table->string('alias', 128)->nullable(false)->comment(__('yokakit.alarm_text'))->change();
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
            $table->dropColumn('identification_number');
            $table->string('alias', 32)->nullable()->comment(__('yokakit.alarm_text'))->change();
        });
    }
};
