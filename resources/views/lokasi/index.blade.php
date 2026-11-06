@extends('layouts.app')
@section('title', 'Master Lokasi')
@section('content')
<div class="card">
    <div class="bar">
        <h2>Master Lokasi Simpan Fisik</h2>
        @if (auth()->user()->role === 'petugas')
            <a class="btn" href="{{ route('lokasi.create') }}">+ Tambah</a>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Kode lokasi</th>
                <th>Gedung / ruang</th>
                <th>Rak</th>
                <th>Ambalan</th>
                <th>Boks / ordner</th>
                @if (auth()->user()->role === 'petugas')
                    <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $l)
                <tr>
                    <td>{{ $l->kode_lokasi }}</td>
                    <td>{{ $l->gedung }}</td>
                    <td>{{ $l->nomor_rak }}</td>
                    <td>{{ $l->ambalan }}</td>
                    <td>{{ $l->nomor_boks }}</td>
                    @if (auth()->user()->role === 'petugas')
                        <td>
                            <a href="{{ route('lokasi.edit', $l) }}">Edit</a>
                            <form method="POST" action="{{ route('lokasi.destroy', $l) }}" style="display:inline" onsubmit="return confirm('Hapus lokasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection