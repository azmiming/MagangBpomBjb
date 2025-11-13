<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Present;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\AttendanceReportExport;
use App\Exports\AttendanceDetailExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan dengan daftar acara.
     */
    public function index()
    {
        $presents = Present::latest()->get();
        return view('report', compact('presents'));
    }

    /**
     * Menangani filter laporan - Search + Filter Rentang Waktu
     */
    public function filter(Request $request)
    {
        // Validasi opsional tapi direkomendasikan
        $request->validate([
            'search' => 'nullable|string|max:255',
            'filter3' => 'nullable|in:tanggal,bulan,tahun',
            'tahun' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'bulan' => 'nullable|date_format:Y-m',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Present::query();

        // 🔍 Pencarian berdasarkan nama acara
        if ($request->filled('search')) {
            $query->where('acara', 'LIKE', '%' . $request->search . '%');
        }

        // 📅 Filter berdasarkan rentang waktu
        $filter3 = $request->filter3;

        if ($filter3 === 'tahun' && $request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        } elseif ($filter3 === 'bulan' && $request->filled('bulan')) {
            // Input type="month" menghasilkan YYYY-MM
            $bulan = $request->bulan;
            $query->whereDate('tanggal', '>=', $bulan . '-01')
                  ->whereDate('tanggal', '<=', $bulan . '-31');
        } elseif ($filter3 === 'tanggal' && $request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal', [$start, $end]);
        }

        $presents = $query->latest()->get();

        return view('report', compact('presents'));
    }

    /**
     * Menampilkan detail acara dengan daftar kehadiran.
     */
    public function detail($token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();
            $attendances = Attendance::where('token_present', $token)
                ->orderBy('nama', 'asc')
                ->get();

            if ($attendances->isEmpty()) {
                $message = 'Belum ada data kehadiran untuk acara ini.';
                return view('report_detail', compact('present', 'attendances', 'message'));
            }

            return view('report_detail', compact('present', 'attendances'));
        } catch (\Exception $e) {
            abort(404, 'Acara tidak ditemukan.');
        }
    }

    /**
     * Menangani filter detail acara - Filter 1 & 2
     */
    public function detailFilter(Request $request, $token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();
            
            $filter1 = $request->filter1;  // Status Kehadiran
            $filter2 = $request->filter2;  // Divisi
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order ?? 'asc';

            // ==========================================
            // FILTER 1: TIDAK HADIR (User yang belum absen)
            // ==========================================
            if ($filter1 === 'tidak_hadir') {
                // Ambil semua user yang aktif
                $activeUsers = User::where('aktif', 'Y')
                    ->whereNull('deleted_at')
                    ->get();
                
                // Ambil user yang sudah memiliki attendance record
                $attendedUserNips = Attendance::where('token_present', $token)
                    ->pluck('nip')
                    ->toArray();
                
                // Filter user yang TIDAK di attendance records
                $notAttendedUsers = $activeUsers->filter(function ($user) use ($attendedUserNips) {
                    return !in_array($user->no_pegawai, $attendedUserNips);
                });

                // Apply Filter 2: Divisi (jika ada)
                if ($filter2) {
                    $notAttendedUsers = $notAttendedUsers->filter(function ($user) use ($filter2) {
                        // Ambil nama divisi dari relasi user
                        $divisiName = optional(optional($user)->divisi)->nama;
                        return $divisiName === $filter2;
                    });
                }

                // Konversi user ke format attendance untuk ditampilkan
                $attendances = $notAttendedUsers->map(function ($user) {
                    return (object) [
                        'nip' => $user->no_pegawai,
                        'nama' => $user->name ?? $user->namanogelar ?? '-',
                        'jabatan' => optional(optional($user)->jabasn)->nama ?? '-',
                        'divisi' => optional(optional($user)->divisi)->nama ?? '-',
                        'unit_kerja' => 'Badan POM Banjarbaru',
                        'kehadiran_status' => 'tidak_hadir',
                        'bukti_path' => null,
                        'signature' => null,
                        'submitted_at' => null,
                        'user_id' => $user->id,
                    ];
                });

                // Sorting
                if ($sortBy === 'nama') {
                    $attendances = $attendances->sortBy('nama', SORT_NATURAL, $sortOrder === 'desc');
                } elseif ($sortBy === 'nip') {
                    $attendances = $attendances->sortBy('nip', SORT_NATURAL, $sortOrder === 'desc');
                }

                $attendances = collect($attendances)->values();

            } else {
                // ==========================================
                // FILTER NORMAL: Berdasarkan kehadiran_status
                // ==========================================
                $query = Attendance::where('token_present', $token);

                // Filter 1: Jenis Kehadiran
                if ($filter1) {
                    if ($filter1 === 'hadir') {
                        $query->whereIn('kehadiran_status', [
                            'hadir',
                            'Daring-WFH',
                            'Daring-WFO',
                            'hadir(daring)'
                        ]);
                    } else {
                        $query->where('kehadiran_status', $filter1);
                    }
                }

                // Filter 2: Divisi
                if ($filter2) {
                    $query->where('divisi', $filter2);
                }

                // Sorting
                if ($sortBy === 'nama') {
                    $query->orderBy('nama', $sortOrder);
                } elseif ($sortBy === 'nip') {
                    $query->orderBy('nip', $sortOrder);
                } else {
                    $query->latest();
                }

                $attendances = $query->get();
            }
            
            $message = $attendances->isEmpty()
                ? 'Tidak ada data yang sesuai dengan filter.'
                : null;

            return view('report_detail', compact('present', 'attendances', 'message'));
        } catch (\Exception $e) {
            abort(404, 'Acara tidak ditemukan.');
        }
    }

    /**
     * Menampilkan halaman print untuk PDF cetak laporan kehadiran
     * Bisa langsung di-print ke PDF dari browser (Ctrl+P -> Save as PDF)
     */
    public function printPDF($token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();
            
            // Ambil filter dari request jika ada
            $filter1 = request('filter1');
            $filter2 = request('filter2');
            $sortBy = request('sort_by');
            $sortOrder = request('sort_order', 'asc');

            // ==========================================
            // FILTER 1: TIDAK HADIR (User yang belum absen)
            // ==========================================
            if ($filter1 === 'tidak_hadir') {
                // Ambil semua user yang aktif
                $activeUsers = User::where('aktif', 'Y')
                    ->whereNull('deleted_at')
                    ->get();
                
                // Ambil user yang sudah memiliki attendance record
                $attendedUserNips = Attendance::where('token_present', $token)
                    ->pluck('nip')
                    ->toArray();
                
                // Filter user yang TIDAK di attendance records
                $notAttendedUsers = $activeUsers->filter(function ($user) use ($attendedUserNips) {
                    return !in_array($user->no_pegawai, $attendedUserNips);
                });

                // Apply Filter 2: Divisi (jika ada)
                if ($filter2) {
                    $notAttendedUsers = $notAttendedUsers->filter(function ($user) use ($filter2) {
                        $divisiName = optional(optional($user)->divisi)->nama;
                        return $divisiName === $filter2;
                    });
                }

                // Konversi user ke format attendance untuk ditampilkan
                $attendances = $notAttendedUsers->map(function ($user) {
                    return (object) [
                        'nip' => $user->no_pegawai,
                        'nama' => $user->name ?? $user->namanogelar ?? '-',
                        'jabatan' => optional(optional($user)->jabasn)->nama ?? '-',
                        'divisi' => optional(optional($user)->divisi)->nama ?? '-',
                        'unit_kerja' => 'Badan POM Banjarbaru',
                        'kehadiran_status' => 'tidak_hadir',
                        'bukti_path' => null,
                        'signature' => null,
                        'submitted_at' => null,
                    ];
                });

                // Sorting
                if ($sortBy === 'nama') {
                    $attendances = $attendances->sortBy('nama', SORT_NATURAL, $sortOrder === 'desc');
                } elseif ($sortBy === 'nip') {
                    $attendances = $attendances->sortBy('nip', SORT_NATURAL, $sortOrder === 'desc');
                }

                $attendances = collect($attendances)->values();

            } else {
                // ==========================================
                // FILTER NORMAL
                // ==========================================
                $query = Attendance::where('token_present', $token);

                // Apply Filter 1
                if ($filter1) {
                    if ($filter1 === 'hadir') {
                        $query->whereIn('kehadiran_status', [
                            'hadir',
                            'Daring-WFH',
                            'Daring-WFO',
                            'hadir(daring)'
                        ]);
                    } else {
                        $query->where('kehadiran_status', $filter1);
                    }
                }

                // Apply Filter 2
                if ($filter2) {
                    $query->where('divisi', $filter2);
                }

                // Sorting
                if ($sortBy === 'nama') {
                    $query->orderBy('nama', $sortOrder);
                } elseif ($sortBy === 'nip') {
                    $query->orderBy('nip', $sortOrder);
                } else {
                    $query->latest();
                }

                $attendances = $query->get();
            }

            // Pass data ke view
            return view('report_print_pdf', compact('present', 'attendances', 'filter1', 'filter2'));
        } catch (\Exception $e) {
            abort(404, 'Acara tidak ditemukan.');
        }
    }

    /**
     * Ekspor laporan ke Excel (belum terintegrasi dengan filter halaman utama)
     */
    public function exportExcel(Request $request)
    {
        // TODO: Integrasi dengan filter utama jika diperlukan
        $attendances = collect();

        $attendances = $attendances->map(function ($item, $index) {
            $item->no = $index + 1;
            return $item;
        });

        return Excel::download(
            new AttendanceReportExport($attendances),
            'Laporan_Kehadiran_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Menampilkan halaman print untuk Excel cetak laporan kehadiran 
     * 
     */
    public function printExcel($token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();
            
            // Ambil filter dari request jika ada
            $filter1 = request('filter1');
            $filter2 = request('filter2');
            $sortBy = request('sort_by');
            $sortOrder = request('sort_order', 'asc');

            // ==========================================
            // FILTER 1: TIDAK HADIR (User yang belum absen)
            // ==========================================
            if ($filter1 === 'tidak_hadir') {
                // Ambil semua user yang aktif
                $activeUsers = User::where('aktif', 'Y')
                    ->whereNull('deleted_at')
                    ->get();
                
                // Ambil user yang sudah memiliki attendance record
                $attendedUserNips = Attendance::where('token_present', $token)
                    ->pluck('nip')
                    ->toArray();
                
                // Filter user yang TIDAK di attendance records
                $notAttendedUsers = $activeUsers->filter(function ($user) use ($attendedUserNips) {
                    return !in_array($user->no_pegawai, $attendedUserNips);
                });

                // Apply Filter 2: Divisi (jika ada)
                if ($filter2) {
                    $notAttendedUsers = $notAttendedUsers->filter(function ($user) use ($filter2) {
                        $divisiName = optional(optional($user)->divisi)->nama;
                        return $divisiName === $filter2;
                    });
                }

                // Konversi user ke format attendance untuk ditampilkan
                $attendances = $notAttendedUsers->map(function ($user) {
                    return (object) [
                        'nip' => $user->no_pegawai,
                        'nama' => $user->name ?? $user->namanogelar ?? '-',
                        'jabatan' => optional(optional($user)->jabasn)->nama ?? '-',
                        'divisi' => optional(optional($user)->divisi)->nama ?? '-',
                        'unit_kerja' => 'Badan POM Banjarbaru',
                        'kehadiran_status' => 'tidak_hadir',
                        'bukti_path' => null,
                        'signature' => null,
                        'submitted_at' => null,
                    ];
                });

                // Sorting
                if ($sortBy === 'nama') {
                    $attendances = $attendances->sortBy('nama', SORT_NATURAL, $sortOrder === 'desc');
                } elseif ($sortBy === 'nip') {
                    $attendances = $attendances->sortBy('nip', SORT_NATURAL, $sortOrder === 'desc');
                }

                $attendances = collect($attendances)->values();

            } else {
                // ==========================================
                // FILTER NORMAL
                // ==========================================
                $query = Attendance::where('token_present', $token);

                // Apply Filter 1
                if ($filter1) {
                    if ($filter1 === 'hadir') {
                        $query->whereIn('kehadiran_status', [
                            'hadir',
                            'Daring-WFH',
                            'Daring-WFO',
                            'hadir(daring)'
                        ]);
                    } else {
                        $query->where('kehadiran_status', $filter1);
                    }
                }

                // Apply Filter 2
                if ($filter2) {
                    $query->where('divisi', $filter2);
                }

                // Sorting
                if ($sortBy === 'nama') {
                    $query->orderBy('nama', $sortOrder);
                } elseif ($sortBy === 'nip') {
                    $query->orderBy('nip', $sortOrder);
                } else {
                    $query->latest();
                }

                $attendances = $query->get();
            }

            // Pass data ke view
            return view('report_print_excel', compact('present', 'attendances', 'filter1', 'filter2'));
        } catch (\Exception $e) {
            abort(404, 'Acara tidak ditemukan.');
        }
    }

    /**
     * Export Detail Kehadiran ke Excel dengan Gambar
     * Method untuk download Excel dengan bukti (daduk) dan tanda tangan tertanam
     */
    public function exportDetailExcel($token)
    {
        try {
            $present = Present::where('token', $token)->firstOrFail();
            
            // Ambil filter dari request
            $filter1 = request('filter1');
            $filter2 = request('filter2');
            $sortBy = request('sort_by');
            $sortOrder = request('sort_order', 'asc');

            // ==========================================
            // FILTER 1: TIDAK HADIR (User yang belum absen)
            // ==========================================
            if ($filter1 === 'tidak_hadir') {
                // Ambil semua user yang aktif
                $activeUsers = User::where('aktif', 'Y')
                    ->whereNull('deleted_at')
                    ->get();
                
                // Ambil user yang sudah memiliki attendance record
                $attendedUserNips = Attendance::where('token_present', $token)
                    ->pluck('nip')
                    ->toArray();
                
                // Filter user yang TIDAK di attendance records
                $notAttendedUsers = $activeUsers->filter(function ($user) use ($attendedUserNips) {
                    return !in_array($user->no_pegawai, $attendedUserNips);
                });

                // Apply Filter 2: Divisi (jika ada)
                if ($filter2) {
                    $notAttendedUsers = $notAttendedUsers->filter(function ($user) use ($filter2) {
                        $divisiName = optional(optional($user)->divisi)->nama;
                        return $divisiName === $filter2;
                    });
                }

                // Konversi user ke format attendance
                $attendances = $notAttendedUsers->map(function ($user) {
                    return (object) [
                        'nip' => $user->no_pegawai,
                        'nama' => $user->name ?? $user->namanogelar ?? '-',
                        'jabatan' => optional(optional($user)->jabasn)->nama ?? '-',
                        'divisi' => optional(optional($user)->divisi)->nama ?? '-',
                        'unit_kerja' => 'Badan POM Banjarbaru',
                        'kehadiran_status' => 'tidak_hadir',
                        'bukti_path' => null,
                        'signature' => null,
                        'submitted_at' => null,
                    ];
                });

                // Sorting
                if ($sortBy === 'nama') {
                    $attendances = $attendances->sortBy('nama', SORT_NATURAL, $sortOrder === 'desc');
                } elseif ($sortBy === 'nip') {
                    $attendances = $attendances->sortBy('nip', SORT_NATURAL, $sortOrder === 'desc');
                }

                $attendances = collect($attendances)->values();

            } else {
                
                // FILTER NORMAL: Berdasarkan kehadiran_status
              
                $query = Attendance::where('token_present', $token);

                // Filter 1: Jenis Kehadiran
                if ($filter1) {
                    if ($filter1 === 'hadir') {
                        $query->whereIn('kehadiran_status', [
                            'hadir',
                            'Daring-WFH',
                            'Daring-WFO',
                            'hadir(daring)'
                        ]);
                    } else {
                        $query->where('kehadiran_status', $filter1);
                    }
                }

                // Filter 2: Divisi
                if ($filter2) {
                    $query->where('divisi', $filter2);
                }

                // Sorting
                if ($sortBy === 'nama') {
                    $query->orderBy('nama', $sortOrder);
                } elseif ($sortBy === 'nip') {
                    $query->orderBy('nip', $sortOrder);
                } else {
                    $query->latest();
                }

                $attendances = $query->get();
            }

            // Jika tidak ada data
            if ($attendances->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data untuk diexport');
            }

            // Generate nama file
            $fileName = 'Detail_Hadir_' 
                . str_replace(' ', '_', $present->acara) 
                . '_' 
                . Carbon::now()->format('Y-m-d_His') 
                . '.xlsx';

            // Export menggunakan class AttendanceDetailExport
            return Excel::download(
                new AttendanceDetailExport($attendances, $present),
                $fileName
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi error: ' . $e->getMessage());
        }
    }

    /**
     * Mengunduh file bukti
     */
    public function downloadFile(Request $request)
    {
        $file = $request->query('file');

        if (empty($file)) {
            abort(400, 'Nama file tidak diberikan.');
        }

        $file = trim($file);

        if (strpos($file, '..') !== false || strpos($file, '\\') !== false) {
            abort(403, 'Akses ditolak.');
        }

        if (!preg_match('/^[a-zA-Z0-9_\-\/.]+$/', $file) || !Str::startsWith($file, 'bukti/')) {
            abort(400, 'Path file tidak valid.');
        }

        $filePath = public_path('storage/' . $file);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        if (!is_readable($filePath)) {
            abort(500, 'File tidak dapat diakses.');
        }

        return response()->download($filePath, basename($filePath));
    }
}