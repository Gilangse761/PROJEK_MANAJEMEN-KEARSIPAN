@extends('layouts.app')
@section('title', 'Detail Berita Acara ' . $ba->no_berita_acara . ' - e-Arsip KSP')
@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('penyusutan.index', ['tab' => 'berita-acara']) }}" class="btn btn-secondary btn-sm">← Kembali ke Daftar Berita Acara</a>
</div>

<!-- Header Berita Acara -->
<div class="card" style="border-left: 6px solid #b91c1c; margin-bottom: 20px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                <span class="badge" style="background: #1f3864; color: #fff; font-size: 13px;">
                    BERITA ACARA LEGAL: {{ $ba->no_berita_acara }}
                </span>
                @if ($ba->status_approval === 'menunggu')
                    <span class="badge badge-menunggu">STATUS: MENUNGGU PERSETUJUAN PIMPINAN</span>
                @elseif ($ba->status_approval === 'disetujui')
                    <span class="badge badge-disetujui">STATUS: DISETUJUI PIMPINAN</span>
                @else
                    <span class="badge badge-ditolak">STATUS: DITOLAK PIMPINAN</span>
                @endif

                @if ($ba->arsip?->status_arsip === 'musnah')
                    <span class="badge badge-musnah">🔥 TELAH MUSNAH</span>
                @else
                    <span class="badge badge-inaktif">STATUS FISIK: BELUM DIEKSEKUSI</span>
                @endif
            </div>
            <h2 style="margin: 0; color: #1e293b;">
                Pemusnahan Berkas: {{ $ba->arsip?->judul_arsip ?? $ba->id_arsip }}
            </h2>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('penyusutan.cetak', $ba->no_berita_acara) }}" target="_blank" class="btn" style="background: #0284c7; color: #fff;">
                🖨️ Cetak Berita Acara PDF
            </a>
            @if (auth()->user()->role === 'petugas' && $ba->status_approval === 'disetujui' && $ba->arsip?->status_arsip !== 'musnah')
                <form method="POST" action="{{ route('penyusutan.eksekusi', $ba->no_berita_acara) }}" style="margin: 0;" onsubmit="return confirm('PERINGATAN! Anda akan mengeksekusi pemusnahan fisik berkas dan mengubah status dokumen menjadi MUSNAH. Lanjutkan?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        🔥 Eksekusi Pemusnahan Fisik Sekarang
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Kolom Kiri: Rincian Berkas yang Dimusnahkan -->
    <div>
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📄 Rincian Berkas yang Disusutkan / Dimusnahkan
            </h3>
            <div class="info-row">
                <span class="info-label">Nomor Registrasi Arsip</span>
                <span class="info-val" style="font-family: monospace;">
                    <a href="{{ route('arsip.show', $ba->id_arsip) }}" style="color: #1f3864;">
                        {{ $ba->id_arsip }}
                    </a>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Judul / Uraian Dokumen</span>
                <span class="info-val">{{ $ba->arsip?->judul_arsip }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Klasifikasi Kearsipan</span>
                <span class="info-val">
                    {{ $ba->arsip?->kode_klasifikasi }} — {{ $ba->arsip?->klasifikasi?->nama_klasifikasi }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Penciptaan Berkas</span>
                <span class="info-val">{{ $ba->arsip?->tanggal_arsip }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jadwal Retensi Penyusutan</span>
                <span class="info-val" style="color: #b91c1c;">{{ $ba->arsip?->jadwal_penyusutan ?: '-' }}</span>
            </div>
            @if ($ba->arsip?->pinjaman)
                <div class="info-row">
                    <span class="info-label">Referensi Pinjaman</span>
                    <span class="info-val">
                        {{ $ba->arsip->pinjaman->no_rekening_pinjaman }} (Status: <strong style="color: #166534;">LUNAS</strong>)
                    </span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Status Fisik Sekarang</span>
                <span class="info-val">
                    @if ($ba->arsip?->status_arsip === 'musnah')
                        <span class="badge badge-musnah">Musnah Secara Fisik & Digital</span>
                    @else
                        <span class="badge badge-inaktif">Tersimpan di Rak Depo</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                👥 Panitia Eksekutor & Saksi-Saksi
            </h3>
            <div class="info-row">
                <span class="info-label">Petugas / Ketua Panitia</span>
                <span class="info-val">{{ $ba->petugas_eksekutor }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Saksi 1 (Legal & Kepatuhan)</span>
                <span class="info-val">{{ $ba->saksi_1 ?: '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Saksi 2 (Satuan Pengawas Internal)</span>
                <span class="info-val">{{ $ba->saksi_2 ?: '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lokasi Pelaksanaan Eksekusi</span>
                <span class="info-val">{{ $ba->lokasi_eksekusi }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pelaksanaan Eksekusi</span>
                <span class="info-val">{{ $ba->tanggal_eksekusi ? $ba->tanggal_eksekusi->format('d F Y') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Detail Legalitas & Approval Pimpinan -->
    <div>
        <div class="card" style="border-top: 4px solid {{ $ba->status_approval === 'disetujui' ? '#16a34a' : ($ba->status_approval === 'ditolak' ? '#dc2626' : '#f59e0b') }};">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                🛡️ Otorisasi & Approval Pemusnahan Pimpinan
            </h3>
            <div class="info-row">
                <span class="info-label">Status Keputusan Pimpinan</span>
                <span class="info-val">
                    @if ($ba->status_approval === 'menunggu')
                        <span class="badge badge-menunggu">MENUNGGU KEPUTUSAN</span>
                    @elseif ($ba->status_approval === 'disetujui')
                        <span class="badge badge-disetujui">DISETUJUI</span>
                    @else
                        <span class="badge badge-ditolak">DITOLAK</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Pejabat Penyetuju (Pimpinan)</span>
                <span class="info-val">{{ $ba->approver?->name ?? 'Belum diproses' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Waktu Keputusan Approval</span>
                <span class="info-val">
                    {{ $ba->tanggal_approval ? $ba->tanggal_approval->format('d F Y, H:i') . ' WIB' : '-' }}
                </span>
            </div>
            <div style="margin-top: 12px;">
                <span class="info-label" style="display: block; margin-bottom: 4px;">Catatan / Instruksi Pimpinan:</span>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 4px; font-size: 13px; font-style: italic;">
                    {{ $ba->catatan_approval ?: 'Tidak ada catatan khusus.' }}
                </div>
            </div>

            @if (auth()->user()->role === 'pimpinan' && $ba->isMenunggu())
                <div style="margin-top: 16px; padding: 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;">
                    <div style="font-weight: bold; margin-bottom: 8px; color: #92400e;">⚡ Keputusan Otorisasi Pimpinan:</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <form method="POST" action="{{ route('penyusutan.setujui', $ba->no_berita_acara) }}" style="flex: 2; margin: 0;">
                            @csrf
                            <input type="text" name="catatan_approval" placeholder="Catatan persetujuan (opsional)" style="margin-bottom: 6px; font-size: 13px;">
                            <button type="submit" class="btn btn-success" style="width: 100%;">
                                ✓ Setujui Usulan Pemusnahan
                            </button>
                        </form>
                        <form method="POST" action="{{ route('penyusutan.tolak', $ba->no_berita_acara) }}" style="flex: 1; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK usulan pemusnahan ini?');">
                            @csrf
                            <input type="text" name="catatan_approval" placeholder="Alasan penolakan (wajib)" required style="margin-bottom: 6px; font-size: 13px;">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                ✕ Tolak Usulan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                ⚖️ Metode & Dasar Pertimbangan Hukum
            </h3>
            <div class="info-row">
                <span class="info-label">Metode Pemusnahan Fisik</span>
                <span class="info-val">{{ $ba->metode_pemusnahan }}</span>
            </div>
            <div style="margin-top: 12px;">
                <span class="info-label" style="display: block; margin-bottom: 4px;">Dasar Pertimbangan Konsiderans:</span>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 4px; font-size: 13px; line-height: 1.5;">
                    {{ $ba->dasar_pertimbangan }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

