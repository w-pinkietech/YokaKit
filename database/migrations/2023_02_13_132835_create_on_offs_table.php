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
        Schema::create('on_offs', function (Blueprint $table) {
            // ID
            $table->id('on_off_id')->comment(__('yokakit.target_id', ['target' => 'ON-OFF']));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // ラズパイID
            $table->unsignedBigInteger('raspberry_pi_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.raspberry_pi')]));
            // イベント名
            $table->string('event_name', 64)->comment(__('yokakit.target_name', ['target' => __('yokakit.event')]));
            // ONメッセージ
            $table->string('on_message', 64)->comment(__('yokakit.target_message', ['target' => 'ON']));
            // OFFメッセージ
            $table->string('off_message', 64)->nullable()->comment(__('yokakit.target_message', ['target' => 'OFF']));
            // ピン番号
            $table->tinyInteger('pin_number', false, true)->comment(__('yokakit.pin_number'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
            // 複合ユニーク
            $table->unique(['process_id', 'event_name']);
            $table->unique(['raspberry_pi_id', 'pin_number']);
            // 複合インデックス
            $table->index(['raspberry_pi_id', 'pin_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('on_offs');
    }
};
