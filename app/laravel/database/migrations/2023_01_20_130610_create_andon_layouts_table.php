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
        Schema::create('andon_layouts', function (Blueprint $table) {
            // アンドンレイアウトID
            $table->id('andon_layout_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.andon_layout')]));
            // ユーザーID
            $table->unsignedBigInteger('user_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.user')]));
            // 工程ID
            $table->unsignedBigInteger('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // 表示
            $table->boolean('is_display')->default(true)->comment(__('yokakit.display'));
            // 順序
            $table->integer('order')->default(2147483647)->comment(__('yokakit.order'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('process_id')->references('process_id')->on('processes')->cascadeOnDelete();
            // 複合ユニーク
            $table->unique(['user_id', 'process_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('andon_layouts');
    }
};
