<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attendance;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validasi sesuai kolom tabel attendances
        $request->validate([
            'status' => 'required|string',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'divisi' => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|string|max:50',
            'kehadiran_status' => 'nullable|string|max:20',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'signature' => 'required',
        ]);

        // ✅ Simpan file bukti kalau ada
        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $buktiPath = $file->storeAs('uploads/bukti', $filename, 'public');
        }

        // ✅ Buat token unik
        $token = Str::random(12);

        // ✅ Simpan ke database
        Attendance::create([
            'token_present'     => $token,
            'nama'              => $request->nama,
            'nip'               => $request->nip,
            'jabatan'           => $request->jabatan,
            'divisi'            => $request->divisi,
            'unit_kerja'        => $request->unit_kerja,
            'jenis_kelamin'     => $request->jenis_kelamin,
            'kehadiran_status'  => $request->kehadiran_status ?? 'hadir',
            'bukti_path'        => $buktiPath,
            'signature'         => $request->signature,
            'submitted_at'      => now(),
            'status'            => $request->status,
        ]);

        return redirect()->back()->with('success', 'Data kehadiran berhasil disimpan!');
    }
}
