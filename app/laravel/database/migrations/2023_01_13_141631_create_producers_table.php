<?php

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
        Schema::create('producers', function (Blueprint $table) {
            // 生産者ID
            $table->id('producer_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.producer')]));
            // 作業者ID
            $table->unsignedBigInteger('worker_id')->nullable()->comment(__('yokakit.target_id', ['target' => __('yokakit.worker')]));
            // 生産ラインID
            $table->unsignedBigInteger('production_line_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_line')]));
            // 識別番号
            $table->string('identification_number', 32)->comment(__('yokakit.identification_number'));
            // 作業者名
            $table->string('worker_name', 32)->comment(__('yokakit.target_name', ['target' => __('yokakit.worker')]));
            // 開始
            $table->dateTime('start')->default(DB::raw('CURRENT_TIMESTAMP'))->comment(__('yokakit.start'));
            // 停止
            $table->dateTime('stop')->index()->nullable()->comment(__('yokakit.stop'));
            // タイムスタンプ
            $table->timestamps();
            // 外部キー
            $table->foreign('worker_id')->references('worker_id')->on('workers')->nullOnDelete();
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
        Schema::dropIfExists('producers');
    }
};
