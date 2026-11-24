@extends('layouts.app')
@section('title', 'Form Usulan Pemusnahan Berkas - e-Arsip KSP')
@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('penyusutan.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Modul Penyusutan</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-top: 0; color: #1f3864; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        📝 Formulir Usulan Pemusnahan Berkas & Registrasi Berita Acara
    </h2>

    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; color: #991b1b;">
        <strong>⚠️ Peringatan Kepatuhan Regulasi Kearsipan & Validasi JRA:</strong>
        <p style="margin: 4px 0 0 0;">
            Pemusnahan berkas wajib memenuhi masa retensi inaktif sesuai JRA Koperasi.
            Sistem secara otomatis memblokir berkas agunan vital (Sertifikat/BPKB) dan dokumen kredit macet. Berkas yang diajukan akan diproses melalui <strong>Approval Berjenjang oleh Pimpinan</strong> sebelum eksekusi fisik dan penerbitan Berita Acara Legal.
        </p>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('penyusutan.store') }}">
        @csrf

        <label style="font-weight: 600; font-size: 13px; color: #334155;">Pilih Berkas Inaktif yang Diusulkan Musnah (Sesuai DUM): <span style="color: red;">*</span></label>
        <select name="id_arsip" required style="margin-bottom: 6px;">
            <option value="">-- Pilih Berkas Inaktif yang Memenuhi Syarat JRA --</option>
            @foreach ($arsipEligible as $item)
                @php
                    $selected = old('id_arsip', $selectedArsipId) === $item->id_arsip;
                @endphp
                <option value="{{ $item->id_arsip }}" {{ $selected ? 'selected' : '' }}>
                    [{{ $item->id_arsip }}] — {{ $item->judul_arsip }} 
                    (Klasifikasi: {{ $item->kode_klasifikasi }} | Retensi Jatuh Tempo: {{ $item->jadwal_penyusutan ?: 'Lewat' }})
                </option>
            @endforeach
        </select>
        <small style="color: #64748b; display: block; margin-bottom: 14px;">
            *Hanya berkas berstatus inaktif, berketerangan akhir musnah, dan bebas status Legal Hold yang ditampilkan.
        </small>

        <div class="grid-2">
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Metode Pemusnahan Fisik: <span style="color: red;">*</span></label>
                <input type="text" name="metode_pemusnahan" value="{{ old('metode_pemusnahan', 'Pencacahan Fisik (Cross-Cut Shredder) & Peleburan Kertas') }}" required placeholder="Contoh: Pencacahan fisik total">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Petugas / Ketua Panitia Eksekutor: <span style="color: red;">*</span></label>
                <input type="text" name="petugas_eksekutor" value="{{ old('petugas_eksekutor', auth()->user()->name) }}" required>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Saksi 1 (Bagian Hukum / Kepatuhan): <span style="color: red;">*</span></label>
                <input type="text" name="saksi_1" value="{{ old('saksi_1', 'Jerry Pratama (Staf Legal & Kepatuhan)') }}" required placeholder="Nama dan Jabatan Saksi 1">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Saksi 2 (Satuan Pengawas Internal): <span style="color: red;">*</span></label>
                <input type="text" name="saksi_2" value="{{ old('saksi_2', 'Gilang Ramadhan (Pengawas Internal KSP)') }}" required placeholder="Nama dan Jabatan Saksi 2">
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Lokasi Pelaksanaan Eksekusi Fisik:</label>
                <input type="text" name="lokasi_eksekusi" value="{{ old('lokasi_eksekusi', 'Ruang Pemusnahan Depo Arsip KSP') }}">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 13px; color: #334155;">Rujukan Peraturan / Jadwal Retensi:</label>
                <input type="text" value="JRA Koperasi Simpan Pinjam No. 04/2026" readonly style="background: #f1f5f9; color: #475569;">
            </div>
        </div>

        <label style="font-weight: 600; font-size: 13px; color: #334155;">Dasar Pertimbangan & Konsiderans Pemusnahan: <span style="color: red;">*</span></label>
        <textarea name="dasar_pertimbangan" rows="3" required style="width: 100%; padding: 8px; box-sizing: border-box; margin: 4px 0 16px; border: 1px solid #cbd5e1; border-radius: 4px;">{{ old('dasar_pertimbangan', 'Berkas telah melewati masa simpan aktif dan inaktif berdasarkan Jadwal Retensi Arsip (JRA). Berkas tidak lagi memiliki nilai guna administrasi, hukum, maupun operasional, dan pinjaman terkait telah dinyatakan lunas tanpa sengketa.') }}</textarea>

        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <a href="{{ route('penyusutan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-danger">
                📤 Ajukan Usulan & Terbitkan Berita Acara
            </button>
        </div>
    </form>
</div>
@endsection

