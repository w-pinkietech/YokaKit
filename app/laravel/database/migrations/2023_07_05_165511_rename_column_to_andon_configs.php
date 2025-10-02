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
            $table->renameColumn('is_show_standard_cycle_time', 'is_show_cycle_time');
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
            $table->renameColumn('is_show_cycle_time', 'is_show_standard_cycle_time');
        });
    }
};
