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
        Schema::create('productions', function (Blueprint $table) {
            // 生産ラインID (主キーではない)
            $table->unsignedBigInteger('production_line_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_line')]));
            // 作成日時
            $table->dateTime('at', 3)->index()->default(DB::raw('CURRENT_TIMESTAMP'))->comment(__('yokakit.created_at'));
            // カウント
            $table->integer('count')->comment(__('yokakit.count'));
            // 段取り替え
            $table->boolean('changeover')->nullable()->comment(__('yokakit.changeover'));
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
        Schema::dropIfExists('productions');
    }
};
