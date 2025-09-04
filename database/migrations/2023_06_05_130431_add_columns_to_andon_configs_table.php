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
        Schema::table('andon_configs', function (Blueprint $table) {
            // 設備総合効率を表示するかどうか
            $table->boolean('is_show_overall_equipment_effectiveness')->default(false)->after('fade')->comment(__('yokakit.is_show_overall_equipment_effectiveness'));
            // 性能稼働率を表示するかどうか
            $table->boolean('is_show_performance_operating_rate')->default(false)->after('fade')->comment(__('yokakit.is_show_performance_operating_rate'));
            // 時間稼働率を表示するかどうか
            $table->boolean('is_show_time_operating_rate')->default(false)->after('fade')->comment(__('yokakit.is_show_time_operating_rate'));
            // 標準サイクルタイムを表示するかどうか
            $table->boolean('is_show_standard_cycle_time')->default(false)->after('fade')->comment(__('yokakit.is_show_standard_cycle_time'));
            // 達成率を表示するかどうか
            $table->boolean('is_show_achievement_rate')->default(false)->after('fade')->comment(__('yokakit.achievement_rate'));
            // 計画値を表示するかどうか
            $table->boolean('is_show_plan_count')->default(false)->after('fade')->comment(__('yokakit.is_show_plan_count'));
            // 不良品率を表示するかどうか
            $table->boolean('is_show_defective_rate')->default(false)->after('fade')->comment(__('yokakit.is_show_defective_rate'));
            // 良品率を表示するかどうか
            $table->boolean('is_show_good_rate')->default(false)->after('fade')->comment(__('yokakit.is_show_good_rate'));
            // 不良品数を表示するかどうか
            $table->boolean('is_show_defective_count')->default(false)->after('fade')->comment(__('yokakit.is_show_defective_count'));
            // 良品数を表示するかどうか
            $table->boolean('is_show_good_count')->default(false)->after('fade')->comment(__('yokakit.is_show_good_count'));
            // 開始時間を表示するかどうか
            $table->boolean('is_show_start')->default(true)->after('fade')->comment(__('yokakit.is_show_start'));
            // 品番を表示するかどうか
            $table->boolean('is_show_part_number')->default(true)->after('fade')->comment(__('yokakit.is_show_part_number'));
            // アイテム表示列数
            $table->integer('item_column_count')->default(3)->after('fade')->comment(__('yokakit.item_column_count'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('andon_configs', function (Blueprint $table) {
            $table->dropColumn('item_column_count');
            $table->dropColumn('is_show_part_number');
            $table->dropColumn('is_show_start');
            $table->dropColumn('is_show_good_count');
            $table->dropColumn('is_show_good_rate');
            $table->dropColumn('is_show_defective_count');
            $table->dropColumn('is_show_defective_rate');
            $table->dropColumn('is_show_plan_count');
            $table->dropColumn('is_show_achievement_rate');
            $table->dropColumn('is_show_standard_cycle_time');
            $table->dropColumn('is_show_time_operating_rate');
            $table->dropColumn('is_show_performance_operating_rate');
            $table->dropColumn('is_show_overall_equipment_effectiveness');
        });
    }
};
