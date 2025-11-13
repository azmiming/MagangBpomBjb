@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Dashboard</h2>
        <span class="text-muted fs-6">
            <i class="fas fa-calendar-day me-1"></i>
         {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white px-3 py-2 shadow-sm rounded">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>

    <!-- Quick Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4 bg-primary text-white rounded">
                    <i class="fas fa-list fa-2x mb-3"></i>
                    <h6 class="mb-2 fw-semibold">Acara Aktif</h6>
                    <h4 class="mb-0 fw-bold">{{ $activeEvents->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4 bg-info text-white rounded">
                    <i class="fas fa-calendar-check fa-2x mb-3"></i>
                    <h6 class="mb-2 fw-semibold">Acara Selesai</h6>
                    <h4 class="mb-0 fw-bold">{{ $completedEvents ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Section -->
    <div class="row g-4">
        <!-- Active Attendance Events -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-list me-2"></i>Acara Absensi Aktif
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if ($activeEvents->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            <p class="mb-0">Tidak ada acara absensi yang sedang dibuka hari ini.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($activeEvents as $event)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-medium">{{ $event->acara ?? 'Tanpa Judul' }}</h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
                                        </small>
                                        @if ($event->status === 'open')
                                            <span class="badge bg-success mt-1">🟢 Dibuka</span>
                                        @else
                                            <span class="badge bg-danger mt-1">🔴 Ditutup</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('present.show', $event->token ?? '#') }}" 
                                       class="btn btn-sm {{ $event->status === 'open' ? 'btn-outline-primary' : 'btn-outline-secondary disabled' }} rounded-pill ms-2"
                                       aria-label="Lihat acara"
                                       {{ $event->status !== 'open' ? 'tabindex="-1"' : '' }}>
                                        <i class="{{ $event->status === 'open' ? 'fas fa-arrow-right' : 'fas fa-eye-slash' }}"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold text-warning">
                        <i class="fas fa-calendar-alt me-2"></i>Acara Akan Datang
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if ($upcomingEvents->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x d-block mb-2"></i>
                            <p class="mb-0">Tidak ada acara mendatang.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($upcomingEvents as $event)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-medium">{{ $event->acara ?? 'Tanpa Judul' }}</h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
                                        </small>
                                        @if ($event->status === 'open')
                                            <span class="badge bg-success mt-1">🟢 Sudah Dibuka</span>
                                        @else
                                            <span class="badge bg-warning text-dark mt-1">🟡 Belum Dibuka</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('present.show', $event->token ?? '#') }}" 
                                       class="btn btn-sm {{ $event->status === 'open' ? 'btn-outline-primary' : 'btn-outline-secondary disabled' }} rounded-pill ms-2"
                                       aria-label="Lihat detail acara"
                                       {{ $event->status !== 'open' ? 'tabindex="-1"' : '' }}>
                                        <i class="{{ $event->status === 'open' ? 'fas fa-eye' : 'fas fa-eye-slash' }}"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection