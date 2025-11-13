<?php

use Illuminate\Database\Seeder;
use App\Models\Pegawai; // Pastikan model Pegawai ada di App\Models

class PegawaiSeeder extends Seeder
{
    public function run()
    {
        Pegawai::create([
            'nip' => '198001012005011001',
            'nama' => 'Andi Surya',
            'jabatan' => 'Kepala Sub Bagian'
        ]);

        Pegawai::create([
            'nip' => '197503152003012002',
            'nama' => 'Budi Santoso',
            'jabatan' => 'Staf Ahli'
        ]);
    }
}