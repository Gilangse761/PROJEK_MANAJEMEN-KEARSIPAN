@extends('layouts.app')
@section('title', 'Registrasi Arsip Baru')
@section('content')
<div class="card" style="max-width: 720px; margin: 0 auto;">
    <div class="bar">
        <div>
            <h2 style="margin: 0 0 4px 0;">Registrasi Arsip Dokumen Baru</h2>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Pemberkasan dokumen fisik & digital dengan kalkulasi otomatis JRA.</p>
        </div>
        <a href="{{ route('arsip.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('arsip.store') }}" enctype="multipart/form-data">
        @csrf

        <label for="kode_klasifikasi"><strong>Klasifikasi Kearsipan (JRA)</strong> <span style="color:#b00020">*</span></label>
        <select id="kode_klasifikasi" name="kode_klasifikasi" required>
            <option value="">- Pilih Klasifikasi Dokumen -</option>
            @foreach ($klasifikasi as $k)
                <option value="{{ $k->kode_klasifikasi }}" @selected(old('kode_klasifikasi') === $k->kode_klasifikasi)>
                    {{ $k->kode_klasifikasi }} - {{ $k->nama_klasifikasi }} [{{ strtoupper($k->jenis_berkas) }} | Aktif: {{ $k->retensi_aktif }}th, Inaktif: {{ $k->retensi_inaktif }}th]
                </option>
            @endforeach
        </select>
        <small style="display:block; margin-top:-8px; margin-bottom:12px; color:#64748b;">
            Klasifikasi menentukan masa retensi aktif, inaktif, dan tindakan akhir (musnah / permanen / diserahkan).
        </small>

        <label for="id_lokasi"><strong>Titik Lokasi Simpan Fisik (Depo / Brankas)</strong> <span style="color:#b00020">*</span></label>
        <select id="id_lokasi" name="id_lokasi" required>
            <option value="">- Pilih Titik Rak Presisi -</option>
            @foreach ($lokasi as $l)
                <option value="{{ $l->id_lokasi }}" @selected((string) old('id_lokasi') === (string) $l->id_lokasi)>
                    {{ $l->kode_lokasi }} — {{ $l->gedung }} (Rak: {{ $l->nomor_rak }}, Ambalan: {{ $l->ambalan }}, Boks: {{ $l->nomor_boks }})
                </option>
            @endforeach
        </select>

        <label for="referensi_nomor"><strong>Nomor Rekening Pinjaman / Simpanan</strong> (Wajib untuk klasifikasi KP.*)</label>
        <input id="referensi_nomor" name="referensi_nomor" list="daftar-pinjaman" value="{{ old('referensi_nomor') }}" placeholder="Ketik atau pilih nomor rekening pinjaman nasabah..." autocomplete="off">
        <datalist id="daftar-pinjaman">
            @foreach ($pinjaman as $p)
                <option value="{{ $p->no_rekening_pinjaman }}">{{ $p->anggota->nama_lengkap }} (Status: {{ $p->status_pinjaman }}, Plafon: Rp {{ number_format($p->nominal_pinjaman, 0, ',', '.') }})</option>
            @endforeach
        </datalist>
        <small style="display:block; margin-top:-8px; margin-bottom:12px; color:#64748b;">
            Untuk berkas jaminan vital (KP.03), masa retensi baru mulai berjalan setelah rekening berstatus LUNAS.
        </small>

        <label for="judul_arsip"><strong>Judul / Uraian Isi Berkas</strong> <span style="color:#b00020">*</span></label>
        <input id="judul_arsip" name="judul_arsip" value="{{ old('judul_arsip') }}" placeholder="Contoh: Sertifikat SHM No. 1234 An. Budi Santoso" required>

        <div class="grid-2">
            <div>
                <label for="tanggal_arsip"><strong>Tanggal Dokumen / Penciptaan</strong> <span style="color:#b00020">*</span></label>
                <input type="date" id="tanggal_arsip" name="tanggal_arsip" value="{{ old('tanggal_arsip', date('Y-m-d')) }}" required>
            </div>
            <div>
                <label for="media_arsip"><strong>Bentuk Media Fisik/Digital</strong></label>
                <select id="media_arsip" name="media_arsip">
                    @foreach (['kertas' => 'Kertas (Fisik Asli)', 'digital' => 'Digital Murni', 'hybrid' => 'Hybrid (Fisik & Scan Digital)'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('media_arsip', 'kertas') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fitur Upload Scan PDF (ARSIP-009) -->
        <div style="background: #f0fdf4; border: 1px dashed #22c55e; border-radius: 6px; padding: 14px; margin: 12px 0 18px 0;">
            <label for="file_digital" style="color: #166534; font-weight: bold; margin-bottom: 4px; display: block;">
                📄 Upload Berkas Scan PDF Jaminan / Agunan (Opsional saat registrasi)
            </label>
            <input type="file" id="file_digital" name="file_digital" accept="application/pdf" style="background:#fff; border-color:#86efac; margin-bottom:4px;">
            <p style="font-size: 12px; color: #15803d; margin: 4px 0 0 0;">
                Format yang didukung: <strong>Dokumen PDF (Maksimal 10 MB)</strong>.<br>
                🔒 Berkas akan otomatis disimpan secara terenkripsi (AES-256) pada direktori server yang terlindungi.
            </p>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; margin-top: 16px;">
            <button type="submit" class="btn btn-success">💾 Simpan & Hitung JRA Otomatis</button>
            <a href="{{ route('arsip.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
