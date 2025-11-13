<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| File ini digunakan untuk mendefinisikan semua route API aplikasi.
| Rute-rute ini otomatis dimuat oleh RouteServiceProvider dan akan
| berada di dalam grup middleware "api".
|
*/

// ✅ Route default bawaan Laravel untuk autentikasi API
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ Route untuk mencari data pegawai berdasarkan NIP atau No Pegawai
Route::get('/pegawai/{nip}', [UserController::class, 'findByNip'])
    ->where('nip', '[0-9A-Za-z]+') // validasi agar nip hanya huruf/angka
    ->name('api.pegawai.find');
