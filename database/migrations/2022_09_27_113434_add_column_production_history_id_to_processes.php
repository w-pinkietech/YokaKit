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
        Schema::table('processes', function (Blueprint $table) {
            // 生産履歴ID
            $table->unsignedBigInteger('production_history_id')->nullable()->unique()->after('process_id')->comment(__('yokakit.target_id', ['target' => __('yokakit.production_history')]));
            // 外部キー
            $table->foreign('production_history_id')->references('production_history_id')->on('production_histories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign('processes_production_history_id_foreign');
            $table->dropColumn('production_history_id');
        });
    }
};
