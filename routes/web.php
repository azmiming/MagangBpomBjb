<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PresentController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;


// ✅ DASHBOARD

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


// ✅ PRESENSI (PRESENT)

Route::prefix('present')->group(function () {
    Route::get('/', [PresentController::class, 'index'])->name('present.index');
    Route::post('/generate', [PresentController::class, 'generate'])->name('present.generate');
    Route::get('/{token}', [PresentController::class, 'show'])->name('present.show');
    Route::post('/{token}', [PresentController::class, 'submit'])->name('present.submit');
    Route::post('/{token}/toggle-status', [PresentController::class, 'toggleStatus'])->name('present.toggle-status');
    Route::get('/{id}/edit', [PresentController::class, 'edit'])->name('present.edit');
    Route::put('/{id}', [PresentController::class, 'update'])->name('present.update');
});


// ✅ CARI PEGAWAI

Route::get('/pegawai/{nip}', [UserController::class, 'findByNip'])
    ->where('nip', '[0-9A-Za-z]+')
    ->name('pegawai.find');


// ✅ PROFIL

Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
Route::post('/profil', [ProfilController::class, 'search'])->name('profil.search');
Route::get('/profil/detail/{nip}', [ProfilController::class, 'detail'])
    ->name('profil.detail')
    ->where('nip', '[0-9]+');
Route::get('/profil/search-ajax', [ProfilController::class, 'searchAjax'])->name('profil.searchAjax');


// ✅ LAPORAN (REPORT)

Route::prefix('report')->group(function () {
    // Halaman utama laporan (filter 3)
    Route::get('/', [ReportController::class, 'index'])->name('report');
    Route::post('/filter', [ReportController::class, 'filter'])->name('report.filter');

    // Detail acara dan filter detail
    Route::get('/detail/{token}', [ReportController::class, 'detail'])->name('report.detail');
    Route::post('/detail/{token}/filter', [ReportController::class, 'detailFilter'])->name('report.detail.filter');

    // Print PDF (TAMBAH INI)
    Route::get('/detail/{token}/print-pdf', [ReportController::class, 'printPDF'])->name('report.print.pdf');

    // Download & Export
    Route::get('/download', [ReportController::class, 'downloadFile'])->name('report.download');
    Route::get('/detail/{token}/print-excel', [ReportController::class, 'printExcel'])->name('report.print.excel');
    Route::get('/detail/{token}/export', [ReportController::class, 'exportDetailExcel'])->name('report.detail.export');
});