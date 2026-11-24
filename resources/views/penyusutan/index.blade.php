@extends('layouts.app')
@section('title', 'Modul Penyusutan & Berita Acara Pemusnahan - e-Arsip KSP')
@section('content')
<div class="card" style="border-left: 6px solid #b91c1c; margin-bottom: 20px;">
    <div class="bar" style="margin-bottom: 0;">
        <div>
            <h2 style="margin: 0 0 4px 0; color: #1f3864;">
                🔥 Modul Penyusutan, Eksekusi Pemusnahan & Berita Acara Legal
            </h2>
            <p style="margin: 0; color: #64748b; font-size: 14px;">
                Pengendalian penyusutan berkas inaktif berdasarkan JRA, perlindungan anti-pelanggaran fatal (Legal Hold), otorisasi pimpinan, dan penerbitan Berita Acara Pemusnahan berstandar kearsipan.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('penyusutan.create') }}" class="btn btn-danger">
                    + Ajukan Usulan Pemusnahan
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Matriks Statistik Penyusutan -->
<div class="grid-3" style="margin-bottom: 20px;">
    <div class="card" style="border-top: 4px solid #f59e0b;">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Daftar Usul Musnah (DUM)</span>
        <div style="font-size: 28px; font-weight: bold; color: #d97706; margin: 8px 0;">
            {{ $totalUsulMusnah }}
        </div>
        <div style="font-size: 12px; color: #64748b;">Berkas inaktif yang telah lampau jadwal retensinya</div>
    </div>

    <div class="card" style="border-top: 4px solid #dc2626;">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Proteksi Legal Hold (Dilarang Musnah)</span>
        <div style="font-size: 28px; font-weight: bold; color: #dc2626; margin: 8px 0;">
            {{ $totalLegalHold }}
        </div>
        <div style="font-size: 12px; color: #64748b;">Agunan vital & debitur kredit macet (dilindungi hukum)</div>
    </div>

    <div class="card" style="border-top: 4px solid #16a34a;">
        <span class="info-label" style="text-transform: uppercase; font-size: 11px;">Berita Acara Legal Terbit</span>
        <div style="font-size: 28px; font-weight: bold; color: #16a34a; margin: 8px 0;">
            {{ $totalBeritaAcara }}
        </div>
        <div style="font-size: 12px; color: #64748b;">{{ $totalTelahMusnah }} berkas resmi berstatus musnah</div>
    </div>
</div>

<!-- Tab Navigasi Modul Penyusutan -->
<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
    <a href="{{ route('penyusutan.index', ['tab' => 'usul-musnah']) }}"
       class="btn {{ $currentTab === 'usul-musnah' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        📋 Daftar Usul Musnah ({{ $totalUsulMusnah }})
    </a>
    <a href="{{ route('penyusutan.index', ['tab' => 'legal-hold']) }}"
       class="btn {{ $currentTab === 'legal-hold' ? 'btn-danger' : 'btn-secondary' }}" style="font-size: 13px;">
        🛡️ Proteksi Legal Hold ({{ $totalLegalHold }})
    </a>
    <a href="{{ route('penyusutan.index', ['tab' => 'berita-acara']) }}"
       class="btn {{ $currentTab === 'berita-acara' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        📜 Berita Acara Legal ({{ $totalBeritaAcara }})
        @if ($totalMenungguApproval > 0)
            <span style="background: #b91c1c; color: #fff; padding: 1px 6px; border-radius: 10px; font-size: 10px;">{{ $totalMenungguApproval }}</span>
        @endif
    </a>
    <a href="{{ route('penyusutan.index', ['tab' => 'telah-musnah']) }}"
       class="btn {{ $currentTab === 'telah-musnah' ? '' : 'btn-secondary' }}" style="font-size: 13px;">
        🗑️ Riwayat Berkas Musnah ({{ $totalTelahMusnah }})
    </a>
</div>

