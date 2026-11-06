@extends('layouts.app')
@section('title', 'Temu Balik & Daftar Arsip')
@section('content')
<div class="card">
    <div class="bar">
        <div>
            <h2 style="margin: 0 0 4px 0;">Temu Balik Arsip (Multi-Kriteria)</h2>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Pencarian cepat berkas jaminan, dokumen simpanan, dan arsip vital koperasi.</p>
        </div>
        @if (auth()->user()->role === 'petugas')
            <a class="btn" href="{{ route('arsip.create') }}">+ Registrasi Arsip Baru</a>
        @endif
    </div>

    <!-- Kotak Pencarian Multi-Parameter (ARSIP-010) -->
    <form method="GET" action="{{ route('arsip.index') }}" class="filter-box">
        <div class="filter-grid">
            <div>
                <label for="q">Pencarian Umum (Keyword)</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Cari judul, kode, nama, rek...">
            </div>
            <div>
                <label for="id_arsip">Nomor / Kode Registrasi Arsip</label>
                <input type="text" id="id_arsip" name="id_arsip" value="{{ request('id_arsip') }}" placeholder="Contoh: ARC-KP.03...">
            </div>
            <div>
                <label for="nama_anggota">Nama Anggota Nasabah</label>
                <input type="text" id="nama_anggota" name="nama_anggota" value="{{ request('nama_anggota') }}" placeholder="Contoh: Budi Santoso">
            </div>
            <div>
                <label for="no_rekening">Nomor Kontrak / Rekening Pinjaman</label>
                <input type="text" id="no_rekening" name="no_rekening" value="{{ request('no_rekening') }}" placeholder="Contoh: KRD-2024-0001">
            </div>
            <div>
                <label for="kode_klasifikasi">Kategori / Klasifikasi</label>
                <select id="kode_klasifikasi" name="kode_klasifikasi">
                    <option value="">- Semua Kategori -</option>
                    @foreach ($klasifikasiList as $k)
                        <option value="{{ $k->kode_klasifikasi }}" @selected(request('kode_klasifikasi') === $k->kode_klasifikasi)>
                            {{ $k->kode_klasifikasi }} - {{ $k->nama_klasifikasi }} ({{ $k->jenis_berkas }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="jenis_berkas">Jenis Berkas</label>
                <select id="jenis_berkas" name="jenis_berkas">
                    <option value="">- Semua Jenis -</option>
                    <option value="vital" @selected(request('jenis_berkas') === 'vital')>Vital (Agunan / Hak Koperasi)</option>
                    <option value="non_vital" @selected(request('jenis_berkas') === 'non_vital')>Non-Vital</option>
                </select>
            </div>
            <div>
                <label for="status_arsip">Status Masa Arsip</label>
                <select id="status_arsip" name="status_arsip">
                    <option value="">- Semua Status -</option>
                    <option value="aktif" @selected(request('status_arsip') === 'aktif')>Aktif</option>
                    <option value="inaktif" @selected(request('status_arsip') === 'inaktif')>Inaktif</option>
                    <option value="permanen" @selected(request('status_arsip') === 'permanen')>Permanen</option>
                    <option value="musnah" @selected(request('status_arsip') === 'musnah')>Musnah</option>
                </select>
            </div>
            <div>
                <label for="id_lokasi">Titik Lokasi Depo / Brankas</label>
                <select id="id_lokasi" name="id_lokasi">
                    <option value="">- Semua Lokasi -</option>
                    @foreach ($lokasiList as $l)
                        <option value="{{ $l->id_lokasi }}" @selected((string) request('id_lokasi') === (string) $l->id_lokasi)>
                            {{ $l->kode_lokasi }} ({{ $l->gedung }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="margin-top: 12px; display: flex; gap: 8px; align-items: center;">
            <button type="submit" class="btn">🔍 Temukan Arsip</button>
            <a href="{{ route('arsip.index') }}" class="btn btn-secondary">Reset Pencarian</a>
            <span style="margin-left: auto; font-size: 13px; color: #475569;">
                Menampilkan <strong>{{ $totalHasil }}</strong> dari <strong>{{ $totalKeseluruhan }}</strong> arsip
            </span>
        </div>
    </form>

    <!-- Tabel Daftar Arsip -->
    <table>
        <thead>
            <tr>
                <th>Kode Registrasi</th>
                <th>Judul & Media</th>
                <th>Klasifikasi</th>
                <th>Titik Lokasi Rak</th>
                <th>Referensi Transaksi & Anggota</th>
                <th>Status JRA</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $a)
                <tr>
                    <td>
                        <a href="{{ route('arsip.show', $a->id_arsip) }}" style="color: #1f3864; font-weight: bold; text-decoration: none;">
                            {{ $a->id_arsip }}
                        </a>
                        <br>
                        <small style="color: #64748b;">Tgl: {{ $a->tanggal_arsip }}</small>
                    </td>
                    <td>
                        <strong>{{ $a->judul_arsip }}</strong>
                        <div style="margin-top: 4px;">
                            @if ($a->hasDigitalFile())
                                <a href="{{ route('arsip.preview', $a->id_arsip) }}" target="_blank" class="badge badge-pdf" style="text-decoration:none;">
                                    📄 Scan PDF Tersedia
                                </a>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#94a3b8;">Fisik Tanpa Scan</span>
                            @endif
                            <span class="badge" style="background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;">{{ $a->media_arsip }}</span>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $a->kode_klasifikasi }}</strong><br>
                        <small>{{ $a->klasifikasi?->nama_klasifikasi }}</small><br>
                        @if ($a->klasifikasi?->jenis_berkas === 'vital')
                            <span class="badge badge-vital">VITAL</span>
                        @else
                            <span class="badge badge-non-vital">NON-VITAL</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $a->lokasi?->kode_lokasi }}</strong><br>
                        <small>{{ $a->lokasi?->gedung }}</small><br>
                        <small style="color: #64748b;">Rak: {{ $a->lokasi?->nomor_rak }} | Boks: {{ $a->lokasi?->nomor_boks }}</small>
                    </td>
                    <td>
                        @if ($a->referensi_nomor)
                            <strong>{{ $a->referensi_nomor }}</strong>
                            @if ($a->pinjaman && $a->pinjaman->anggota)
                                <br>
                                <span>{{ $a->pinjaman->anggota->nama_lengkap }}</span>
                                <div style="margin-top: 2px;">
                                    @if ($a->pinjaman->status_pinjaman === 'macet')
                                        <span class="badge badge-macet">Kredit Macet</span>
                                    @elseif ($a->pinjaman->status_pinjaman === 'lunas')
                                        <span class="badge badge-lunas">Lunas</span>
                                    @else
                                        <span class="badge badge-berjalan">Berjalan</span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($a->status_arsip === 'aktif')
                            <span class="badge badge-aktif">AKTIF</span>
                        @elseif ($a->status_arsip === 'inaktif')
                            <span class="badge badge-inaktif">INAKTIF</span>
                        @elseif ($a->status_arsip === 'permanen')
                            <span class="badge badge-permanen">PERMANEN</span>
                        @else
                            <span class="badge badge-musnah">{{ strtoupper($a->status_arsip) }}</span>
                        @endif
                        <br>
                        <small style="color: #64748b;">
                            @if ($a->jadwal_retensi_inaktif)
                                Inaktif: {{ $a->jadwal_retensi_inaktif }}
                            @else
                                Inaktif: (belum berjalan)
                            @endif
                        </small>
                    </td>
                    <td>
                        <a href="{{ route('arsip.show', $a->id_arsip) }}" class="btn btn-secondary btn-sm" style="display:block; text-align:center; margin-bottom:4px;">
                            Kartu Arsip
                        </a>
                        @if ($a->hasDigitalFile())
                            <a href="{{ route('arsip.preview', $a->id_arsip) }}" target="_blank" class="btn btn-sm btn-success" style="display:block; text-align:center;">
                                Lihat PDF
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 32px; color: #64748b;">
                        Tidak ada arsip yang cocok dengan kriteria pencarian Anda.<br>
                        <a href="{{ route('arsip.index') }}" style="color: #1f3864; font-weight: bold; margin-top: 8px; display: inline-block;">Reset Kriteria Pencarian</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
