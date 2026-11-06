@extends('layouts.app')
@section('title', 'Master Klasifikasi')
@section('content')
<div class="card">
    <div class="bar">
        <h2>Master Klasifikasi Dokumen</h2>
        @if (auth()->user()->role === 'petugas')
            <a class="btn" href="{{ route('klasifikasi.create') }}">+ Tambah</a>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama klasifikasi</th>
                <th>Jenis berkas</th>
                <th>Retensi aktif</th>
                <th>Retensi inaktif</th>
                <th>Dihitung dari</th>
                <th>Keterangan akhir</th>
                @if (auth()->user()->role === 'petugas')
                    <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td><strong>{{ $item->kode_klasifikasi }}</strong></td>
                    <td>{{ $item->nama_klasifikasi }}</td>
                    <td>{{ $item->jenis_berkas }}</td>
                    <td>{{ $item->retensi_aktif }} th</td>
                    <td>{{ $item->retensi_inaktif }} th</td>
                    <td>{{ $item->retensi_dihitung_dari }}</td>
                    <td>{{ $item->keterangan_akhir }}</td>
                    @if (auth()->user()->role === 'petugas')
                        <td>
                            <a href="{{ route('klasifikasi.edit', $item) }}">Edit</a>
                            <form method="POST" action="{{ route('klasifikasi.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Hapus klasifikasi ini?')">
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

