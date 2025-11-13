<?php

namespace App\Http\Controllers;

use App\Present;
use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PresentController extends Controller
{
    public function index()
    {
        $presents = Present::latest()->get();
        return view('present', compact('presents'));
    }

    public function generate(Request $request)
    {
        // Daftar semua status kehadiran yang diizinkan (gabungan Luring + Daring)
        $allowedStatuses = [
            'hadir',
            'sakit',
            'cuti',
            'dinas',
            'izin',
            'hadir(daring)',
            'Daring-WFH',
            'Daring-WFO'
        ];

        $request->validate([
            'acara'            => 'required|string|max:255',
            'tanggal'          => 'required|date',
             'lokasi'           => 'nullable|string|max:255', // ✅ tambahkan ini
            'status'           => 'required|in:open,close',
            'tipe'             => 'required|array',
            'tipe.*'           => 'in:selfie,tanda_tangan',
            'metode_kehadiran' => 'required|in:luring,daring',
            'jam_buka_jam'     => 'required|integer|min:0|max:23',
            'jam_buka_menit'   => 'required|integer|min:0|max:59',
            'status_kehadiran' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $allowed = [
                        'hadir', 'sakit', 'cuti', 'dinas', 'izin', 'hadir(daring)',
                        'Daring-WFH', 'Daring-WFO'
                    ];
                    foreach ($value as $item) {
                        if (!in_array($item, $allowed)) {
                            $fail("Status kehadiran '{$item}' tidak dikenali.");
                            return;
                        }
                    }
                    if (empty($value)) {
                        $fail('Minimal pilih satu status kehadiran.');
                    }
                }
            ]
        ]);
        $jam_buka = sprintf('%02d:%02d', $request->jam_buka_jam, $request->jam_buka_menit);

        // Perbaikan: Filter status kehadiran berdasarkan metode kehadiran sebelum menyimpan
        $statusKehadiran = $request->input('status_kehadiran');
        if ($request->metode_kehadiran === 'luring') {
            // Untuk luring, kecualikan status daring (yang mengandung 'daring' atau 'Daring')
            $statusKehadiran = array_filter($statusKehadiran, function ($status) {
                return !preg_match('/daring/i', $status); // Case-insensitive match
            });
        } elseif ($request->metode_kehadiran === 'daring') {
            // Untuk daring, hanya sertakan status daring (yang mengandung 'daring' atau 'Daring')
            $statusKehadiran = array_filter($statusKehadiran, function ($status) {
                return preg_match('/daring/i', $status); // Case-insensitive match
            });
        }
        // Jika metode tidak luring/daring, gunakan semua (fallback)

        do {
            $token = Str::random(10);
        } while (Present::where('token', $token)->exists());

        $present = Present::create([
            'acara'             => $request->acara,
            'tanggal'           => $request->tanggal,
            'lokasi'            => $request->lokasi, // ✅ simpan lokasi
            'status'            => $request->status,
            'tipe'              => implode(',', $request->input('tipe')),
            'status_kehadiran'  => implode(',', $statusKehadiran), // Gunakan yang sudah difilter
            'metode_kehadiran'  => $request->metode_kehadiran,
            'jam_buka'          => $jam_buka,
            'token'             => $token,
        ]);

        return redirect()->route('present.index')
            ->with('success', 'Link absensi berhasil dibuat!');
    }

    public function findByNip($nip)
    {
        if (empty($nip)) {
            return response()->json([
                'success' => false,
                'message' => 'NIP tidak boleh kosong'
            ], 400);
        }

        $user = User::where('no_pegawai', $nip)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'no_pegawai' => $user->no_pegawai,
                    'name' => $user->name ?? 'Nama tidak tersedia',
                    'jabatan' => $user->jabatan ?? null,
                    'divisi' => $user->divisi ?? null,
                    'substansi' => $user->substansi ?? null,
                    'jk' => $user->jk ?? $user->jenis_kelamin ?? null,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pegawai tidak ditemukan'
        ], 404);
    }

    public function show($token)
    {
        $present = Present::where('token', $token)->firstOrFail();

        $now = now();
        $acaraDate = \Carbon\Carbon::parse($present->tanggal)->startOfDay();
        $waktuBuka = $acaraDate->copy()->setTimeFromTimeString($present->jam_buka);
        $isTimeToOpen = $now->gte($waktuBuka);

        if ($present->status === 'close') {
            $accessStatus = 'closed_manually';
        } elseif (!$isTimeToOpen) {
            $accessStatus = 'not_yet_open';
        } else {
            $accessStatus = 'open';
        }

        $tipeList = is_array($present->tipe) ? $present->tipe : explode(',', $present->tipe);
        $statusKehadiranList = is_array($present->status_kehadiran)
            ? $present->status_kehadiran
            : explode(',', $present->status_kehadiran);

        if (empty($statusKehadiranList)) {
            $statusKehadiranList = ['hadir'];
        }

        $statusKehadiranList = array_map('trim', $statusKehadiranList);

        // Perbaikan tambahan: Filter ulang berdasarkan metode kehadiran (lapisan kedua untuk aman)
        if ($present->metode_kehadiran === 'luring') {
            $statusKehadiranList = array_filter($statusKehadiranList, function ($status) {
                return !preg_match('/daring/i', $status);
            });
        } elseif ($present->metode_kehadiran === 'daring') {
            $statusKehadiranList = array_filter($statusKehadiranList, function ($status) {
                return preg_match('/daring/i', $status);
            });
        }

        return view('show-present', compact(
            'present',
            'token',
            'accessStatus',
            'waktuBuka',
            'tipeList',
            'statusKehadiranList'
        ));
    }

    public function submit(Request $request, $token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();

            $now = now();
            $acaraDate = \Carbon\Carbon::parse($present->tanggal)->startOfDay();
            $waktuBuka = $acaraDate->copy()->setTimeFromTimeString($present->jam_buka);
            $isTimeToOpen = $now->gte($waktuBuka);
            $effectiveStatus = ($present->status === 'open' && $isTimeToOpen) ? 'open' : 'close';

            if ($effectiveStatus !== 'open') {
                return redirect()->route('present.show', $token)
                    ->with('error', 'Absensi belum dibuka atau sudah ditutup.');
            }

            $tipeList = is_array($present->tipe) ? $present->tipe : explode(',', $present->tipe);
            $allowedStatuses = is_array($present->status_kehadiran)
                ? $present->status_kehadiran
                : explode(',', $present->status_kehadiran);

            if (empty($allowedStatuses)) {
                $allowedStatuses = ['hadir'];
            }

            $allowedStatuses = array_map('trim', $allowedStatuses);
            $kehadiranStatus = trim($request->kehadiran_status);

            if (!in_array($kehadiranStatus, $allowedStatuses)) {
                return back()->withErrors([
                    'kehadiran_status' => 'Status kehadiran yang dipilih tidak valid.'
                ])->withInput()->with('error', 'Status kehadiran tidak valid.');
            }

            $rules = [
                'status' => 'required|in:pegawai,non_pegawai',
                'nama' => 'required|string|max:255',
                'nip' => 'required|string|max:255',
            ];

            if (in_array('selfie', $tipeList)) {
                $rules['bukti'] = 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx|max:10240';
            }

            if (in_array('tanda_tangan', $tipeList)) {
                $rules['signature'] = 'required|string';
            }

            $request->validate($rules);

            // Cek duplikat
            $existing = Attendance::where('token_present', $token)
                ->where('nip', $request->nip)
                ->first();

            if ($existing) {
                return back()->with('error', 'NIP/NIK ini sudah mengisi absensi untuk acara ini.');
            }

            $attendance = new Attendance();
            $attendance->token_present = $token;
            $attendance->status = $request->status;
            $attendance->nip = $request->nip;
            $attendance->nama = $request->nama;
            $attendance->kehadiran_status = $kehadiranStatus;
            $attendance->metode_kehadiran = $present->metode_kehadiran;

            // Handle bukti (selfie/dokumen)
            if (in_array('selfie', $tipeList) && $request->hasFile('bukti')) {
                $file = $request->file('bukti');
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('bukti', $filename, 'public');
                $attendance->bukti_path = $path;
            }

            // Handle tanda tangan
            if (in_array('tanda_tangan', $tipeList) && $request->filled('signature')) {
                $attendance->signature = $request->signature;
            }

            // Isi data tambahan berdasarkan status
            if ($request->status === 'pegawai') {
                $user = User::where('no_pegawai', $request->nip)->first();
                if ($user) {
                    $attendance->jabatan = $user->jabasn ? $user->jabasn->nama : '-';
                    $attendance->divisi = $user->divisi ? $user->divisi->nama : '-';
                    $attendance->unit_kerja = 'Badan POM Banjarbaru';
                    $jk = $user->jk ?: $user->jkel ?: null;
                    if ($jk === 'L') {
                        $attendance->jenis_kelamin = 'Laki-laki';
                    } elseif ($jk === 'P') {
                        $attendance->jenis_kelamin = 'Perempuan';
                    } else {
                        $attendance->jenis_kelamin = $jk ?: '-';
                    }
                } else {
                    $attendance->jabatan = '-';
                    $attendance->divisi = '-';
                    $attendance->unit_kerja = 'BALAI BESAR POM DI BANJARBARU';
                    $attendance->jenis_kelamin = '-';
                }
            } else {
                $instansi = $request->instansi_non_pegawai ?: '-';
                $attendance->jabatan = '-';
                $attendance->divisi = '-';
                $attendance->unit_kerja = $instansi;
                $attendance->jenis_kelamin = $request->jkel ?: '-';
            }

            $attendance->submitted_at = now();
            $attendance->save();

            return redirect()->route('present.show', $token)
                ->with('success', 'Terima kasih! Absensi Anda berhasil tercatat.');
        } catch (\Exception $e) {
            Log::error('❌ Error submit absensi: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        $present = Present::findOrFail($id);
        return view('present-edit', compact('present'));
    }

    public function update(Request $request, $id)
    {
        $present = Present::findOrFail($id);

        $request->validate([
            'acara' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255', // ✅ tambahkan
            'jam_buka_jam' => 'required|integer|min:0|max:23',
            'jam_buka_menit' => 'required|integer|min:0|max:59'
        ]);

        $jam_buka = sprintf('%02d:%02d', $request->jam_buka_jam, $request->jam_buka_menit);

        $present->update([
            'acara' => $request->acara,
            'tanggal' => $request->tanggal,
            'lokasi' => $request->lokasi, // ✅ simpan lokasi
            'jam_buka' => $jam_buka,
        ]);

        return redirect()->route('present.index')->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function toggleStatus($token)
    {
        $present = Present::where('token', $token)->firstOrFail();
        $present->status = $present->status === 'open' ? 'close' : 'open';
        $present->save();

        $statusText = $present->status === 'open' ? 'dibuka' : 'ditutup';
        return back()->with('success', "Absensi '{$present->acara}' berhasil {$statusText}.");
    }
}  