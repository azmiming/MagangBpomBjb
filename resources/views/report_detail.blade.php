@extends('layouts.app')

@section('title', 'Detail Acara - ' . $present->acara)

@section('content')
<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('report') }}">Laporan Kehadiran</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $present->acara }}</li>
        </ol>
    </nav>

    <!-- Detail Acara Header -->
    <div class="card shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="mb-3">📌 {{ $present->acara }}</h3>
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($present->tanggal)->format('d/m/Y') }}</p>
                    <p><strong>Lokasi:</strong> {{ $present->lokasi ?? '-' }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Metode:</strong> 
                        @if($present->metode_kehadiran === 'luring')
                            <span class="badge bg-primary">Luring</span>
                        @else
                            <span class="badge bg-info text-dark">Daring</span>
                        @endif
                    </p>
                    <p><strong>Status:</strong> 
                        @if($present->status === 'open')
                            <span class="badge bg-success">Terbuka</span>
                        @else
                            <span class="badge bg-danger">Tertutup</span>
                        @endif
                    </p>
                    <a href="{{ route('report') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter 1 & 2 -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filter Detail Kehadiran</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report.detail.filter', ['token' => $present->token]) }}" id="filterForm">
                @csrf
                <input type="hidden" name="sort_by" id="sort_by" value="{{ request('sort_by', '') }}">
                <input type="hidden" name="sort_order" id="sort_order" value="{{ request('sort_order', 'asc') }}">
                
                <div class="row g-3">
                    <!-- Filter 1: Jenis Kehadiran -->
                    <div class="col-md-4">
                        <label for="filter1" class="form-label">Filter 1: Jenis Kehadiran</label>
                        <select name="filter1" id="filter1" class="form-select form-select-sm">
                            <option value="">-- Semua --</option>
                            <option value="hadir" {{ request('filter1') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="tidak_hadir" {{ request('filter1') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="izin" {{ request('filter1') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('filter1') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="cuti" {{ request('filter1') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                            <option value="dinas" {{ request('filter1') == 'dinas' ? 'selected' : '' }}>Dinas</option>
                        </select>
                    </div>

                    <!-- Filter 2: Per Divisi -->
                    <div class="col-md-4">
                        <label for="filter2" class="form-label">Filter 2: Per Divisi</label>
                        <select name="filter2" id="filter2" class="form-select form-select-sm">
                            <option value="">-- Semua Divisi --</option>
                            <option value="BALAI BESAR DI POM BANJARBARU" {{ request('filter2') == 'BALAI BESAR DI POM BANJARBARU' ? 'selected' : '' }}>BALAI BESAR DI POM BANJARBARU</option>
                            <option value="TATA USAHA" {{ request('filter2') == 'TATA USAHA' ? 'selected' : '' }}>TATA USAHA</option>
                            <option value="PENINDAKAN" {{ request('filter2') == 'PENINDAKAN' ? 'selected' : '' }}>PENINDAKAN</option>
                            <option value="PENGUJIAN" {{ request('filter2') == 'PENGUJIAN' ? 'selected' : '' }}>PENGUJIAN</option>
                            <option value="INFORMASI DAN KOMUNIKASI" {{ request('filter2') == 'INFORMASI DAN KOMUNIKASI' ? 'selected' : '' }}>INFORMASI DAN KOMUNIKASI</option>
                            <option value="PEMERIKSAAN" {{ request('filter2') == 'PEMERIKSAAN' ? 'selected' : '' }}>PEMERIKSAAN</option>
                        </select>
                    </div>

                    <!-- Tombol Aksi di sini -->
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" form="filterForm" class="btn btn-primary btn-sm">
                            <i class="bi bi-filter-circle"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('report.detail', ['token' => $present->token]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Button Cetak & Export -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('report.print.pdf', [
                'token' => $present->token,
                'filter1' => request('filter1'),
                'filter2' => request('filter2'),
                'sort_by' => request('sort_by'),
                'sort_order' => request('sort_order')
            ]) }}" target="_blank" class="btn btn-warning btn-sm">
                <i class="bi bi-printer"></i> 📄 Cetak Daftar Hadir (PDF)
            </a>
        </div>
    </div>

<!-- Button Cetak & Export -->
<div>
    <div>
        <!-- Excel  -->
        <a href="{{ route('report.detail.export', [
            'token' => $present->token,
            'filter1' => request('filter1'),
            'filter2' => request('filter2'),
            'sort_by' => request('sort_by'),
            'sort_order' => request('sort_order')
        ]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel"></i> 📊 Export Excel (Dengan Gambar)
        </a>
    </div>
</div>
                
     

    <!-- Pesan -->
    @if(isset($message))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabel Daftar Kehadiran -->
    @if(isset($attendances) && $attendances->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Kehadiran ({{ $attendances->count() }} peserta)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 10%;">
                                    <a href="javascript:void(0)" onclick="sortTable('nip')" class="text-white text-decoration-none d-flex align-items-center gap-1">
                                        NIP/NIK
                                        @if(request('sort_by') == 'nip')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort" style="opacity: 0.5;"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="width: 15%;">
                                    <a href="javascript:void(0)" onclick="sortTable('nama')" class="text-white text-decoration-none d-flex align-items-center gap-1">
                                        Nama
                                        @if(request('sort_by') == 'nama')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort" style="opacity: 0.5;"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="width: 14%;">Jabatan</th>
                                <th style="width: 12%;">Divisi</th>
                                <th style="width: 8%;">Unit Kerja</th>
                                <th style="width: 10%;" class="text-center">Status Kehadiran</th>
                                <th style="width: 8%;" class="text-center">Daduk</th>
                                <th style="width: 8%;" class="text-center">TTD</th>
                                <th style="width: 13%;" class="text-center">Tanggal & Jam Submit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <small class="text-monospace font-monospace">{{ $attendance->nip ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $attendance->nama ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $attendance->jabatan ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $attendance->divisi ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $attendance->unit_kerja ?? 'Badan POM Banjarbaru' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $status = $attendance->kehadiran_status;
                                        @endphp
                                        @if($status === 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($status === 'izin')
                                            <span class="badge bg-warning text-dark">Izin</span>
                                        @elseif($status === 'sakit')
                                            <span class="badge bg-info text-dark">Sakit</span>
                                        @elseif($status === 'cuti')
                                            <span class="badge bg-primary">Cuti</span>
                                        @elseif($status === 'dinas')
                                            <span class="badge bg-secondary">Dinas</span>
                                        @elseif(in_array($status, ['Daring-WFH', 'Daring-WFO', 'hadir(daring)']))
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($status === 'tidak_hadir')
                                            <span class="badge bg-danger">Tidak Hadir</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $status }}</span>
                                        @endif
                                    </td>
                                    <!-- Kolom Selfie -->
                                    <td class="text-center">
                                        @if(!empty($attendance->bukti_path))
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <a href="{{ asset('storage/' . $attendance->bukti_path) }}" target="_blank" title="Lihat Selfie">
                                                    <img src="{{ asset('storage/' . $attendance->bukti_path) }}"
                                                         alt="Selfie"
                                                         class="img-thumbnail rounded-1"
                                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;">
                                                </a>
                                                <a href="{{ route('report.download', ['file' => $attendance->bukti_path]) }}"
                                                   class="btn btn-link btn-sm p-0"
                                                   title="Download Selfie">
                                                    <i class="fas fa-download" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <!-- Kolom Tanda Tangan -->
                                    <td class="text-center">
                                        @if(!empty($attendance->signature))
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                @if(Str::startsWith($attendance->signature, 'data:image'))
                                                    <img src="{{ $attendance->signature }}"
                                                         alt="TTD"
                                                         title="Lihat Tanda Tangan"
                                                         class="img-thumbnail rounded-1"
                                                         style="width: 40px; height: 30px; object-fit: contain; cursor: pointer;"
                                                         onclick="viewSignature(this)">
                                                    <button type="button" class="btn btn-link btn-sm p-0"
                                                            onclick="downloadSignature('{{ $attendance->signature }}', 'ttd_{{ $attendance->nip }}.png')"
                                                            title="Download Tanda Tangan">
                                                        <i class="fas fa-download" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                @else
                                                    <img src="{{ $attendance->signature }}"
                                                         alt="TTD"
                                                         title="Lihat Tanda Tangan"
                                                         class="img-thumbnail rounded-1"
                                                         style="width: 40px; height: 30px; object-fit: contain; cursor: pointer;"
                                                         onclick="window.open(this.src, '_blank')">
                                                    <a href="{{ route('report.download', ['file' => basename($attendance->signature)]) }}"
                                                       class="btn btn-link btn-sm p-0"
                                                       title="Download Tanda Tangan">
                                                        <i class="fas fa-download" style="font-size: 0.75rem;"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <!-- Kolom Tanggal & Jam Submit -->
                                    <td class="text-center">
                                        @if($attendance->submitted_at)
                                            <small>
                                                <div><strong>{{ \Carbon\Carbon::parse($attendance->submitted_at)->format('d/m/Y') }}</strong></div>
                                                <div style="color: #0d6efd; font-weight: 500;">{{ \Carbon\Carbon::parse($attendance->submitted_at)->format('H:i:s') }}</div>
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center" role="alert">
            <i class="bi bi-exclamation-triangle"></i> Tidak ada data kehadiran untuk acara ini.
        </div>
    @endif
</div>

<!-- FontAwesome CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

<style>
    /* Override Bootstrap untuk tabel lebih baik */
    .table-sm {
        font-size: 0.85rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .text-monospace {
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 100;
    }

    thead th a {
        cursor: pointer;
        text-decoration: none;
    }

    thead th a:hover {
        opacity: 0.8;
    }

    /* Responsive untuk mobile */
    @media (max-width: 768px) {
        .table-sm {
            font-size: 0.75rem;
        }
    }

    /* Scroll bar styling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Badge styling */
    .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }

    /* Button styling */
    .btn-sm {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }

    /* Alert styling */
    .alert {
        border-radius: 6px;
        font-size: 0.95rem;
    }

    /* Form styling */
    .form-select-sm {
        font-size: 0.85rem;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }

    /* Styling untuk waktu submit */
    .submit-time {
        display: block;
        color: #0d6efd;
        font-weight: 500;
        margin-top: 2px;
    }
</style>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<script>
    // Fungsi untuk sort table
    function sortTable(column) {
        const sortByInput = document.getElementById('sort_by');
        const sortOrderInput = document.getElementById('sort_order');
        const currentSortBy = sortByInput.value;
        const currentSortOrder = sortOrderInput.value;

        if (currentSortBy === column) {
            sortOrderInput.value = currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            sortByInput.value = column;
            sortOrderInput.value = 'asc';
        }

        document.getElementById('filterForm').submit();
    }

    // Fungsi view signature
    function viewSignature(element) {
        const src = element.getAttribute('src');
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            cursor: pointer;
        `;
        
        const img = document.createElement('img');
        img.src = src;
        img.style.cssText = `
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        `;
        
        modal.appendChild(img);
        modal.onclick = () => modal.remove();
        document.body.appendChild(modal);
    }

    // Fungsi download signature
    function downloadSignature(dataUrl, filename) {
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

@endsection