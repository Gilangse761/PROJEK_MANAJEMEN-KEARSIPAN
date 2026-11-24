@extends('layouts.app')
@section('title', 'Modul Sirkulasi & Peminjaman Berkas - e-Arsip KSP')
@section('content')
<div class="card" style="margin-bottom: 16px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <h2 style="margin: 0 0 6px 0; color: #1f3864;">📋 Modul Sirkulasi & Peminjaman Berkas Jaminan</h2>
            <p style="margin: 0; color: #64748b; font-size: 14px;">
                Pengendalian peredaran berkas jaminan fisik, otorisasi berjenjang oleh pimpinan, dan pemantauan keterlambatan pengembalian berkas.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('sirkulasi.create') }}" class="btn btn-success">+ Ajukan Permohonan Pinjam</a>
            @endif
            @if (auth()->user()->role === 'pimpinan')
                <a href="{{ route('approval.index') }}" class="btn" style="background: #f59e0b; color: #000; font-weight: bold;">
                    ⚡ Menu Approval ({{ $totalMenunggu }})
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Tab Filter Status Sirkulasi -->
<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
    <a href="{{ route('sirkulasi.index', array_merge($filters, ['status' => 'semua'])) }}"
       class="btn {{ $currentStatus === 'semua' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        Semua Data
    </a>
    <a href="{{ route('sirkulasi.index', array_merge($filters, ['status' => 'menunggu'])) }}"
       class="btn {{ $currentStatus === 'menunggu' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        ⏳ Menunggu Approval ({{ $totalMenunggu }})
    </a>
    <a href="{{ route('sirkulasi.index', array_merge($filters, ['status' => 'dipinjam'])) }}"
       class="btn {{ $currentStatus === 'dipinjam' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        📤 Sedang Dipinjam ({{ $totalDipinjam }})
    </a>
    <a href="{{ route('sirkulasi.index', array_merge($filters, ['status' => 'terlambat'])) }}"
       class="btn {{ $currentStatus === 'terlambat' ? 'btn-danger' : 'btn-secondary' }}" 
       style="font-size: 13px; {{ $totalTerlambat > 0 ? 'font-weight: bold;' : '' }}">
        ⚠️ Terlambat ({{ $totalTerlambat }})
    </a>
    <a href="{{ route('sirkulasi.index', array_merge($filters, ['status' => 'kembali'])) }}"
       class="btn {{ $currentStatus === 'kembali' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        ✅ Sudah Kembali ({{ $totalKembali }})
    </a>
</div>

