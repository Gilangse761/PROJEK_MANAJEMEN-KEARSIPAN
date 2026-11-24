@extends('layouts.app')
@section('title', 'Form Permohonan Peminjaman Berkas - e-Arsip KSP')
@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('sirkulasi.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Sirkulasi</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-top: 0; color: #1f3864; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        📝 Formulir Permohonan Peminjaman Berkas Jaminan / Arsip
    </h2>

    <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; color: #1e40af;">
        <strong>🛡️ Kebijakan Kepatuhan & Pengendalian Fisik Berkas Jaminan:</strong>
        <p style="margin: 4px 0 0 0;">
            Sesuai SOP Kearsipan KSP, berkas agunan vital (Sertifikat/BPKB) tidak boleh dikeluarkan dari brankas/depo tanpa otorisasi tertulis pimpinan.
            Setelah form ini disubmit, status peminjaman berstatus <strong>Menunggu Persetujuan Pimpinan</strong> dan berkas fisik tetap terkunci di brankas hingga izin approval diterbitkan.
        </p>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('sirkulasi.store') }}">
        @csrf

        <label style="font-weight: 600; font-size: 13px; color: #334155;">Pilih Berkas Jaminan / Arsip Fisik yang Tersedia: <span style="color: red;">*</span></label>
        <select name="id_arsip" required style="margin-bottom: 6px;">
            <option value="">-- Pilih Dokumen Arsip (Hanya yang berstatus TERSEDIA di rak) --</option>
            @foreach ($arsipTersedia as $item)
                @php
                    $isVital = $item->isBerkasJaminan();
                    $selected = old('id_arsip', $selectedArsipId) === $item->id_arsip;
                @endphp
                <option value="{{ $item->id_arsip }}" {{ $selected ? 'selected' : '' }}>
                    [{{ $item->id_arsip }}] — {{ $item->judul_arsip }} 
                    ({{ $isVital ? '⚠️ VITAL AGUNAN' : 'NON-VITAL' }} | Lokasi: {{ $item->lokasi?->gedung ?? '-' }} Rak {{ $item->lokasi?->nomor_rak ?? '-' }})
                </option>
            @endforeach
        </select>
        <small style="color: #64748b; display: block; margin-bottom: 14px;">
            *Hanya dokumen yang ketersediaan fisiknya sedang ada di rak yang ditampilkan.
        </small>

        <div class="grid-2">
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Nama Lengkap Peminjam: <span style="color: red;">*</span></label>
                <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}" required placeholder="Contoh: Jerry Pratama">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Divisi / Unit Kerja Peminjam: <span style="color: red;">*</span></label>
                <select name="divisi" required>
                    <option value="">-- Pilih Divisi --</option>
                    <option value="Remedial & Penagihan" {{ old('divisi') === 'Remedial & Penagihan' ? 'selected' : '' }}>Remedial & Penagihan</option>
                    <option value="Marketing" {{ old('divisi') === 'Marketing' ? 'selected' : '' }}>Marketing / Account Officer</option>
                    <option value="Legal & Kepatuhan" {{ old('divisi') === 'Legal & Kepatuhan' ? 'selected' : '' }}>Legal & Kepatuhan</option>
                    <option value="Operasional Kredit" {{ old('divisi') === 'Operasional Kredit' ? 'selected' : '' }}>Operasional Kredit</option>
                    <option value="Audit Internal" {{ old('divisi') === 'Audit Internal' ? 'selected' : '' }}>Audit Internal / Pengawas</option>
                    <option value="Pimpinan / Pengurus" {{ old('divisi') === 'Pimpinan / Pengurus' ? 'selected' : '' }}>Pimpinan / Pengurus</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Tanggal Mulai Pinjam: <span style="color: red;">*</span></label>
                <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Rencana Tanggal Pengembalian: <span style="color: red;">*</span></label>
                <input type="date" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana', date('Y-m-d', strtotime('+3 days'))) }}" required>
            </div>
        </div>

        <label style="font-weight: 600; font-size: 13px; color: #334155;">Keperluan / Alasan Peminjaman Berkas Jaminan: <span style="color: red;">*</span></label>
        <textarea name="keperluan" rows="3" required style="width: 100%; padding: 8px; box-sizing: border-box; margin: 4px 0 16px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="Jelaskan secara spesifik urgensi dan keperluan peminjaman fisik dokumen...">{{ old('keperluan') }}</textarea>

        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <a href="{{ route('sirkulasi.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">📤 Ajukan Permohonan Peminjaman</button>
        </div>
    </form>
</div>
@endsection

