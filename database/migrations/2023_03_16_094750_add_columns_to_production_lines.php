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
        Schema::table('production_lines', function (Blueprint $table) {
            // 不良品ラインが関連するラインID
            $table->unsignedBigInteger('parent_id')->nullable()->after('line_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.target_line')]));
            // 順序
            $table->integer('order')->after('defective')->comment(__('yokakit.order'));
            // オフセットミリ秒
            $table->integer('offset_millisecond')->default(0)->after('count')->comment(__('yokakit.offset') . 'ms');
            // オフセット秒の削除
            $table->dropColumn('offset_second');
            // 外部キーは自身のテーブル
            $table->foreign('parent_id')->references('production_line_id')->on('production_lines')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->dropForeign('production_lines_parent_id_foreign');
            $table->integer('offset_second')->default(0)->after('count')->comment(__('yokakit.offset') . 'sec');
            $table->dropColumn('offset_millisecond');
            $table->dropColumn('order');
            $table->dropColumn('parent_id');
        });
    }
};
