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
        Schema::create('sensors', function (Blueprint $table) {
            // センサーID
            $table->id('sensor_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.sensor_type')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // ラズパイID
            $table->unsignedBigInteger('raspberry_pi_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.raspberry_pi')]));
            // デバイス名
            $table->string('device_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.device')]));
            // センサー種別
            $table->smallInteger('sensor_type', false, true)->comment(__('yokakit.sensor_type'));
            // エイリアス
            $table->string('alias', 32)->nullable()->comment(__('yokakit.alias'));
            // トリガー
            $table->boolean('trigger')->comment(__('yokakit.trigger'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
            // 複合ユニーク
            $table->unique(['raspberry_pi_id', 'device_name']);
            // 複合インデックス
            $table->index(['raspberry_pi_id', 'device_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensors');
    }
};
