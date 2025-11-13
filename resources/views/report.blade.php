@extends('layouts.app')

@section('title', 'Laporan Kehadiran')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">📋 Laporan Kehadiran</h2>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Report</li>
        </ol>
    </nav>

    <!-- Filter: Waktu + Pencarian Nama Acara -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report.filter') }}" id="filterForm">
                @csrf
                
                <div class="row g-3">
                    <!-- Search Nama Acara -->
                    <div class="col-md-6">
                        <label for="search" class="form-label">Cari Nama Acara</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Masukkan nama acara..." value="{{ request('search') }}">
                    </div>

                    <!-- Filter 3: Waktu -->
                    <div class="col-md-6">
                        <label for="filter3" class="form-label">Filter: Rentang Waktu</label>
                        <select name="filter3" id="filter3" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="tahun" {{ request('filter3') == 'tahun' ? 'selected' : '' }}>Pertahun</option>
                            <option value="bulan" {{ request('filter3') == 'bulan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="tanggal" {{ request('filter3') == 'tanggal' ? 'selected' : '' }}>Pertanggal</option>
                        </select>
                    </div>

                    <!-- Field Tahun -->
                    <div class="col-md-4" id="tahun-field" style="display:none;">
                        <label for="tahun" class="form-label">Tahun</label>
                        <input type="number" name="tahun" id="tahun" class="form-control" min="2000" max="{{ date('Y') + 1 }}" value="{{ request('tahun', date('Y')) }}">
                    </div>

                    <!-- Field Bulan -->
                    <div class="col-md-4" id="bulan-field" style="display:none;">
                        <label for="bulan" class="form-label">Bulan</label>
                        <input type="month" name="bulan" id="bulan" class="form-control" value="{{ request('bulan') }}">
                    </div>

                    <!-- Field Tanggal -->
                    <div class="col-md-4" id="tanggal-field" style="display:none;">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4" id="end-date-field" style="display:none;">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" form="filterForm" class="btn btn-primary">
                            <i class="bi bi-filter-circle"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('report') }}" class="btn btn-secondary ms-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Acara -->
    @if(isset($presents) && $presents->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Daftar Acara</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Acara</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Tipe Verifikasi</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presents as $present)
                            @php
                                $tipeList = is_array($present->tipe) ? $present->tipe : explode(',', $present->tipe);
                                $labelList = array_map(fn($t) => $t === 'selfie' ? 'Selfie' : ($t === 'tanda_tangan' ? 'Tanda Tangan' : ucfirst($t)), $tipeList);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $present->acara }}</td>
                                <td>{{ \Carbon\Carbon::parse($present->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $present->lokasi ?? '-' }}</td>
                                <td>{{ implode(' & ', $labelList) }}</td>
                                <td>
                                    @if($present->metode_kehadiran === 'luring')
                                        <span class="badge bg-primary">Luring</span>
                                    @else
                                        <span class="badge bg-info text-dark">Daring</span>
                                    @endif
                                </td>
                                <td>
                                    @if($present->status === 'open')
                                        <span class="badge bg-success">Terbuka</span>
                                    @else
                                        <span class="badge bg-danger">Tertutup</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('report.detail', ['token' => $present->token]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-info text-center">
            Tidak ada acara ditemukan.
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<style>
thead th a {
    cursor: pointer;
}
thead th a:hover {
    text-decoration: underline !important;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filter3 = document.getElementById('filter3');
        const tahunField = document.getElementById('tahun-field');
        const bulanField = document.getElementById('bulan-field');
        const tanggalField = document.getElementById('tanggal-field');
        const endDateField = document.getElementById('end-date-field');

        function toggleFields() {
            tahunField.style.display = 'none';
            bulanField.style.display = 'none';
            tanggalField.style.display = 'none';
            endDateField.style.display = 'none';

            if (filter3.value === 'tahun') {
                tahunField.style.display = 'block';
            } else if (filter3.value === 'bulan') {
                bulanField.style.display = 'block';
            } else if (filter3.value === 'tanggal') {
                tanggalField.style.display = 'block';
                endDateField.style.display = 'block';
            }
        }

        filter3.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>

@endsection