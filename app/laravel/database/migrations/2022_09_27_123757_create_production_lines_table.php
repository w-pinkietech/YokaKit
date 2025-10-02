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
        Schema::create('production_lines', function (Blueprint $table) {
            // 生産ラインID
            $table->id('production_line_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_line')]));
            // 生産履歴ID
            $table->unsignedBigInteger('production_history_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_history')]));
            // ラインID
            $table->unsignedBigInteger('line_id')->nullable()->comment(__('yokakit.target_id', ['target' => __('yokakit.line')]));
            // ライン名
            $table->string('line_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.line')]));
            // チャートカラー
            $table->char('chart_color', 7)->comment(__('yokakit.chart_color'));
            // IPアドレス
            $table->ipAddress()->comment(__('yokakit.ip_address'));
            // ピン番号
            $table->tinyInteger('pin_number', false, true)->comment(__('yokakit.pin_number'));
            // 不良品
            $table->boolean('defective')->default(false)->comment(__('yokakit.defective'));
            // 統計フラグ
            $table->boolean('indicator')->default(false)->comment(__('yokakit.indicator'));
            // オフセットカウント
            $table->integer('offset_count', false, true)->nullable()->comment(__('yokakit.offset') . __('yokakit.count'));
            // 現在のカウント
            $table->integer('count', false, true)->default(0)->comment(__('yokakit.count'));
            // オフセット秒
            $table->integer('offset_second')->default(0)->comment(__('yokakit.offset') . 'sec');
            // タイムスタンプ
            $table->timestamps();
            // 複合インデックス
            $table->index(['ip_address', 'pin_number']);
            // 外部キー
            $table->foreign('production_history_id')->references('production_history_id')->on('production_histories')->cascadeOnDelete();
            $table->foreign('line_id')->references('line_id')->on('lines')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_lines');
    }
};
