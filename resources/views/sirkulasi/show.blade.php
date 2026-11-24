@extends('layouts.app')
@section('title', 'Log Sirkulasi #' . $peminjaman->id_pinjam . ' - e-Arsip KSP')
@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('sirkulasi.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Daftar Sirkulasi</a>
</div>

<!-- Header Ringkasan -->
<div class="card" style="border-left: 6px solid #1f3864; margin-bottom: 16px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                <span class="badge" style="background: #1f3864; color: #fff; font-size: 13px;">
                    LOG SIRKULASI #{{ $peminjaman->id_pinjam }}
                </span>
                @if ($peminjaman->status_approval === 'menunggu')
                    <span class="badge badge-menunggu">STATUS APPROVAL: MENUNGGU PERSETUJUAN</span>
                @elseif ($peminjaman->status_approval === 'disetujui')
                    <span class="badge badge-disetujui">STATUS APPROVAL: DISETUJUI PIMPINAN</span>
                @else
                    <span class="badge badge-ditolak">STATUS APPROVAL: DITOLAK PIMPINAN</span>
                @endif

                @if ($peminjaman->isKembali())
                    <span class="badge badge-kembali">SIRKULASI: TELAH DIKEMBALIKAN</span>
                @elseif ($peminjaman->isTerlambat())
                    <span class="badge badge-terlambat">⚠️ TERLAMBAT {{ $peminjaman->hari_terlambat }} HARI</span>
                @elseif ($peminjaman->isDisetujui())
                    <span class="badge badge-dipinjam">SIRKULASI: SEDANG DIPINJAM</span>
                @endif
            </div>
            <h2 style="margin: 0; color: #1e293b;">
                Peminjaman Berkas: {{ $peminjaman->arsip?->judul_arsip ?? $peminjaman->id_arsip }}
            </h2>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('arsip.show', $peminjaman->id_arsip) }}" class="btn">
                📋 Buka Kartu Arsip
            </a>
            @if (auth()->user()->role === 'petugas' && $peminjaman->status_approval === 'disetujui' && !$peminjaman->isKembali())
                <button type="button" 
                        onclick="document.getElementById('modalKembali').style.display = 'flex'"
                        class="btn btn-success">
                    📥 Proses Pengembalian
                </button>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Kolom Kiri: Informasi Peminjam & Dokumen -->
    <div>
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                👤 Data Peminjam & Keperluan
            </h3>
            <div class="info-row">
                <span class="info-label">Nama Lengkap Peminjam</span>
                <span class="info-val">{{ $peminjaman->nama_peminjam }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Divisi / Unit Kerja</span>
                <span class="info-val">{{ $peminjaman->divisi }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Mulai Pinjam</span>
                <span class="info-val">{{ $peminjaman->tanggal_pinjam?->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Rencana Pengembalian</span>
                <span class="info-val">{{ $peminjaman->tanggal_kembali_rencana?->format('d F Y') }}</span>
            </div>
            <div style="margin-top: 12px;">
                <span class="info-label" style="display: block; margin-bottom: 4px;">Alasan / Keperluan Peminjaman:</span>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 4px; font-size: 13px; line-height: 1.5;">
                    {{ $peminjaman->keperluan }}
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📦 Data Berkas Jaminan / Arsip Fisik
            </h3>
            <div class="info-row">
                <span class="info-label">Kode Arsip</span>
                <span class="info-val" style="font-family: monospace;">{{ $peminjaman->id_arsip }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Klasifikasi Berkas</span>
                <span class="info-val">
                    {{ $peminjaman->arsip?->kode_klasifikasi }} — {{ $peminjaman->arsip?->klasifikasi?->nama_klasifikasi }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Kategori Berkas</span>
                <span class="info-val">
                    @if ($peminjaman->arsip?->isBerkasJaminan())
                        <span class="badge badge-vital">BERKAS VITAL AGUNAN</span>
                    @else
                        <span class="badge badge-non-vital">BERKAS NON-VITAL</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Posisi Rak Penyimpanan</span>
                <span class="info-val">
                    {{ $peminjaman->arsip?->lokasi?->gedung ?? '-' }} | Rak {{ $peminjaman->arsip?->lokasi?->nomor_rak ?? '-' }} | Ambalan {{ $peminjaman->arsip?->lokasi?->ambalan ?? '-' }} | Boks {{ $peminjaman->arsip?->lokasi?->nomor_boks ?? '-' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Ketersediaan Fisik Sekarang</span>
                <span class="info-val">
                    @if ($peminjaman->arsip?->status_ketersediaan === 'tersedia')
                        <span class="badge badge-tersedia">Tersedia di Rak</span>
                    @else
                        <span class="badge badge-dipinjam">Fisik Sedang Keluar</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Log Otorisasi Approval & Pengembalian -->
    <div>
        <!-- Panel Approval Pimpinan -->
        <div class="card" style="border-top: 4px solid {{ $peminjaman->status_approval === 'disetujui' ? '#16a34a' : ($peminjaman->status_approval === 'ditolak' ? '#dc2626' : '#f59e0b') }};">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                🛡️ Otorisasi & Log Approval Pimpinan
            </h3>
            <div class="info-row">
                <span class="info-label">Status Keputusan</span>
                <span class="info-val">
                    @if ($peminjaman->status_approval === 'menunggu')
                        <span class="badge badge-menunggu">MENUNGGU KEPUTUSAN</span>
                    @elseif ($peminjaman->status_approval === 'disetujui')
                        <span class="badge badge-disetujui">DISETUJUI</span>
                    @else
                        <span class="badge badge-ditolak">DITOLAK</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Pejabat Penyetuju (Pimpinan)</span>
                <span class="info-val">{{ $peminjaman->approver?->name ?? 'Belum diproses' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Waktu Keputusan Approval</span>
                <span class="info-val">
                    {{ $peminjaman->tanggal_approval ? $peminjaman->tanggal_approval->format('d F Y, H:i') . ' WIB' : '-' }}
                </span>
            </div>
            <div style="margin-top: 12px;">
                <span class="info-label" style="display: block; margin-bottom: 4px;">Catatan / Instruksi Pimpinan:</span>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 4px; font-size: 13px; font-style: italic; color: #334155;">
                    {{ $peminjaman->catatan_approval ?: 'Tidak ada catatan khusus.' }}
                </div>
            </div>

            @if (auth()->user()->role === 'pimpinan' && $peminjaman->isMenunggu())
                <div style="margin-top: 16px; padding: 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;">
                    <div style="font-weight: bold; margin-bottom: 8px; color: #92400e;">Tindakan Pimpinan:</div>
                    <div style="display: flex; gap: 8px;">
                        <form method="POST" action="{{ route('approval.setujui', $peminjaman->id_pinjam) }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">✓ Setujui Peminjaman</button>
                        </form>
                        <form method="POST" action="{{ route('approval.tolak', $peminjaman->id_pinjam) }}" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="catatan_approval" value="Ditolak oleh pimpinan setelah peninjauan berkas.">
                            <button type="submit" class="btn btn-danger btn-sm">✕ Tolak Permohonan</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Rekonsiliasi Pengembalian Fisik -->
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📥 Rekonsiliasi Pengembalian Fisik Berkas
            </h3>
            <div class="info-row">
                <span class="info-label">Status Fisik Sirkulasi</span>
                <span class="info-val">
                    @if ($peminjaman->isKembali())
                        <span class="badge badge-kembali">Lengkap & Kembali ke Rak</span>
                    @elseif ($peminjaman->isTerlambat())
                        <span class="badge badge-terlambat">TERLAMBAT ({{ $peminjaman->hari_terlambat }} HARI)</span>
                    @else
                        <span class="badge badge-dipinjam">Belum Dikembalikan</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Kembali Aktual</span>
                <span class="info-val">{{ $peminjaman->tanggal_kembali_aktual ? $peminjaman->tanggal_kembali_aktual->format('d F Y') : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Petugas Penerima Pengembalian</span>
                <span class="info-val">{{ $peminjaman->petugasKembali?->name ?? '-' }}</span>
            </div>
            <div style="margin-top: 12px;">
                <span class="info-label" style="display: block; margin-bottom: 4px;">Catatan Pemeriksaan Fisik:</span>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 4px; font-size: 13px;">
                    {{ $peminjaman->catatan_pengembalian ?: 'Berkas belum diserahterimakan kembali.' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Pengembalian Berkas Fisik -->
@if (auth()->user()->role === 'petugas' && $peminjaman->status_approval === 'disetujui' && !$peminjaman->isKembali())
<div id="modalKembali" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; color: #1f3864; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
            📥 Proses Pengembalian Berkas Fisik
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
            Konfirmasi penerimaan fisik berkas jaminan kembali ke depo/brankas arsip. Ketersediaan fisik berkas akan dipulihkan menjadi <strong>TERSEDIA</strong>.
        </p>

        <form method="POST" action="{{ route('sirkulasi.kembali', $peminjaman->id_pinjam) }}">
            @csrf
            <label style="font-size: 12px; font-weight: 600; color: #475569;">Tanggal Penerimaan Pengembalian:</label>
            <input type="date" name="tanggal_kembali_aktual" value="{{ date('Y-m-d') }}" required>

            <label style="font-size: 12px; font-weight: 600; color: #475569;">Catatan Kondisi Fisik Berkas:</label>
            <textarea name="catatan_pengembalian" rows="3" style="width: 100%; padding: 8px; box-sizing: border-box; margin: 4px 0 12px; border: 1px solid #cbd5e1; border-radius: 4px;" required placeholder="Contoh: Berkas jaminan diterima lengkap, segel utuh, dan langsung dikembalikan ke rak brankas.">Berkas fisik diterima dalam kondisi utuh, lengkap, dan disimpan kembali ke rak penyimpanan.</textarea>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="document.getElementById('modalKembali').style.display = 'none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success">Konfirmasi Pengembalian Berkas</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

