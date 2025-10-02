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
        Schema::create('cycle_times', function (Blueprint $table) {
            // サイクルタイムID
            $table->id('cycle_time_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.cycle_time')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // 品番ID
            $table->unsignedBigInteger('part_number_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.part_number')]));
            // サイクルタイム
            $table->unsignedFloat('cycle_time', 8, 3)->comment(__('yokakit.cycle_time'));
            // オーバータイム
            $table->unsignedFloat('over_time', 8, 3)->comment(__('yokakit.over_time'));
            // タイムスタンプ
            $table->timestamps();
            // 複合ユニーク
            $table->unique(['process_id', 'part_number_id']);
            // 複合インデックス
            $table->index(['process_id', 'part_number_id']);
            // 外部キー
            $table->foreign('part_number_id')->references('part_number_id')->on('part_numbers')->cascadeOnDelete();
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cycle_times');
    }
};
