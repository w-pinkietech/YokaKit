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
        Schema::create('andon_configs', function (Blueprint $table) {
            // アンドン設定ID
            $table->id('andon_config_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.target_config', ['target' => __('yokakit.andon')])]));
            // ユーザーID
            $table->unsignedBigInteger('user_id')->unique()->comment(__('yokakit.target_id', ['target' => __('yokakit.user')]));
            // 行数
            $table->integer('row_count')->comment(__('yokakit.row_count'));
            // 列数
            $table->integer('column_count')->comment(__('yokakit.column_count'));
            // 自動再生
            $table->boolean('auto_play')->comment(__('yokakit.auto_play'));
            // 自動再生速度
            $table->integer('auto_play_speed')->comment(__('yokakit.auto_play_speed'));
            // スライド速度
            $table->integer('slide_speed')->comment(__('yokakit.slide_speed'));
            // 緩急
            $table->string('easing', 16)->comment(__('yokakit.easing'));
            // フェード
            $table->boolean('fade')->comment(__('yokakit.fade'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー設定
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('andon_configs');
    }
};