@if ($currentTab === 'usul-musnah')
    <!-- TAB 1: DAFTAR USUL MUSNAH (DUM) -->
    <div class="card" style="padding: 0; overflow-x: auto;">
        <div style="padding: 16px 20px 8px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #1f3864;">📋 Daftar Usul Musnah (DUM) Berkas Inaktif</h3>
                <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">
                    Daftar berkas yang telah melewati masa retensi inaktif, berketerangan akhir musnah, dan telah dinyatakan lunas/selesai.
                </p>
            </div>
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('penyusutan.create') }}" class="btn btn-sm btn-danger">
                    + Ajukan Pemusnahan Berkas
                </a>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Registrasi Arsip</th>
                    <th>Judul & Uraian Berkas</th>
                    <th>Klasifikasi & JRA</th>
                    <th>Jadwal Penyusutan</th>
                    <th>Status Pelunasan</th>
                    <th>Ketersediaan Fisik</th>
                    <th style="text-align: center; width: 140px;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usulMusnah as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong style="color: #1f3864; font-family: monospace;">
                                <a href="{{ route('arsip.show', $item->id_arsip) }}" style="color: #1f3864; text-decoration: none;">
                                    {{ $item->id_arsip }}
                                </a>
                            </strong>
                            <div style="font-size: 11px; color: #64748b;">Tgl Berkas: {{ $item->tanggal_arsip }}</div>
                        </td>
                        <td>
                            <strong>{{ $item->judul_arsip }}</strong>
                            @if ($item->referensi_nomor)
                                <div style="font-size: 11px; color: #475569; font-family: monospace;">Rek: {{ $item->referensi_nomor }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:#e0e7ff; color:#3730a3;">{{ $item->kode_klasifikasi }}</span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                {{ $item->klasifikasi?->nama_klasifikasi }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-inaktif">INAKTIF</span>
                            <div style="font-size: 11px; color: #b91c1c; font-weight: bold; margin-top: 2px;">
                                Jatuh Tempo: {{ $item->jadwal_penyusutan ?: '-' }}
                            </div>
                        </td>
                        <td>
                            @if ($item->pinjaman)
                                <span class="badge badge-lunas">LUNAS</span>
                                <div style="font-size: 11px; color: #166534; margin-top: 2px;">
                                    {{ $item->pinjaman->tanggal_lunas }}
                                </div>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#475569;">Non-Pinjaman</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->status_ketersediaan === 'tersedia')
                                <span class="badge badge-tersedia">Tersedia di Rak</span>
                            @else
                                <span class="badge badge-dipinjam">Fisik Dipinjam</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($item->beritaAcara)
                                <a href="{{ route('penyusutan.show', $item->beritaAcara->no_berita_acara) }}" class="btn btn-sm btn-secondary">
                                    Lihat Usulan BA
                                </a>
                            @else
                                <a href="{{ route('penyusutan.create', ['id_arsip' => $item->id_arsip]) }}" class="btn btn-sm btn-danger">
                                    🔥 Usulkan Musnah
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: #64748b;">
                            Tidak ada berkas inaktif yang saat ini memenuhi kriteria usul musnah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@elseif ($currentTab === 'legal-hold')
    <!-- TAB 2: DAFTAR PROTEKSI LEGAL HOLD (PENCEGAHAN PELANGGARAN FATAL) -->
    <div class="card" style="padding: 0; overflow-x: auto; border-top: 4px solid #dc2626;">
        <div style="padding: 16px 20px 8px 20px; border-bottom: 1px solid #e2e8f0; background: #fff5f5;">
            <div style="display: flex; gap: 8px; align-items: center;">
                <h3 style="margin: 0; color: #991b1b;">
                    🛡️ Daftar Proteksi Legal Hold & Perlindungan Agunan Vital (Anti-Pelanggaran Fatal)
                </h3>
            </div>
            <p style="margin: 4px 0 0 0; color: #7f1d1d; font-size: 13px;">
                <strong>Kebijakan Mutlak Kearsipan:</strong> Dokumen jaminan nasabah (Sertifikat/BPKB) dan berkas debitur kredit macet <strong>DIBLOKIR SECARA OTOMATIS</strong> oleh sistem dari daftar usul musnah demi mencegah penghilangan barang bukti hukum dan pelanggaran regulasi kearsipan.
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Registrasi Arsip</th>
                    <th>Judul & Uraian Berkas</th>
                    <th>Kategori Dokumen</th>
                    <th>Status Kredit / Pinjaman</th>
                    <th>Alasan Perlindungan Hukum (Legal Hold)</th>
                    <th style="text-align: center; width: 140px;">Status Proteksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($legalHold as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong style="font-family: monospace; color: #1f3864;">
                                <a href="{{ route('arsip.show', $item->id_arsip) }}" style="color: #1f3864; text-decoration: none;">
                                    {{ $item->id_arsip }}
                                </a>
                            </strong>
                            @if ($item->lokasi)
                                <div style="font-size: 11px; color: #64748b;">
                                    📍 {{ $item->lokasi->gedung }} (Rak {{ $item->lokasi->nomor_rak }})
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->judul_arsip }}</strong>
                            @if ($item->pinjaman && $item->pinjaman->anggota)
                                <div style="font-size: 12px; color: #334155;">
                                    Debitur: <strong>{{ $item->pinjaman->anggota->nama_lengkap }}</strong>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($item->isBerkasJaminan())
                                <span class="badge badge-vital">AGUNAN VITAL</span>
                            @elseif ($item->klasifikasi?->keterangan_akhir === 'permanen')
                                <span class="badge badge-permanen">PERMANEN</span>
                            @else
                                <span class="badge badge-non-vital">BERKAS KREDIT</span>
                            @endif
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Ket. Akhir: <strong>{{ strtoupper($item->klasifikasi?->keterangan_akhir ?? '-') }}</strong>
                            </div>
                        </td>
                        <td>
                            @if ($item->pinjaman)
                                <span class="badge badge-{{ $item->pinjaman->status_pinjaman }}">
                                    {{ strtoupper($item->pinjaman->status_pinjaman) }}
                                </span>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    Rek: {{ $item->pinjaman->no_rekening_pinjaman }}
                                </div>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="max-width: 280px; font-size: 12px; color: #991b1b; line-height: 1.4;">
                            <strong>{{ $item->alasan_legal_hold }}</strong>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #dc2626; color: #fff; font-size: 10px; padding: 4px 8px;">
                                ⛔ DILARANG MUSNAH
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: #64748b;">
                            Tidak ada berkas yang terkena status Legal Hold.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@elseif ($currentTab === 'berita-acara')
    <!-- TAB 3: DAFTAR BERITA ACARA LEGAL -->
    <div class="card" style="padding: 0; overflow-x: auto;">
        <div style="padding: 16px 20px 8px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #1f3864;">📜 Daftar Berita Acara Pemusnahan Arsip Legal</h3>
                <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">
                    Dokumen legalitas Berita Acara pemusnahan berkas lengkap dengan tanda tangan 4 pihak berwenang.
                </p>
            </div>
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('penyusutan.create') }}" class="btn btn-sm btn-danger">
                    + Buat Usulan Berita Acara
                </a>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Berita Acara</th>
                    <th>Berkas yang Dimusnahkan</th>
                    <th>Tgl Eksekusi / Pengajuan</th>
                    <th>Approval Pimpinan</th>
                    <th>Status Eksekusi Fisik</th>
                    <th>Metode Pemusnahan</th>
                    <th style="text-align: center; width: 170px;">Aksi Legal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($beritaAcaraList as $idx => $ba)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong style="font-family: monospace; color: #1f3864; font-size: 13px;">
                                {{ $ba->no_berita_acara }}
                            </strong>
                            <div style="font-size: 11px; color: #64748b;">Eksekutor: {{ $ba->petugas_eksekutor }}</div>
                        </td>
                        <td>
                            <div style="font-family: monospace; font-weight: bold; font-size: 12px;">
                                <a href="{{ route('arsip.show', $ba->id_arsip) }}" style="color: #1f3864; text-decoration: none;">
                                    {{ $ba->id_arsip }}
                                </a>
                            </div>
                            <div style="font-size: 13px;">{{ $ba->arsip?->judul_arsip }}</div>
                        </td>
                        <td style="font-size: 12px; white-space: nowrap;">
                            {{ $ba->tanggal_eksekusi ? $ba->tanggal_eksekusi->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            @if ($ba->status_approval === 'menunggu')
                                <span class="badge badge-menunggu">Menunggu Approval</span>
                            @elseif ($ba->status_approval === 'disetujui')
                                <span class="badge badge-disetujui">Disetujui Pimpinan</span>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    Oleh: {{ $ba->approver?->name ?? 'Pimpinan' }}
                                </div>
                            @else
                                <span class="badge badge-ditolak">Ditolak</span>
                            @endif
                        </td>
                        <td>
                            @if ($ba->arsip?->status_arsip === 'musnah')
                                <span class="badge badge-musnah">🔥 TELAH MUSNAH</span>
                            @else
                                <span class="badge badge-inaktif">⏳ Belum Dieksekusi</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; max-width: 200px;">
                            {{ $ba->metode_pemusnahan }}
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center; flex-direction: column;">
                                <a href="{{ route('penyusutan.show', $ba->no_berita_acara) }}" class="btn btn-secondary btn-sm">
                                    👁️ Detail BA
                                </a>
                                <a href="{{ route('penyusutan.cetak', $ba->no_berita_acara) }}" target="_blank" class="btn btn-sm" style="background: #0284c7; color: #fff;">
                                    🖨️ Cetak BA PDF
                                </a>

                                @if (auth()->user()->role === 'petugas' && $ba->status_approval === 'disetujui' && $ba->arsip?->status_arsip !== 'musnah')
                                    <form method="POST" action="{{ route('penyusutan.eksekusi', $ba->no_berita_acara) }}" style="margin: 0;" onsubmit="return confirm('PERINGATAN! Anda akan mengeksekusi pemusnahan berkas fisik dan mengubah status arsip menjadi MUSNAH secara permanen. Lanjutkan?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" style="width: 100%;">
                                            🔥 Eksekusi Fisik
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: #64748b;">
                            Belum ada dokumen Berita Acara Pemusnahan yang diterbitkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@elseif ($currentTab === 'telah-musnah')
    <!-- TAB 4: RIWAYAT BERKAS MUSNAH -->
    <div class="card" style="padding: 0; overflow-x: auto;">
        <div style="padding: 16px 20px 8px 20px; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0; color: #1f3864;">🗑️ Riwayat Berkas yang Telah Resmi Dimusnahkan</h3>
            <p style="margin: 2px 0 0 0; color: #64748b; font-size: 13px;">
                Arsip inaktif yang telah sah dimusnahkan secara fisik dan digital berdasarkan Berita Acara Legal.
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Registrasi Arsip</th>
                    <th>Judul Berkas</th>
                    <th>Klasifikasi</th>
                    <th>Nomor Berita Acara Legal</th>
                    <th>Status Arsip</th>
                    <th style="text-align: center; width: 140px;">Bukti Legal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($telahMusnah as $idx => $m)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold; color: #1f3864;">
                            {{ $m->id_arsip }}
                        </td>
                        <td>{{ $m->judul_arsip }}</td>
                        <td>
                            <strong>{{ $m->kode_klasifikasi }}</strong> - {{ $m->klasifikasi?->nama_klasifikasi }}
                        </td>
                        <td>
                            @if ($m->no_berita_acara_pemusnahan)
                                <a href="{{ route('penyusutan.show', $m->no_berita_acara_pemusnahan) }}" style="color: #1f3864; font-weight: bold;">
                                    {{ $m->no_berita_acara_pemusnahan }}
                                </a>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-musnah">MUSNAH</span>
                        </td>
                        <td style="text-align: center;">
                            @if ($m->no_berita_acara_pemusnahan)
                                <a href="{{ route('penyusutan.cetak', $m->no_berita_acara_pemusnahan) }}" target="_blank" class="btn btn-sm btn-secondary">
                                    🖨️ Cetak BA PDF
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: #64748b;">
                            Belum ada berkas yang dimusnahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection

