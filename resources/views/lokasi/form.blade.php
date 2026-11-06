@extends('layouts.app')
@section('title', 'Form Lokasi')
@section('content')
<div class="card" style="max-width: 560px;">
    <h2>{{ $item ? 'Edit' : 'Tambah' }} Lokasi</h2>
    @include('partials.errors')
    <form method="POST" action="{{ $item ? route('lokasi.update', $item) : route('lokasi.store') }}">
        @csrf
        @if ($item) @method('PUT') @endif

        <label>Gedung / ruang</label>
        <input name="gedung" list="daftar-gedung" value="{{ old('gedung', $item->gedung ?? '') }}" required>
        <datalist id="daftar-gedung">
            <option value="Brankas Khazanah">
            <option value="Gedung Depo Utama">
        </datalist>

        <label>Nomor rak / brankas (contoh: RK-02, BK-01)</label>
        <input name="nomor_rak" value="{{ old('nomor_rak', $item->nomor_rak ?? '') }}" required>

        <label>Ambalan / baris (contoh: RF-A)</label>
        <input name="ambalan" value="{{ old('ambalan', $item->ambalan ?? '') }}" required>

        <label>Nomor boks / ordner (contoh: OD-04)</label>
        <input name="nomor_boks" value="{{ old('nomor_boks', $item->nomor_boks ?? '') }}" required>

        <p style="font-size:13px;color:#666">Kode lokasi dibuat otomatis: rak.ambalan.boks, misalnya RK-02.RF-A.OD-04</p>
        <button type="submit">Simpan</button>
        <a href="/lokasi">Batal</a>
    </form>
</div>
@endsection