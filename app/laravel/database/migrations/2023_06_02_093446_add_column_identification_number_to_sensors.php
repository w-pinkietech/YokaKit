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
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign('sensors_raspberry_pi_id_foreign');
            $table->integer('identification_number', false, true)->after('raspberry_pi_id')->comment(__('yokakit.identification_number'));
            $table->string('alias', 128)->nullable(false)->comment(__('yokakit.alarm_text'))->change();
            $table->unique(['raspberry_pi_id', 'identification_number']);
            $table->index(['raspberry_pi_id', 'identification_number']);
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign('sensors_raspberry_pi_id_foreign');
            $table->dropUnique('sensors_raspberry_pi_id_identification_number_unique');
            $table->dropIndex('sensors_raspberry_pi_id_identification_number_index');
            $table->dropColumn('identification_number');
            $table->string('alias', 32)->nullable()->comment(__('yokakit.alarm_text'))->change();
            $table->foreign('raspberry_pi_id')->references('raspberry_pi_id')->on('raspberry_pis')->cascadeOnDelete();
        });
    }
};
