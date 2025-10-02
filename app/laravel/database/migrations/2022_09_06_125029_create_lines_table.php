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
        Schema::create('lines', function (Blueprint $table) {
            // ラインID
            $table->id('line_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.line')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // ラズパイID
            $table->unsignedBigInteger('raspberry_pi_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.raspberry_pi')]));
            // 作業者ID
            $table->unsignedBigInteger('worker_id')->nullable()->comment(__('yokakit.target_id', ['target' => __('yokakit.worker')]));
            // ライン名
            $table->string('line_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.line')]));
            // チャートカラー
            $table->char('chart_color', 7)->comment(__('yokakit.chart_color'));
            // ピン番号
            $table->tinyInteger('pin_number', false, true)->comment(__('yokakit.pin_number'));
            // 不良品
            $table->boolean('defective')->default(false)->comment(__('yokakit.defective'));
            // 順序
            $table->integer('order')->default(2147483647)->comment(__('yokakit.order'));
            // タイムスタンプ
            $table->timestamps();
            // 複合ユニーク
            $table->unique(['line_name', 'process_id']);
            $table->unique(['pin_number', 'raspberry_pi_id']);
            // 複合インデックス
            $table->index(['line_name', 'process_id']);
            $table->index(['pin_number', 'raspberry_pi_id']);
            // 外部キー
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
            $table->foreign('worker_id')->references('worker_id')->on('workers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lines');
    }
};
