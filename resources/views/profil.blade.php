@extends('layouts.app')

@section('title', 'Profil Pegawai')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold text-success mb-3"><i class="fas fa-user-circle"></i> Profil Pegawai</h2>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil</li>
        </ol>
    </nav>

    {{-- FORM CARI NAMA --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('profil.search') }}" id="searchByNameForm">
                @csrf
                <label for="search_nama" class="form-label fw-semibold">Cari Pegawai Berdasarkan Nama</label>
                <div class="position-relative">
                    <div class="input-group">
                        <input type="text" name="nip" id="search_nama" class="form-control" 
                               placeholder="Ketik nama pegawai..." autocomplete="off">
                        <button type="submit" class="btn btn-success"><i class="fas fa-search"></i> Cari</button>
                    </div>
                    <div id="search_results" class="list-group" style="position: absolute; width: 100%; max-height: 300px; overflow-y: auto; top: 100%; left: 0; z-index: 1050; display: none; margin-top: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                </div>
            </form>
        </div>
    </div>

    {{-- ERROR MESSAGE --}}
    @if($errors->has('error') || session()->has('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('error') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- DATA PROFIL DETAIL --}}
    @if(isset($pegawai))
        @php
            $jk = $pegawai->jkel ?? null;
            
            if ($jk == 'L') {
                $jenisKelamin = 'Laki-laki';
            } elseif ($jk == 'P') {
                $jenisKelamin = 'Perempuan';
            } else {
                $jenisKelamin = '-';
            }
            
            $namaLengkap = $pegawai->name ?? 'Nama tidak tersedia';
            $unitKerja = $pegawai->unit_kerja ?? 'Balai Besar Pom di Banjarbaru';
            $jabatanNama = optional($pegawai->jabasn)->nama ?? '-';
            $divisiNama = optional($pegawai->divisi)->nama ?? '-';
        @endphp

        {{-- KARTU PROFIL --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold mb-0">{{ $namaLengkap }}</h4>
                        <p class="text-muted mb-0">{{ $jabatanNama }}</p>
                    </div>
                    <button type="button" class="btn btn-success" onclick="exportProfilToExcel()" title="Export ke Excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Nama Lengkap:</strong> 
                                <span>{{ $namaLengkap }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>NIP / No Pegawai:</strong> 
                                <span>{{ $pegawai->no_pegawai ?? '-' }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Jabatan:</strong> 
                                <span>{{ $jabatanNama }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Divisi / Bagian:</strong> 
                                <span>{{ $divisiNama }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Unit Kerja:</strong> 
                                <span>{{ $unitKerja }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Jenis Kelamin:</strong> 
                                <span>{{ $jenisKelamin }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIWAYAT KEHADIRAN --}}
        <div class="card border-0 shadow-sm mb-5">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="fw-bold text-primary mb-0"><i class="fas fa-calendar-check"></i> Riwayat Kehadiran</h4>
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        {{ $kehadiran->total() ?? 0 }} Acara
                    </span>
                </div>

                @if($kehadiran->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Acara</th>
                                    <th>Tanggal Submit</th>
                                    <th>Status Kehadiran</th>
                                    <th>Status Pegawai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kehadiran as $k)
                                    @php
                                        $namaAcara = optional($k->present)->acara ?? ($k->token_present ?? 'Acara Tidak Diketahui');
                                        $submittedFormatted = $k->submitted_at 
                                            ? \Carbon\Carbon::parse($k->submitted_at)->translatedFormat('d F Y H:i') 
                                            : '-';
                                        $status = strtolower($k->kehadiran_status ?? '');
                                        
                                        if (in_array($status, ['hadir', 'daring-wfh', 'daring-wfo'])) {
                                            $badgeClass = 'bg-primary';
                                        } elseif (in_array($status, ['sakit', 'cuti', 'dinas', 'izin'])) {
                                            $badgeClass = 'bg-warning text-dark';
                                        } else {
                                            $badgeClass = 'bg-secondary';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $namaAcara }}</td>
                                        <td>{{ $submittedFormatted }}</td>
                                        <td>
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($k->kehadiran_status ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                {{ ucfirst($k->status ?? 'pegawai') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($kehadiran->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $kehadiran->links() }}
                        </div>
                    @endif
                @else
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Belum ada riwayat kehadiran untuk NIP ini.
                    </div>
                @endif
            </div>
        </div>

    @else
        {{-- DAFTAR SEMUA PEGAWAI --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Daftar Pegawai</h5>
            </div>
            <div class="card-body">
                @if(isset($pegawais) && $pegawais->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Divisi</th>
                                    <th>Jabatan</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pegawais as $key => $pegawai)
                                    @php
                                        $divisiNama = optional($pegawai->divisi)->nama ?? '-';
                                        $jabatanNama = optional($pegawai->jabasn)->nama ?? '-';
                                    @endphp
                                    <tr>
                                        <td>{{ $pegawais->firstItem() + $key }}</td>
                                        <td><strong>{{ $pegawai->no_pegawai ?? '-' }}</strong></td>
                                        <td>{{ $pegawai->name ?? '-' }}</td>
                                        <td>{{ $divisiNama }}</td>
                                        <td>{{ $jabatanNama }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('profil.search') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="nip" value="{{ $pegawai->no_pegawai }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($pegawais->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pegawais->links() }}
                        </div>
                    @endif
                @else
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> Tidak ada data pegawai.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<style>
    .card {
        border-radius: 14px;
        transition: all 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.08);
    }
    .profile-avatar img {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .avatar-placeholder {
        width: 80px;
        height: 80px;
        background-color: #28a745;
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
    }
    .list-group-item {
        background: transparent;
        border: none;
        padding-left: 0;
        padding-right: 0;
        font-size: 0.95rem;
    }
    .list-group-item strong {
        color: #198754;
        width: 160px;
        display: inline-block;
    }
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    .btn-sm {
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    $(document).ready(function() {
        let searchTimeout;
        
        $('#search_nama').on('keyup', function() {
            clearTimeout(searchTimeout);
            let query = $(this).val();
            
            if (query.length < 2) {
                $('#search_results').empty().hide();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("profil.searchAjax") }}',
                    type: 'GET',
                    data: { q: query },
                    success: function(response) {
                        let results = response.data;
                        let html = '';
                        
                        if (results.length > 0) {
                            results.forEach(function(item) {
                                html += '<a href="#" class="list-group-item list-group-item-action search-result-item" data-nip="' + item.no_pegawai + '" data-name="' + item.name + '"><div class="d-flex w-100 justify-content-between"><strong>' + item.name + '</strong></div><small class="text-muted">NIP: ' + item.no_pegawai + ' | Jabatan: ' + item.jabatan + '</small></a>';
                            });
                        } else {
                            html = '<div class="list-group-item text-muted text-center">Tidak ada hasil</div>';
                        }
                        
                        $('#search_results').html(html).show();
                    },
                    error: function() {
                        $('#search_results').html('<div class="list-group-item text-danger text-center">Error mencari data</div>').show();
                    }
                });
            }, 300);
        });
        
        $(document).on('click', '.search-result-item', function(e) {
            e.preventDefault();
            let nip = $(this).data('nip');
            let name = $(this).data('name');
            
            $('#search_nama').val(name);
            $('#search_results').empty().hide();
            $('#searchByNameForm').submit();
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#search_nama, #search_results').length) {
                $('#search_results').hide();
            }
        });
    });

   function exportProfilToExcel() {
    var namaLengkap = '{{ $namaLengkap ?? "Pegawai" }}';
    var noPegawai = '{{ $pegawai->no_pegawai ?? "-" }}';
    var jabatan = '{{ $jabatanNama ?? "-" }}';
    var divisi = '{{ $divisiNama ?? "-" }}';
    var unitKerja = '{{ $unitKerja ?? "-" }}';
    var jenisKelamin = '{{ $jenisKelamin ?? "-" }}';

    // ===== Warna Tema Modern =====
    const theme = {
        primary: '228B22',    // hijau tua
        header: 'C6EFCE',     // hijau muda
        border: 'A9A9A9',
        white: 'FFFFFF',
        altRow: 'F9F9F9'
    };

    var wb = XLSX.utils.book_new();

    // Ambil data kehadiran dari tabel HTML
    var tableRows = document.querySelectorAll('.table tbody tr');
    var dataKehadiran = [];
    var no = 1;
    tableRows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            dataKehadiran.push([
                no++,
                cells[0].innerText,
                cells[1].innerText,
                cells[2].innerText,
                cells[3].innerText
            ]);
        }
    });

    if (dataKehadiran.length === 0) {
        alert('Pegawai ini belum memiliki riwayat kehadiran.');
        return;
    }

    // ===== Data Awal =====
    var gabunganData = [
        ['', '', 'BALAI BESAR POM DI BANJARBARU', '', ''],
        ['', '', 'LAPORAN PROFIL & KEHADIRAN PEGAWAI', '', ''],
        ['', '', 'Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID'), '', ''],
        [],
        ['', 'Nama Lengkap', namaLengkap, '', ''],
        ['', 'NIP / No Pegawai', noPegawai, '', ''],
        ['', 'Jabatan', jabatan, '', ''],
        ['', 'Divisi / Bagian', divisi, '', ''],
        ['', 'Unit Kerja', unitKerja, '', ''],
        ['', 'Jenis Kelamin', jenisKelamin, '', ''],
        [],
        ['No', 'Nama Acara', 'Tanggal Submit', 'Status Kehadiran', 'Status Pegawai']
    ];

    // ===== Tambah Data Kehadiran =====
    dataKehadiran.forEach(function(row) {
        gabunganData.push(row);
    });

    // ===== Buat Sheet =====
    var ws = XLSX.utils.aoa_to_sheet(gabunganData);

    // Lebar kolom
    ws['!cols'] = [
        { wch: 8 },
        { wch: 30 },
        { wch: 25 },
        { wch: 22 },
        { wch: 20 }
    ];

    // ===== Border & Style =====
    var borderThin = {
        top: { style: 'thin', color: { rgb: theme.border } },
        bottom: { style: 'thin', color: { rgb: theme.border } },
        left: { style: 'thin', color: { rgb: theme.border } },
        right: { style: 'thin', color: { rgb: theme.border } }
    };

    // Merge untuk judul
    ws['!mergedCells'] = [
        { s: { r: 0, c: 1 }, e: { r: 0, c: 3 } },
        { s: { r: 1, c: 1 }, e: { r: 1, c: 3 } },
        { s: { r: 2, c: 1 }, e: { r: 2, c: 3 } }
    ];

    // Styling judul
    ['B1', 'B2'].forEach(c => {
        if (ws[c]) {
            ws[c].font = { bold: true, size: (c === 'B1' ? 16 : 12), color: { rgb: theme.primary } };
            ws[c].alignment = { horizontal: 'center', vertical: 'center' };
        }
    });
    if (ws['B3']) {
        ws['B3'].font = { italic: true, size: 10, color: { rgb: '808080' } };
        ws['B3'].alignment = { horizontal: 'center', vertical: 'center' };
    }

    // Styling data profil
    for (let row = 5; row <= 10; row++) {
        if (ws['B' + row]) {
            ws['B' + row].fill = { fgColor: { rgb: theme.primary }, patternType: 'solid' };
            ws['B' + row].font = { bold: true, color: { rgb: theme.white } };
            ws['B' + row].alignment = { horizontal: 'left', vertical: 'center' };
        }
        if (ws['C' + row]) {
            ws['C' + row].alignment = { horizontal: 'left', vertical: 'center' };
            ws['C' + row].border = borderThin;
        }
    }

    // ===== Styling Header Tabel =====
    var headerRow = 12;
    ['A', 'B', 'C', 'D', 'E'].forEach(col => {
        let cell = col + headerRow;
        if (ws[cell]) {
            ws[cell].fill = { fgColor: { rgb: theme.primary }, patternType: 'solid' };
            ws[cell].font = { bold: true, color: { rgb: theme.white } };
            ws[cell].alignment = { horizontal: 'center', vertical: 'center' };
            ws[cell].border = borderThin;
        }
    });

    // ===== Styling Data Kehadiran =====
    var startDataRow = headerRow + 1;
    for (let i = 0; i < dataKehadiran.length; i++) {
        let row = startDataRow + i;
        ['A', 'B', 'C', 'D', 'E'].forEach(col => {
            let cell = col + row;
            if (ws[cell]) {
                ws[cell].font = { size: 10 };
                ws[cell].alignment = { horizontal: 'left', vertical: 'center', wrapText: true };
                ws[cell].border = borderThin;
                ws[cell].fill = {
                    fgColor: { rgb: i % 2 === 0 ? theme.altRow : theme.white },
                    patternType: 'solid'
                };
            }
        });
    }

    // ===== Auto Filter + Freeze Header =====
    let endRow = startDataRow + dataKehadiran.length - 1;
    ws['!autofilter'] = { ref: `A${headerRow}:E${endRow}` };
    ws['!freeze'] = { xSplit: 0, ySplit: headerRow };

    // ===== Footer =====
    let footerRow = endRow + 3;
    ws['B' + footerRow] = { t: 's', v: 'Dibuat otomatis oleh Sistem Presensi BBPOM Banjarbaru' };
    ws['B' + footerRow].font = { italic: true, size: 9, color: { rgb: '808080' } };

    // Simpan sheet
    XLSX.utils.book_append_sheet(wb, ws, 'Profil & Kehadiran');

    var fileName = `${namaLengkap.replace(/\s+/g, '_')}_${noPegawai.replace(/\//g, '-')}.xlsx`;
    XLSX.writeFile(wb, fileName);
}

</script>
@endsection