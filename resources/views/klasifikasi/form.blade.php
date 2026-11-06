@extends('layouts.app')
@section('title', 'Form Klasifikasi')
@section('content')
<div class="card" style="max-width: 560px;">
    <h2>{{ $item ? 'Edit' : 'Tambah' }} Klasifikasi</h2>
    @include('partials.errors')
    <form method="POST" action="{{ $item ? route('klasifikasi.update', $item) : route('klasifikasi.store') }}">
        @csrf
        @if ($item) @method('PUT') @endif

        <label>Kode (contoh: KP.03)</label>
        <input name="kode_klasifikasi" value="{{ old('kode_klasifikasi', $item->kode_klasifikasi ?? '') }}" {{ $item ? 'readonly' : 'required' }}>

        <label>Nama klasifikasi</label>
        <input name="nama_klasifikasi" value="{{ old('nama_klasifikasi', $item->nama_klasifikasi ?? '') }}" required>

        <label>Jenis berkas</label>
        <select name="jenis_berkas">
            @foreach (['vital', 'non_vital'] as $o)
                <option value="{{ $o }}" @selected(old('jenis_berkas', $item->jenis_berkas ?? 'non_vital') === $o)>{{ $o }}</option>
            @endforeach
        </select>

        <label>Retensi aktif (tahun)</label>
        <input type="number" min="0" name="retensi_aktif" value="{{ old('retensi_aktif', $item->retensi_aktif ?? '') }}" required>

        <label>Retensi inaktif (tahun)</label>
        <input type="number" min="0" name="retensi_inaktif" value="{{ old('retensi_inaktif', $item->retensi_inaktif ?? '') }}" required>

        <label>Retensi dihitung dari</label>
        <select name="retensi_dihitung_dari">
            @foreach (['tanggal_arsip', 'tanggal_lunas'] as $o)
                <option value="{{ $o }}" @selected(old('retensi_dihitung_dari', $item->retensi_dihitung_dari ?? 'tanggal_arsip') === $o)>{{ $o }}</option>
            @endforeach
        </select>

        <label>Keterangan akhir</label>
        <select name="keterangan_akhir">
            @foreach (['musnah', 'permanen', 'diserahkan'] as $o)
                <option value="{{ $o }}" @selected(old('keterangan_akhir', $item->keterangan_akhir ?? 'musnah') === $o)>{{ $o }}</option>
            @endforeach
        </select>

        <button type="submit">Simpan</button>
        <a href="/klasifikasi">Batal</a>
    </form>
</div>
@endsection