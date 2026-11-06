<?php

use App\Http\Controllers\ArsipDokumenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KlasifikasiArsipController;
use App\Http\Controllers\LokasiArsipController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard', [
            'totalAnggota' => DB::table('anggota')->count(),
            'totalArsip' => DB::table('arsip_dokumen')->count(),
            'totalMacet' => DB::table('transaksi_pinjaman')->where('status_pinjaman', 'macet')->count(),
        ]);
    })->name('dashboard');

    // Khusus Petugas (create, store, upload scan PDF)
    Route::middleware('role:petugas')->group(function () {
        Route::get('/arsip/create', [ArsipDokumenController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipDokumenController::class, 'store'])->name('arsip.store');
        Route::post('/arsip/upload/{arsip}', [ArsipDokumenController::class, 'uploadFile'])
            ->name('arsip.upload')
            ->where('arsip', '.*');

        Route::resource('klasifikasi', KlasifikasiArsipController::class)
            ->except(['index', 'show'])->parameters(['klasifikasi' => 'klasifikasi']);
        Route::resource('lokasi', LokasiArsipController::class)
            ->except(['index', 'show'])->parameters(['lokasi' => 'lokasi']);
    });

    // Dilihat oleh Petugas dan Pimpinan (read-only, search, kartu arsip, preview & unduh scan PDF)
    Route::middleware('role:petugas,pimpinan')->group(function () {
        Route::get('/arsip', [ArsipDokumenController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/preview/{arsip}', [ArsipDokumenController::class, 'preview'])
            ->name('arsip.preview')
            ->where('arsip', '.*');
        Route::get('/arsip/download/{arsip}', [ArsipDokumenController::class, 'download'])
            ->name('arsip.download')
            ->where('arsip', '.*');
        Route::get('/arsip/{arsip}', [ArsipDokumenController::class, 'show'])
            ->name('arsip.show')
            ->where('arsip', '.*');

        Route::resource('klasifikasi', KlasifikasiArsipController::class)
            ->only(['index'])->parameters(['klasifikasi' => 'klasifikasi']);
        Route::resource('lokasi', LokasiArsipController::class)
            ->only(['index'])->parameters(['lokasi' => 'lokasi']);
    });

    Route::view('/approval', 'halaman', ['judul' => 'Approval Pimpinan (placeholder Sprint 4)'])
        ->middleware('role:pimpinan');
});
