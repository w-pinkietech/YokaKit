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
            $table->dropColumn('offset_millisecond');
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
            // オフセットミリ秒
            $table->integer('offset_millisecond')->default(0)->after('count')->comment(__('yokakit.offset') . 'ms');
        });
    }
};
