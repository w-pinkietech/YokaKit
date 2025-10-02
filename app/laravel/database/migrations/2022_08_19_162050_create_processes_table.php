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
        Schema::create('processes', function (Blueprint $table) {
            // ID
            $table->id('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.process')]));
            // 工程名
            $table->string('process_name', 32)->unique()->index()->comment(__('yokakit.target_name', ['target' => __('yokakit.process')]));
            // チャートカラー
            $table->char('plan_color', 7)->default('#FFFFFF')->comment(__('yokakit.chart_color'));
            // 備考
            $table->string('remark', 256)->nullable()->comment(__('yokakit.remark'));
            // タイムスタンプ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processes');
    }
};
