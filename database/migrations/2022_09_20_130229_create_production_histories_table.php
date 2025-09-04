<?php

use App\Enums\ProductionStatus;
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
        Schema::create('production_histories', function (Blueprint $table) {
            // 生産履歴ID
            $table->id('production_history_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_history')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->nullable()->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // 工程名
            $table->string('process_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.process')]));
            // 品番ID
            $table->unsignedBigInteger('part_number_id')->nullable()->comment(__('yokakit.target_id', ['target' => __('yokakit.part_number')]));
            // 計画値色
            $table->char('plan_color', 7)->comment(__('yokakit.plan_color'));
            // 品番名
            $table->string('part_number_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.part_number')]));
            // サイクルタイム
            $table->unsignedFloat('cycle_time', 8, 3)->comment(__('yokakit.cycle_time'));
            // オーバータイム
            $table->unsignedFloat('over_time', 8, 3)->comment(__('yokakit.over_time'));
            // 目標値
            $table->integer('goal')->nullable()->comment(__('yokakit.goal'));
            // 開始
            $table->dateTime('start')->default(DB::raw('CURRENT_TIMESTAMP'))->comment(__('yokakit.start'));
            // 停止
            $table->dateTime('stop')->nullable()->comment(__('yokakit.stop'));
            // ステータス
            $table->tinyInteger('status', false, true)->comment(__('yokakit.status'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('part_number_id')->references('part_number_id')->on('part_numbers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_histories');
    }
};
