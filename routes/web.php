<?php

use App\Http\Controllers\ApprovalPeminjamanController;
use App\Http\Controllers\ArsipDokumenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KlasifikasiArsipController;
use App\Http\Controllers\LokasiArsipController;
use App\Http\Controllers\PenyusutanArsipController;
use App\Http\Controllers\SirkulasiArsipController;
use App\Models\ArsipDokumen;
use App\Models\PeminjamanArsip;
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
        // Deteksi peminjaman terlambat
        PeminjamanArsip::where('status_approval', 'disetujui')
            ->where('status_sirkulasi', 'dipinjam')
            ->whereDate('tanggal_kembali_rencana', '<', now()->toDateString())
            ->update(['status_sirkulasi' => 'terlambat']);

        $arsipList = ArsipDokumen::with(['klasifikasi', 'pinjaman'])->get();
        $totalUsulMusnah = $arsipList->filter(fn ($a) => $a->isLayakMusnah())->count();
        $totalTelahMusnah = $arsipList->where('status_arsip', 'musnah')->count();

        return view('dashboard', [
            'totalAnggota' => DB::table('anggota')->count(),
            'totalArsip' => DB::table('arsip_dokumen')->count(),
            'totalMacet' => DB::table('transaksi_pinjaman')->where('status_pinjaman', 'macet')->count(),
            'totalDipinjam' => PeminjamanArsip::where('status_approval', 'disetujui')->where('status_sirkulasi', 'dipinjam')->count(),
            'totalTerlambat' => PeminjamanArsip::where('status_approval', 'disetujui')->where('status_sirkulasi', 'terlambat')->count(),
            'totalMenungguApproval' => PeminjamanArsip::where('status_approval', 'menunggu')->count(),
            'totalUsulMusnah' => $totalUsulMusnah,
            'totalTelahMusnah' => $totalTelahMusnah,
        ]);
    })->name('dashboard');

    // Khusus Petugas (create, store, upload scan PDF, sirkulasi, penyusutan)
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

        // Sirkulasi Peminjaman & Pengembalian Berkas Fisik
        Route::get('/sirkulasi/create', [SirkulasiArsipController::class, 'create'])->name('sirkulasi.create');
        Route::post('/sirkulasi', [SirkulasiArsipController::class, 'store'])->name('sirkulasi.store');
        Route::post('/sirkulasi/{peminjaman}/kembali', [SirkulasiArsipController::class, 'kembali'])->name('sirkulasi.kembali');

        // Modul Penyusutan: Eksekusi Pemusnahan Fisik (Khusus Petugas Arsip)
        Route::post('/penyusutan/{noBa}/eksekusi', [PenyusutanArsipController::class, 'eksekusi'])
            ->name('penyusutan.eksekusi')
            ->where('noBa', '.*');
    });

    // Dilihat oleh Petugas dan Pimpinan (read-only, search, kartu arsip, sirkulasi, penyusutan & cetak BA)
    Route::middleware('role:petugas,pimpinan')->group(function () {
        // Modul Penyusutan: Form Pengajuan & Pendaftaran Berita Acara Usul Musnah
        Route::get('/penyusutan/create', [PenyusutanArsipController::class, 'create'])->name('penyusutan.create');
        Route::post('/penyusutan', [PenyusutanArsipController::class, 'store'])->name('penyusutan.store');
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

        // Modul Sirkulasi (Daftar & Detail)
        Route::get('/sirkulasi', [SirkulasiArsipController::class, 'index'])->name('sirkulasi.index');
        Route::get('/sirkulasi/{peminjaman}', [SirkulasiArsipController::class, 'show'])->name('sirkulasi.show');

        // Modul Penyusutan (Daftar Usul Musnah, Legal Hold, & Berita Acara Legal)
        Route::get('/penyusutan', [PenyusutanArsipController::class, 'index'])->name('penyusutan.index');
        Route::get('/penyusutan/{noBa}/cetak', [PenyusutanArsipController::class, 'cetak'])
            ->name('penyusutan.cetak')
            ->where('noBa', '.*');
        Route::get('/penyusutan/{noBa}', [PenyusutanArsipController::class, 'show'])
            ->name('penyusutan.show')
            ->where('noBa', '.*');
    });

    // Khusus Pimpinan (Approval Berjenjang Peminjaman & Pemusnahan Berkas)
    Route::middleware('role:pimpinan')->group(function () {
        Route::get('/approval', [ApprovalPeminjamanController::class, 'index'])->name('approval.index');
        Route::post('/approval/{peminjaman}/setujui', [ApprovalPeminjamanController::class, 'setujui'])->name('approval.setujui');
        Route::post('/approval/{peminjaman}/tolak', [ApprovalPeminjamanController::class, 'tolak'])->name('approval.tolak');

        // Approval Pemusnahan Berkas JRA
        Route::post('/penyusutan/{noBa}/setujui', [PenyusutanArsipController::class, 'setujui'])
            ->name('penyusutan.setujui')
            ->where('noBa', '.*');
        Route::post('/penyusutan/{noBa}/tolak', [PenyusutanArsipController::class, 'tolak'])
            ->name('penyusutan.tolak')
            ->where('noBa', '.*');
    });
});
