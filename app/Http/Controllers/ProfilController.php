<?php

namespace App\Http\Controllers;

use App\User;
use App\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfilController extends Controller
{
    /**
     * Menampilkan halaman profil utama dengan daftar semua pegawai
     */
    public function index()
    {
        $pegawais = User::with(['jabasn', 'divisi'])
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('profil', compact('pegawais'));
    }

    /**
     * Search pegawai berdasarkan NAMA atau NIP

     
     */
    public function search(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:255',
        ]);

        $searchQuery = trim($request->nip);

        // Cek apakah input adalah NIP murni (hanya angka)
        if (is_numeric($searchQuery)) {
            // Cari pegawai berdasarkan NIP yang exact
            $pegawai = User::where('no_pegawai', $searchQuery)
                ->with(['jabasn', 'divisi'])
                ->whereNull('deleted_at')
                ->first();

            if ($pegawai) {
                // Ambil riwayat kehadiran
                $kehadiran = Attendance::with('present')
                    ->where('nip', $searchQuery)
                    ->orderBy('submitted_at', 'desc')
                    ->paginate(10);

                // Log pencarian
                Log::info("Profil dicari untuk NIP: {$searchQuery}", [
                    'nama' => $pegawai->name,
                    'jabatan' => optional($pegawai->jabasn)->nama ?? '-',
                    'total_kehadiran' => $kehadiran->total(),
                    'user_id' => auth()->id(),
                ]);

                return view('profil', compact('pegawai', 'kehadiran'));
            }
        }

        // Jika input adalah nama atau NIP tidak ditemukan, tampilkan daftar dengan pencarian
        $searchResults = User::where(function ($builder) use ($searchQuery) {
                $builder->where('name', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('no_pegawai', 'LIKE', "%{$searchQuery}%");
            })
            ->with(['jabasn', 'divisi'])
            ->whereNull('deleted_at')
            ->orderByRaw("CASE 
                WHEN name LIKE ? THEN 0
                WHEN no_pegawai LIKE ? THEN 1
                ELSE 2 
            END", ["%{$searchQuery}%", "%{$searchQuery}%"])
            ->get();

        // Ambil pegawai lainnya (yang tidak sesuai pencarian)
        $otherPegawais = User::with(['jabasn', 'divisi'])
            ->whereNull('deleted_at')
            ->where(function ($builder) use ($searchQuery) {
                $builder->where('name', 'NOT LIKE', "%{$searchQuery}%")
                        ->where('no_pegawai', 'NOT LIKE', "%{$searchQuery}%");
            })
            ->orderBy('name', 'asc')
            ->get();

        // Gabungkan: hasil pencarian paling atas, diikuti pegawai lainnya
        $allPegawais = $searchResults->concat($otherPegawais);

        // Buat pagination manual
        $pegawais = new \Illuminate\Pagination\Paginator(
            $allPegawais->forPage(
                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                15
            ),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => \Illuminate\Support\Facades\Request::query(),
            ]
        );

        // Log pencarian
        Log::info("Pencarian pegawai: {$searchQuery}", [
            'user_id' => auth()->id(),
            'search_results' => $searchResults->count(),
        ]);

        return view('profil', compact('pegawais', 'searchQuery'));
    }

    /**
      
     */
    public function searchAjax(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $query = trim($query);

        // Cari pegawai yang cocok berdasarkan NAMA atau NIP
        $results = User::where(function ($builder) use ($query) {
                $builder->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('no_pegawai', 'LIKE', "{$query}%");
            })
            ->with(['jabasn', 'divisi'])
            ->whereNull('deleted_at')
            ->orderByRaw("CASE 
                WHEN name LIKE ? THEN 0
                WHEN no_pegawai LIKE ? THEN 1
                ELSE 2 
            END", ["{$query}%", "{$query}%"])
            ->select('no_pegawai', 'name', 'jkel')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'no_pegawai' => $user->no_pegawai,
                    'name' => $user->name,
                    'jabatan' => optional($user->jabasn)->nama ?? '-',
                ];
            });

        return response()->json(['data' => $results]);
    }
}