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
        Schema::create('raspberry_pis', function (Blueprint $table) {
            // ラズパイID
            $table->id('raspberry_pi_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.raspberry_pi')]));
            // ラズパイ名
            $table->string('raspberry_pi_name', 32)->unique()->index()->comment(__('yokakit.target_name', ['target' => __('yokakit.raspberry_pi')]));
            // IPアドレス
            $table->ipAddress()->unique()->index()->comment(__('yokakit.ip_address'));
            // CPU温度
            $table->double('cpu_temperature', 6, 1)->nullable()->comment(__('yokakit.cpu_temperature'));
            // CPU使用率
            $table->unsignedFloat('cpu_utilization', 5, 1)->nullable()->comment(__('yokakit.cpu_utilization'));
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
        Schema::dropIfExists('raspberry_pis');
    }
};
