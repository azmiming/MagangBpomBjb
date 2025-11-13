<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('divisi_id')->nullable()->after('status');
        $table->unsignedBigInteger('jabasn_id')->nullable()->after('jabatan_id');

        $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
        $table->foreign('jabasn_id')->references('id')->on('jabasns')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
