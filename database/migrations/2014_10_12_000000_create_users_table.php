<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint UNSIGNED NOT NULL AUTO_INCREMENT
            $table->string('no_pegawai', 50)->unique(); // pengganti 'nip'
            $table->string('name', 255);
            $table->string('username', 255)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('tempat_lhr', 255)->nullable();
            $table->date('tgl_lhr');
            $table->text('alamat')->nullable();
            $table->enum('nikah', ['Y', 'N'])->nullable();
            $table->enum('jkel', ['P', 'L'])->nullable();
            $table->string('telp', 13)->nullable();
            $table->integer('jabatan_id')->default(0);
            $table->integer('jabasn_id')->default(0);
            $table->string('seri_karpeg', 50)->nullable();
            $table->string('status', 13);
            $table->integer('divisi_id');
            $table->integer('subdivisi_id')->nullable();
            $table->integer('golongan_id')->nullable();
            $table->string('foto', 100)->default('');
            $table->rememberToken(); // varchar(100) nullable
            $table->timestamps(); // created_at, updated_at
            $table->enum('aktif', ['Y', 'N'])->nullable();
            $table->string('deskjob', 250);
            $table->date('TMT_Capeg')->nullable();
            $table->string('namanogelar', 250)->nullable();
            $table->string('agama', 50)->nullable();
            $table->softDeletes(); // deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};