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
        Schema::table('lines', function (Blueprint $table) {
            // 不良品ラインが関連するラインID
            $table->unsignedBigInteger('parent_id')->nullable()->after('worker_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.target_line')]));
            // 外部キーは自身のテーブル
            $table->foreign('parent_id')->references('line_id')->on('lines')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropForeign('lines_parent_id_foreign');
            $table->dropColumn('parent_id');
        });
    }
};
