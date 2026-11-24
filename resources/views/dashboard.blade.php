@extends('layouts.app')
@section('title', 'Dashboard - e-Arsip KSP')
@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <h2 style="margin: 0 0 6px 0; color: #1f3864;">Selamat Datang, {{ auth()->user()->name }}</h2>
            <p style="margin: 0; color: #64748b;">
                Sistem Informasi Manajemen e-Arsip Koperasi Simpan Pinjam — Hak Akses: 
                <span class="badge" style="background: #1f3864; color: #fff;">{{ strtoupper(auth()->user()->role) }}</span>
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('sirkulasi.create') }}" class="btn btn-success">+ Ajukan Pinjam Berkas</a>
                <a href="{{ route('arsip.create') }}" class="btn">+ Registrasi Arsip</a>
            @elseif (auth()->user()->role === 'pimpinan')
                <a href="{{ route('approval.index') }}" class="btn" style="background: #f59e0b; color: #000; font-weight: bold;">
                    ⚡ Proses Approval ({{ $totalMenungguApproval }})
                </a>
            @endif
        </div>
    </div>
</div>

@if ($totalTerlambat > 0)
    <div class="card" style="border-left: 6px solid #dc2626; background: #fff5f5; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="margin: 0 0 4px 0; color: #991b1b;">⚠️ PERINGATAN KETERLAMBATAN PENGEMBALIAN BERKAS FISIK!</h4>
                <p style="margin: 0; color: #7f1d1d; font-size: 14px;">
                    Terdapat <strong>{{ $totalTerlambat }} berkas fisik</strong> yang telah melewati batas tanggal rencana pengembalian dan belum dikembalikan ke depo/brankas.
                </p>
            </div>
            <a href="{{ route('sirkulasi.index', ['status' => 'terlambat']) }}" class="btn btn-danger btn-sm">
                Lihat Berkas Terlambat →
            </a>
        </div>
    </div>
@endif

<!-- Matriks Statistik Utama -->
<div class="grid-3" style="margin-bottom: 20px;">
    <div class="card" style="border-top: 4px solid #1f3864;">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Total Arsip Terdaftar</span>
        <div style="font-size: 28px; font-weight: bold; color: #1f3864; margin: 8px 0;">{{ $totalArsip }}</div>
        <a href="{{ route('arsip.index') }}" style="font-size: 13px; color: #2563eb; text-decoration: none;">Telusuri katalog arsip →</a>
    </div>

    <div class="card" style="border-top: 4px solid #f59e0b;">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Berkas Sedang Dipinjam</span>
        <div style="font-size: 28px; font-weight: bold; color: #d97706; margin: 8px 0;">{{ $totalDipinjam }}</div>
        <a href="{{ route('sirkulasi.index', ['status' => 'dipinjam']) }}" style="font-size: 13px; color: #2563eb; text-decoration: none;">Monitoring sirkulasi aktif →</a>
    </div>

    <div class="card" style="border-top: 4px solid {{ $totalTerlambat > 0 ? '#dc2626' : '#16a34a' }};">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Peminjaman Terlambat (Overdue)</span>
        <div style="font-size: 28px; font-weight: bold; color: {{ $totalTerlambat > 0 ? '#dc2626' : '#16a34a' }}; margin: 8px 0;">
            {{ $totalTerlambat }}
        </div>
        <a href="{{ route('sirkulasi.index', ['status' => 'terlambat']) }}" style="font-size: 13px; color: #2563eb; text-decoration: none;">Pantau keterlambatan →</a>
    </div>
</div>

<div class="grid-2">
    <!-- Ringkasan Sirkulasi & Approval -->
    <div class="card">
        <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
            🔄 Ringkasan Sirkulasi & Approval Peminjaman
        </h3>
        <div class="info-row">
            <span class="info-label">Menunggu Persetujuan Pimpinan</span>
            <span class="info-val">
                @if ($totalMenungguApproval > 0)
                    <span class="badge badge-menunggu">{{ $totalMenungguApproval }} Permohonan</span>
                @else
                    <span style="color: #64748b;">0 permohonan</span>
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Fisik Berkas Keluar (Dipinjam)</span>
            <span class="info-val">
                <span class="badge badge-dipinjam">{{ $totalDipinjam }} berkas</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Keterlambatan Fisik Berkas</span>
            <span class="info-val">
                @if ($totalTerlambat > 0)
                    <span class="badge badge-terlambat">{{ $totalTerlambat }} berkas jatuh tempo</span>
                @else
                    <span class="badge badge-tersedia">Nihil / Tertib</span>
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Daftar Usul Musnah (DUM JRA)</span>
            <span class="info-val">
                <a href="{{ route('penyusutan.index') }}" style="color: #b91c1c; font-weight: bold; text-decoration: none;">
                    {{ $totalUsulMusnah }} Berkas Siap Susut
                </a>
            </span>
        </div>
        <div style="margin-top: 16px; display: flex; gap: 6px; flex-wrap: wrap;">
            <a href="{{ route('sirkulasi.index') }}" class="btn btn-secondary btn-sm" style="display: inline-block;">
                Modul Sirkulasi
            </a>
            <a href="{{ route('penyusutan.index') }}" class="btn btn-sm" style="background: #b91c1c; color: #fff; display: inline-block;">
                Penyusutan & BA
            </a>
            @if (auth()->user()->role === 'pimpinan')
                <a href="{{ route('approval.index') }}" class="btn btn-sm" style="background: #f59e0b; color: #000; font-weight: bold; display: inline-block;">
                    Panel Approval
                </a>
            @endif
        </div>
    </div>

    <!-- Data Perkoperasian -->
    <div class="card">
        <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
            👥 Data Anggota & Kredit Koperasi
        </h3>
        <div class="info-row">
            <span class="info-label">Total anggota: {{ $totalAnggota }}</span>
            <span class="info-val">{{ $totalAnggota }} Anggota</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total arsip: {{ $totalArsip }}</span>
            <span class="info-val">{{ $totalArsip }} Berkas</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pinjaman macet: {{ $totalMacet }}</span>
            <span class="info-val">
                <span class="badge badge-macet">{{ $totalMacet }} Macet</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Perlakuan Berkas Jaminan Macet</span>
            <span class="info-val" style="color: #64748b;">Retensi ditahan hingga lunas</span>
        </div>
        <div style="margin-top: 16px;">
            <a href="{{ route('arsip.index', ['status_arsip' => 'aktif']) }}" class="btn btn-secondary btn-sm">
                Lihat Berkas Agunan Aktif
            </a>
        </div>
    </div>
</div>
@endsection