<!-- Filter Box Pencarian -->
<div class="filter-box">
    <form method="GET" action="{{ route('sirkulasi.index') }}">
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <div class="filter-grid" style="grid-template-columns: 2fr 1fr auto;">
            <div>
                <label>Pencarian Bebas (Nama Peminjam, ID Arsip, Keperluan, Judul):</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ketik kata kunci pencarian...">
            </div>
            <div>
                <label>Filter Divisi:</label>
                <select name="divisi">
                    <option value="">-- Semua Divisi --</option>
                    @foreach ($divisiList as $div)
                        <option value="{{ $div }}" {{ ($filters['divisi'] ?? '') === $div ? 'selected' : '' }}>
                            {{ $div }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 8px;">
                <button type="submit" class="btn" style="height: 38px;">🔍 Cari</button>
                @if (!empty($filters['q']) || !empty($filters['divisi']))
                    <a href="{{ route('sirkulasi.index', ['status' => $currentStatus]) }}" class="btn btn-secondary" style="height: 20px; line-height: 20px;">Reset</a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tabel Daftar Sirkulasi -->
<div class="card" style="padding: 0; overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Berkas Jaminan / Arsip</th>
                <th>Peminjam & Divisi</th>
                <th>Keperluan</th>
                <th>Jadwal Peminjaman</th>
                <th>Approval Pimpinan</th>
                <th>Ketersediaan Fisik</th>
                <th>Status Sirkulasi</th>
                <th style="text-align: center; width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $row)
                @php
                    $arsip = $row->arsip;
                    $isLate = $row->isTerlambat();
                @endphp
                <tr style="{{ $isLate ? 'background: #fff5f5;' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-family: monospace; font-weight: bold; color: #1f3864; font-size: 13px;">
                            <a href="{{ route('arsip.show', $row->id_arsip) }}" style="color: #1f3864; text-decoration: none;">
                                {{ $row->id_arsip }}
                            </a>
                        </div>
                        <div style="font-size: 13px; font-weight: 500; margin: 2px 0;">
                            {{ $arsip?->judul_arsip ?? 'Arsip' }}
                        </div>
                        @if ($arsip?->isBerkasJaminan())
                            <span class="badge badge-vital" style="font-size: 10px;">BERKAS VITAL AGUNAN</span>
                        @endif
                        @if ($arsip?->lokasi)
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                📍 {{ $arsip->lokasi->gedung }} (Rak {{ $arsip->lokasi->nomor_rak }})
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #1e293b;">{{ $row->nama_peminjam }}</strong>
                        <div style="font-size: 12px; color: #64748b;">{{ $row->divisi }}</div>
                    </td>
                    <td style="max-width: 200px; font-size: 13px;">
                        {{ $row->keperluan }}
                    </td>
                    <td style="font-size: 12px; white-space: nowrap;">
                        <div>Pinjam: <strong>{{ $row->tanggal_pinjam?->format('d/m/Y') }}</strong></div>
                        <div>Rencana: <strong>{{ $row->tanggal_kembali_rencana?->format('d/m/Y') }}</strong></div>
                        @if ($row->tanggal_kembali_aktual)
                            <div style="color: #166534;">Kembali: <strong>{{ $row->tanggal_kembali_aktual?->format('d/m/Y') }}</strong></div>
                        @endif
                    </td>
                    <td>
                        @if ($row->status_approval === 'menunggu')
                            <span class="badge badge-menunggu">Menunggu Approval</span>
                            @if (auth()->user()->role === 'pimpinan')
                                <div style="margin-top: 4px;">
                                    <a href="{{ route('approval.index') }}" class="btn btn-sm" style="background: #f59e0b; color: #000; font-weight: bold;">
                                        ⚡ Review
                                    </a>
                                </div>
                            @endif
                        @elseif ($row->status_approval === 'disetujui')
                            <span class="badge badge-disetujui">Disetujui</span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Oleh: {{ $row->approver?->name ?? 'Pimpinan' }}
                            </div>
                        @elseif ($row->status_approval === 'ditolak')
                            <span class="badge badge-ditolak">Ditolak</span>
                            <div style="font-size: 11px; color: #b91c1c; margin-top: 2px;" title="{{ $row->catatan_approval }}">
                                {{ Str::limit($row->catatan_approval, 30) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if ($arsip?->status_ketersediaan === 'tersedia')
                            <span class="badge badge-tersedia">Tersedia di Rak</span>
                        @else
                            <span class="badge badge-dipinjam">Fisik Keluar</span>
                        @endif
                    </td>
                    <td>
                        @if ($row->status_sirkulasi === 'kembali')
                            <span class="badge badge-kembali">Telah Kembali</span>
                        @elseif ($isLate)
                            <span class="badge badge-terlambat">
                                ⚠️ Terlambat {{ $row->hari_terlambat }} Hari
                            </span>
                        @elseif ($row->status_approval === 'menunggu')
                            <span class="badge badge-menunggu">Permohonan</span>
                        @elseif ($row->status_approval === 'ditolak')
                            <span class="badge badge-ditolak">Batal/Ditolak</span>
                        @else
                            <span class="badge badge-dipinjam">Sedang Dipinjam</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 4px; justify-content: center; flex-direction: column;">
                            <a href="{{ route('sirkulasi.show', $row->id_pinjam) }}" class="btn btn-secondary btn-sm">
                                👁️ Detail Log
                            </a>

                            @if (auth()->user()->role === 'petugas' && $row->status_approval === 'disetujui' && $row->status_sirkulasi !== 'kembali')
                                <button type="button" 
                                        onclick="bukaModalKembali({{ $row->id_pinjam }}, '{{ $row->id_arsip }}', '{{ addslashes($row->nama_peminjam) }}')"
                                        class="btn btn-success btn-sm">
                                    📥 Pengembalian
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">
                        Belum ada data peminjaman sirkulasi berkas yang sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Form Pengembalian Berkas Fisik -->
<div id="modalKembali" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; color: #1f3864; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
            📥 Proses Pengembalian Berkas Fisik
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
            Konfirmasi penerimaan fisik berkas jaminan kembali ke depo/brankas arsip. Ketersediaan fisik berkas akan dipulihkan menjadi <strong>TERSEDIA</strong>.
        </p>

        <form id="formKembali" method="POST" action="">
            @csrf
            <div style="background: #f8fafc; padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; font-size: 13px;">
                <div>Kode Berkas: <strong id="modalIdArsip" style="color: #1f3864;"></strong></div>
                <div>Peminjam: <strong id="modalPeminjam"></strong></div>
            </div>

            <label style="font-size: 12px; font-weight: 600; color: #475569;">Tanggal Penerimaan Pengembalian:</label>
            <input type="date" name="tanggal_kembali_aktual" value="{{ date('Y-m-d') }}" required>

            <label style="font-size: 12px; font-weight: 600; color: #475569;">Catatan Kondisi Fisik Berkas:</label>
            <textarea name="catatan_pengembalian" rows="3" style="width: 100%; padding: 8px; box-sizing: border-box; margin: 4px 0 12px; border: 1px solid #cbd5e1; border-radius: 4px;" required placeholder="Contoh: Berkas jaminan diterima lengkap, segel utuh, dan langsung dikembalikan ke rak brankas.">Berkas fisik diterima dalam kondisi utuh, lengkap, dan disimpan kembali ke rak penyimpanan.</textarea>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="tutupModalKembali()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success">Konfirmasi Pengembalian Berkas</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalKembali(idPinjam, idArsip, peminjam) {
        document.getElementById('formKembali').action = '/sirkulasi/' + idPinjam + '/kembali';
        document.getElementById('modalIdArsip').innerText = idArsip;
        document.getElementById('modalPeminjam').innerText = peminjam;
        var modal = document.getElementById('modalKembali');
        modal.style.display = 'flex';
    }

    function tutupModalKembali() {
        var modal = document.getElementById('modalKembali');
        modal.style.display = 'none';
    }
</script>
@endsection

