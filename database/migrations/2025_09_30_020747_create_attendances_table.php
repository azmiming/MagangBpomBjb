<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('token_present');
            $table->string('status'); // 'pegawai' atau 'non_pegawai'
            $table->string('nip')->nullable(); // non-pegawai tidak punya NIP
            $table->string('nama');
            $table->string('substansi');
            $table->string('bukti_path')->nullable();
            $table->longText('signature')->nullable(); // base64 tanda tangan
            $table->timestamp('submitted_at')->nullable(); // waktu submit
            $table->timestamps(); // created_at dan updated_at
            $table->string('kehadiran_status')->default('hadir'); // hadiri atau tidak_hadir

        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
