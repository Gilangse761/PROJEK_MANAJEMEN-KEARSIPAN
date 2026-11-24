@extends('layouts.app')
@section('title', 'Approval Peminjaman Berkas - e-Arsip KSP')
@section('content')
<div class="card" style="border-left: 6px solid #f59e0b; margin-bottom: 20px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <h2 style="margin: 0 0 4px 0; color: #1f3864;">⚡ Panel Approval Berjenjang Peminjaman Berkas Jaminan (Approval Pimpinan)</h2>
            <p style="margin: 0; color: #64748b; font-size: 14px;">
                Otorisasi pengeluaran fisik berkas agunan/arsip vital oleh Kepala Bagian Kredit / Pimpinan.
                Fisik berkas tidak dapat diserahterimakan sebelum disetujui secara resmi.
            </p>
        </div>
        <div>
            <span class="badge" style="background: #f59e0b; color: #000; font-size: 13px; padding: 6px 12px;">
                {{ $totalMenunggu }} Menunggu Approval
            </span>
        </div>
    </div>
</div>

<!-- Section 1: Daftar Permohonan Menunggu Persetujuan -->
<div class="card" style="margin-bottom: 24px;">
    <h3 style="margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; color: #1f3864;">
        ⏳ Permohonan Menunggu Keputusan Pimpinan ({{ $menungguList->count() }})
    </h3>

    @if ($menungguList->isEmpty())
        <div style="text-align: center; padding: 36px 16px; color: #64748b; background: #f8fafc; border-radius: 6px;">
            <div style="font-size: 32px; margin-bottom: 8px;">✨</div>
            <strong style="font-size: 15px; color: #334155;">Semua Permohonan Telah Diproses!</strong>
            <p style="margin: 4px 0 0 0; font-size: 13px;">Saat ini tidak ada berkas jaminan yang tertahan menunggu persetujuan pimpinan.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach ($menungguList as $item)
                @php
                    $arsip = $item->arsip;
                    $pinjaman = $arsip?->pinjaman;
                @endphp
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span class="badge" style="background: #1f3864; color: #fff; font-size: 12px;">
                                {{ $item->id_arsip }}
                            </span>
                            @if ($arsip?->isBerkasJaminan())
                                <span class="badge badge-vital">BERKAS VITAL AGUNAN</span>
                            @endif
                            <h4 style="margin: 6px 0 2px 0; font-size: 17px; color: #0f172a;">
                                {{ $arsip?->judul_arsip ?? 'Arsip' }}
                            </h4>
                            <div style="font-size: 12px; color: #64748b;">
                                📍 Lokasi: <strong>{{ $arsip?->lokasi?->gedung ?? '-' }}</strong> | Rak: <strong>{{ $arsip?->lokasi?->nomor_rak ?? '-' }}</strong> | Ambalan: <strong>{{ $arsip?->lokasi?->ambalan ?? '-' }}</strong> | Boks: <strong>{{ $arsip?->lokasi?->nomor_boks ?? '-' }}</strong>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span class="badge badge-menunggu">STATUS: MENUNGGU PERSETUJUAN</span>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                Diajukan: {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid-2" style="background: #f8fafc; padding: 12px 14px; border-radius: 6px; margin-bottom: 14px; font-size: 13px;">
                        <div>
                            <div style="margin-bottom: 6px;">
                                <span style="color: #64748b;">Pemohon:</span> 
                                <strong style="color: #1e293b;">{{ $item->nama_peminjam }}</strong> 
                                (Divisi: <strong>{{ $item->divisi }}</strong>)
                            </div>
                            <div>
                                <span style="color: #64748b;">Rencana Durasi:</span> 
                                <strong>{{ $item->tanggal_pinjam?->format('d/m/Y') }}</strong> s/d <strong>{{ $item->tanggal_kembali_rencana?->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                        <div>
                            @if ($pinjaman)
                                <div style="margin-bottom: 6px;">
                                    <span style="color: #64748b;">Nasabah Debitur:</span> 
                                    <strong>{{ $pinjaman->anggota?->nama_lengkap ?? '-' }}</strong> 
                                    (No Rek: <code>{{ $pinjaman->no_rekening_pinjaman }}</code>)
                                </div>
                                <div>
                                    <span style="color: #64748b;">Status Kredit:</span> 
                                    <span class="badge badge-{{ $pinjaman->status_pinjaman }}">{{ strtoupper($pinjaman->status_pinjaman) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <span style="font-size: 12px; font-weight: 600; color: #475569;">Keperluan & Alasan Peminjaman:</span>
                        <div style="background: #fff; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; font-size: 13px; margin-top: 4px;">
                            {{ $item->keperluan }}
                        </div>
                    </div>

                    <!-- Tindakan Pimpinan: Form Setujui & Form Tolak -->
                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 14px; display: flex; gap: 12px; flex-wrap: wrap;">
                        <!-- Form Setujui -->
                        <form method="POST" action="{{ route('approval.setujui', $item->id_pinjam) }}" style="flex: 2; margin: 0;">
                            @csrf
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="catatan_approval" placeholder="Catatan persetujuan (opsional, misal: Disetujui, jaga kerahasiaan berkas)" style="margin: 0; font-size: 13px;">
                                <button type="submit" class="btn btn-success" style="white-space: nowrap; font-weight: bold;">
                                    ✓ Setujui & Keluarkan Berkas
                                </button>
                            </div>
                        </form>

                        <!-- Form Tolak -->
                        <form method="POST" action="{{ route('approval.tolak', $item->id_pinjam) }}" style="flex: 1; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK permohonan peminjaman berkas ini?');">
                            @csrf
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="catatan_approval" placeholder="Alasan penolakan (wajib)" required style="margin: 0; font-size: 13px;">
                                <button type="submit" class="btn btn-danger" style="white-space: nowrap;">
                                    ✕ Tolak
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Section 2: Riwayat Keputusan Approval -->
<div class="card" style="padding: 0; overflow-x: auto;">
    <div style="padding: 16px 20px 8px 20px;">
        <h3 style="margin: 0; color: #1f3864;">📜 Riwayat Audit Approval & Keputusan Terdahulu</h3>
        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">
            Log transparansi otorisasi pimpinan untuk setiap sirkulasi berkas jaminan.
        </p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Berkas Arsip</th>
                <th>Peminjam & Divisi</th>
                <th>Waktu Keputusan</th>
                <th>Keputusan Pimpinan</th>
                <th>Pimpinan Otorisator</th>
                <th>Catatan / Instruksi</th>
                <th style="text-align: center;">Log Sirkulasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayatList as $idx => $hist)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-family: monospace; font-weight: bold; color: #1f3864; font-size: 12px;">
                            <a href="{{ route('arsip.show', $hist->id_arsip) }}" style="color: #1f3864; text-decoration: none;">
                                {{ $hist->id_arsip }}
                            </a>
                        </div>
                        <div style="font-size: 13px;">{{ $hist->arsip?->judul_arsip }}</div>
                    </td>
                    <td>
                        <strong>{{ $hist->nama_peminjam }}</strong>
                        <div style="font-size: 11px; color: #64748b;">{{ $hist->divisi }}</div>
                    </td>
                    <td style="font-size: 12px; white-space: nowrap;">
                        {{ $hist->tanggal_approval ? $hist->tanggal_approval->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>
                        @if ($hist->status_approval === 'disetujui')
                            <span class="badge badge-disetujui">DISETUJUI</span>
                        @else
                            <span class="badge badge-ditolak">DITOLAK</span>
                        @endif
                    </td>
                    <td>{{ $hist->approver?->name ?? '-' }}</td>
                    <td style="font-size: 12px; font-style: italic; max-width: 250px;">
                        {{ $hist->catatan_approval ?: '-' }}
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('sirkulasi.show', $hist->id_pinjam) }}" class="btn btn-secondary btn-sm">
                            👁️ Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: #64748b;">
                        Belum ada riwayat keputusan approval.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
