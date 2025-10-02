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
        Schema::create('payloads', function (Blueprint $table) {
            // ペイロードID
            $table->id('payload_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.payload')]));
            // 生産ラインID
            $table->unsignedBigInteger('production_line_id')->unique()->comment(__('yokakit.target_id', ['target' => __('yokakit.production_line')]));
            // ペイロード
            $table->json('payload')->comment(__('yokakit.payload'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('production_line_id')->references('production_line_id')->on('production_lines')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payloads');
    }
};
