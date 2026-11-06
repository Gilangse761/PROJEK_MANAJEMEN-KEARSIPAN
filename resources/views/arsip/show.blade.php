@extends('layouts.app')
@section('title', 'Kartu Arsip: ' . $arsip->id_arsip)
@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('arsip.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Daftar Arsip</a>
</div>

<!-- Header Kartu Arsip -->
<div class="card" style="border-left: 6px solid #1f3864;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                <span class="badge" style="background:#1f3864; color:#fff; font-size:13px; padding: 4px 10px;">
                    {{ $arsip->id_arsip }}
                </span>
                @if ($arsip->klasifikasi?->jenis_berkas === 'vital')
                    <span class="badge badge-vital">BERKAS VITAL AGUNAN</span>
                @else
                    <span class="badge badge-non-vital">BERKAS NON-VITAL</span>
                @endif

                @if ($arsip->status_arsip === 'aktif')
                    <span class="badge badge-aktif">STATUS: AKTIF</span>
                @elseif ($arsip->status_arsip === 'inaktif')
                    <span class="badge badge-inaktif">STATUS: INAKTIF</span>
                @elseif ($arsip->status_arsip === 'permanen')
                    <span class="badge badge-permanen">STATUS: PERMANEN</span>
                @else
                    <span class="badge badge-musnah">STATUS: {{ strtoupper($arsip->status_arsip) }}</span>
                @endif
            </div>
            <h1 style="margin: 0; font-size: 22px; color: #1e293b;">{{ $arsip->judul_arsip }}</h1>
        </div>

        <div style="display: flex; gap: 8px;">
            @if ($hasDigitalFile)
                <a href="{{ route('arsip.preview', $arsip->id_arsip) }}" target="_blank" class="btn btn-success">
                    👁️ Buka PDF Penuh
                </a>
                <a href="{{ route('arsip.download', $arsip->id_arsip) }}" class="btn">
                    ⬇️ Unduh PDF
                </a>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Kolom Kiri: Metadata Dokumen & Referensi Pinjaman -->
    <div>
        <!-- Data Pokok Arsip -->
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📋 Data Identitas Berkas
            </h3>
            <div class="info-row">
                <span class="info-label">Kode Klasifikasi</span>
                <span class="info-val">
                    {{ $arsip->kode_klasifikasi }} — {{ $arsip->klasifikasi?->nama_klasifikasi }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Berkas</span>
                <span class="info-val">{{ $arsip->tanggal_arsip }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bentuk Media</span>
                <span class="info-val">{{ ucfirst($arsip->media_arsip) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status Scan Digital</span>
                <span class="info-val">
                    @if ($hasDigitalFile)
                        <span class="badge badge-pdf">Tersimpan Terenkripsi (AES-256)</span>
                    @else
                        <span style="color:#94a3b8; font-weight:normal;">Belum Ada Scan PDF</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Referensi Transaksi Pinjaman / Anggota -->
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                🤝 Data Referensi Nasabah & Transaksi
            </h3>
            @if ($arsip->referensi_nomor)
                <div class="info-row">
                    <span class="info-label">Nomor Rekening Pinjaman</span>
                    <span class="info-val" style="color: #1f3864; font-family: monospace; font-size: 15px;">
                        {{ $arsip->referensi_nomor }}
                    </span>
                </div>
                @if ($arsip->pinjaman)
                    <div class="info-row">
                        <span class="info-label">Nama Anggota Nasabah</span>
                        <span class="info-val">{{ $arsip->pinjaman->anggota?->nama_lengkap ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nomor Induk Kependudukan (NIK)</span>
                        <span class="info-val">{{ $arsip->pinjaman->anggota?->nik ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status Kredit Nasabah</span>
                        <span class="info-val">
                            @if ($arsip->pinjaman->status_pinjaman === 'macet')
                                <span class="badge badge-macet">KREDIT MACET (NON-PERFORMING)</span>
                            @elseif ($arsip->pinjaman->status_pinjaman === 'lunas')
                                <span class="badge badge-lunas">LUNAS</span>
                            @else
                                <span class="badge badge-berjalan">BERJALAN NORMAL</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Plafon / Nominal Pinjaman</span>
                        <span class="info-val">Rp {{ number_format($arsip->pinjaman->nominal_pinjaman, 0, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Akad & Jatuh Tempo</span>
                        <span class="info-val">{{ $arsip->pinjaman->tanggal_akad }} s/d {{ $arsip->pinjaman->tanggal_jatuh_tempo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pelunasan</span>
                        <span class="info-val">
                            {{ $arsip->pinjaman->tanggal_lunas ?? '(Belum Lunas)' }}
                        </span>
                    </div>
                @endif
            @else
                <p style="color: #64748b; font-size: 14px; margin: 0;">Berkas umum non-pinjaman, tidak terikat kontrak kredit nasabah.</p>
            @endif
        </div>

        <!-- Aturan Jadwal Retensi Arsip (JRA) -->
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #b45309;">
                ⏱️ Aturan & Jadwal Retensi Arsip (JRA)
            </h3>
            <div class="info-row">
                <span class="info-label">Masa Retensi Aktif</span>
                <span class="info-val">{{ $arsip->klasifikasi?->retensi_aktif }} Tahun</span>
            </div>
            <div class="info-row">
                <span class="info-label">Masa Retensi Inaktif</span>
                <span class="info-val">{{ $arsip->klasifikasi?->retensi_inaktif }} Tahun</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dasar Perhitungan</span>
                <span class="info-val">{{ $jraInfo['dasar_perhitungan'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jatuh Tempo Masa Inaktif</span>
                <span class="info-val" style="font-size: 15px; color: #1e293b;">
                    {{ $arsip->jadwal_retensi_inaktif ?? 'Belum Berjalan' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Jadwal Pemusnahan / Penyusutan</span>
                <span class="info-val" style="font-size: 15px; color: #1e293b;">
                    {{ $arsip->jadwal_penyusutan ?? 'Belum Berjalan' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tindakan Akhir Retensi</span>
                <span class="info-val" style="text-transform: uppercase; font-weight: bold;">
                    {{ $arsip->klasifikasi?->keterangan_akhir }}
                </span>
            </div>

            @if ($arsip->klasifikasi?->retensi_dihitung_dari === 'tanggal_lunas' && (!$arsip->pinjaman || $arsip->pinjaman->status_pinjaman !== 'lunas'))
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 10px; margin-top: 12px; font-size: 13px; color: #92400e;">
                    <strong>🛡️ Perlindungan Berkas Jaminan:</strong><br>
                    Pinjaman belum berstatus lunas. Sesuai prosedur kearsipan perbankan/koperasi, berkas sertifikat jaminan tetap aktif dan dilarang dimusnahkan selama kewajiban nasabah belum terselesaikan.
                </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Visualisasi Rak Fisik & In-Browser Preview PDF -->
    <div>
        <!-- Visualisasi Titik Rak Fisik Presisi (ARSIP-011) -->
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📍 Visualisasi Titik Rak Fisik Presisi
            </h3>

            <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">
                    Koordinat Penyimpanan Depo Fisik:
                </div>
                <div style="font-size: 16px; font-weight: bold; color: #1f3864;">
                    🏢 {{ $arsip->lokasi?->gedung }} &nbsp;›&nbsp;
                    🗄️ Rak {{ $arsip->lokasi?->nomor_rak }} &nbsp;›&nbsp;
                    📏 Ambalan {{ $arsip->lokasi?->ambalan ?? 'Utama' }} &nbsp;›&nbsp;
                    📦 Boks {{ $arsip->lokasi?->nomor_boks }}
                </div>
                <div style="font-size: 13px; color: #475569; margin-top: 4px;">
                    Kode Titik Lokasi: <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight: bold;">{{ $arsip->lokasi?->kode_lokasi }}</code>
                </div>
            </div>

            <!-- Denah Visual Rak Depo / Brankas Khazanah -->
            <div class="rack-frame">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #475569; padding-bottom: 8px;">
                    <span style="font-weight: bold; font-size: 13px; letter-spacing: 1px;">
                        SKEMA RAK FISIK: {{ strtoupper($arsip->lokasi?->gedung ?? 'DEPO') }} ({{ $arsip->lokasi?->nomor_rak }})
                    </span>
                    <span style="font-size: 11px; background: #1e293b; padding: 2px 8px; border-radius: 10px;">
                        Sistem Presisi 4-Tingkat
                    </span>
                </div>

                @php
                    $ambalanList = ['RF-A', 'RF-B', 'RF-C', 'RF-D'];
                    $currentAmbalan = $arsip->lokasi?->ambalan ?? 'RF-A';
                    $currentBoks = $arsip->lokasi?->nomor_boks;
                @endphp

                @foreach ($ambalanList as $amb)
                    <div class="rack-tier">
                        <div class="rack-tier-label">
                            {{ $amb }}
                            @if ($amb === $currentAmbalan)
                                <div style="font-size: 9px; color: #fbbf24;">(Posisi)</div>
                            @endif
                        </div>
                        <div class="rack-boxes">
                            @foreach (['OD-01', 'OD-02', 'OD-03', 'OD-04'] as $bx)
                                @php
                                    $isTarget = ($amb === $currentAmbalan && $bx === $currentBoks);
                                @endphp
                                <div class="rack-box {{ $isTarget ? 'rack-box-active' : '' }}">
                                    @if ($isTarget)
                                        <div style="font-size: 10px;">📍 DI SINI</div>
                                    @endif
                                    {{ $bx }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div style="font-size: 12px; color: #cbd5e1; text-align: center; margin-top: 8px;">
                    ⭐ Kotak kuning berkedip menandakan posisi fisik boks/ordner tempat sertifikat/berkas ini tersimpan.
                </div>
            </div>
        </div>

        <!-- In-Browser Preview Dokumen PDF (ARSIP-011) -->
        <div class="card">
            <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
                📄 Pratinjau Dokumen Digital (In-Browser Preview)
            </h3>

            @if ($hasDigitalFile)
                <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: #15803d; font-weight: bold;">
                        ✓ Pindaian Asli Terverifikasi (Terenkripsi AES-256)
                    </span>
                    <div>
                        <a href="{{ route('arsip.preview', $arsip->id_arsip) }}" target="_blank" class="btn btn-secondary btn-sm">
                            Buka di Tab Baru ↗
                        </a>
                    </div>
                </div>

                <!-- Iframe PDF Viewer -->
                <iframe
                    src="{{ route('arsip.preview', $arsip->id_arsip) }}"
                    width="100%"
                    height="500px"
                    style="border: 2px solid #e2e8f0; border-radius: 6px; background: #fff;"
                    title="Pratinjau Berkas PDF">
                </iframe>
            @else
                <div style="text-align: center; padding: 36px 16px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 6px; margin-bottom: 16px;">
                    <div style="font-size: 36px; margin-bottom: 8px;">📑</div>
                    <strong style="color: #475569;">Belum Ada Salinan Pindaian Digital (PDF)</strong>
                    <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">
                        Dokumen ini saat ini hanya tercatat sebagai arsip fisik pada rak depo koperasi.
                    </p>
                </div>
            @endif

            <!-- Form Upload / Perbarui Scan PDF untuk Petugas Arsip (ARSIP-009) -->
            @if (auth()->user()->role === 'petugas')
                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 14px; margin-top: 16px;">
                    <h4 style="margin: 0 0 6px 0; color: #166534;">
                        {{ $hasDigitalFile ? '🔄 Perbarui / Ganti Pindaian Scan PDF' : '📤 Unggah Scan PDF Sertifikat / Agunan' }}
                    </h4>
                    <form method="POST" action="{{ route('arsip.upload', $arsip->id_arsip) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file_digital" accept="application/pdf" required style="background: #fff; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <button type="submit" class="btn btn-success btn-sm">
                                🔒 Simpan & Enkripsi AES-256
                            </button>
                            <small style="color: #15803d;">Maks. 10 MB (Format PDF)</small>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
