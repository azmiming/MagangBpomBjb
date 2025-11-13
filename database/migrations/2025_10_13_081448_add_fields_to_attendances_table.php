<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status_kepegawaian')->nullable();
            $table->string('nip')->nullable();
            $table->string('nama')->nullable();
            $table->string('substansi')->nullable();
            $table->string('tipe_kehadiran')->nullable();
            $table->string('bukti_selfie')->nullable();
            $table->string('tanda_tangan')->nullable();
            $table->dateTime('tanggal_absen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'status_kepegawaian',
                'nip',
                'nama',
                'substansi',
                'tipe_kehadiran',
                'bukti_selfie',
                'tanda_tangan',
                'tanggal_absen',
            ]);
        });
    }
}
