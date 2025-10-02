<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('sensor_events', function (Blueprint $table) {
            // イベントID
            $table->id('sensor_event_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.event')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // センサーID
            $table->unsignedBigInteger('sensor_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.sensor_type')]));
            // IPアドレス
            $table->ipAddress()->comment(__('yokakit.ip_address'));
            // デバイス名
            $table->string('device_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.device')]));
            // センサー種別
            $table->smallInteger('sensor_type', false, true)->comment(__('yokakit.sensor_type'));
            // エイリアス
            $table->string('alias', 32)->nullable()->comment(__('yokakit.alias'));
            // トリガー
            $table->boolean('trigger')->comment(__('yokakit.trigger'));
            // 信号
            $table->boolean('signal')->comment(__('yokakit.signal'));
            // 値
            $table->float('value', 8, 3);
            // タイムスタンプ
            $table->timestamp('at', 3)->index()->default(DB::raw('CURRENT_TIMESTAMP'));
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('sensor_id')->references('sensor_id')->on('sensors')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_events');
    }
};
