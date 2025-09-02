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
        Schema::create('process_planned_outages', function (Blueprint $table) {
            // 工程計画停止時間ID
            $table->id('process_planned_outage_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process_planned_outage')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // 計画停止時間ID
            $table->unsignedBigInteger('planned_outage_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.planned_outage')]));
            // タイムスタンプ
            $table->timestamps();
            // 複合ユニーク
            $table->unique(['process_id', 'planned_outage_id']);
            // 複合インデックス
            $table->index(['process_id', 'planned_outage_id']);
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('planned_outage_id')->references('planned_outage_id')->on('planned_outages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_planned_outages');
    }
};
