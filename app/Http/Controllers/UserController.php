<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * ✅ API untuk mencari pegawai berdasarkan NIP
     * Digunakan oleh form absensi untuk lookup data pegawai
     */
    public function findByNip($nip)
    {
        try {
            // ✅ Query user dengan relasi divisi dan jabasn
            $user = User::with(['divisi', 'jabasn'])
                ->where('no_pegawai', $nip)
                ->first();

            if (!$user) {
                Log::warning("Pegawai tidak ditemukan dengan NIP: {$nip}");
                return response()->json([
                    'success' => false, 
                    'message' => 'Pegawai tidak ditemukan.'
                ], 404);
            }

            // ✅ Format jenis kelamin
            $jenisKelamin = '-';
            if ($user->jkel === 'L') {
                $jenisKelamin = 'Laki-laki';
            } elseif ($user->jkel === 'P') {
                $jenisKelamin = 'Perempuan';
            }

            // ✅ Return data pegawai
            return response()->json([
                'success' => true,
                'data' => [
                    'no_pegawai' => $user->no_pegawai,
                    'nama' => $user->name ?? $user->name,
                    'jabatan' => $user->jabasn->nama ?? '-',
                    'divisi' => $user->divisi->nama ?? '-',
                    'substansi' => $user->divisi->nama ?? '-',
                    'unit_kerja' => 'Badan POM Banjarbaru',
                    'jk' => $jenisKelamin,
                    'jenis_kelamin' => $jenisKelamin,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error findByNip: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan server.'
            ], 500);
        }
    }
}