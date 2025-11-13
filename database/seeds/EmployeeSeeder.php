<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Employee;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        Employee::create([
            'nip' => '198001012005011001',
            'nama' => 'Ahmad Fauzi',
            'substansi' => 'Tata Usaha'
        ]);

        Employee::create([
            'nip' => '198502022010022002',
            'nama' => 'Siti Rahayu',
            'substansi' => 'Infokom'
        ]);
    }
}