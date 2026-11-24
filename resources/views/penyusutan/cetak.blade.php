<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Pemusnahan Arsip - {{ $ba->no_berita_acara }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
            font-size: 13pt;
            line-height: 1.5;
        }

        .paper {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            border: 1px solid #d1d5db;
        }

        .action-bar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-print { background: #0284c7; color: #fff; }
        .btn-back { background: #e2e8f0; color: #334155; }

        /* KOP SURAT */
        .kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .kop h2 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .kop h3 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .kop p {
            margin: 2px 0 0 0;
            font-size: 10pt;
            font-style: italic;
        }

        /* JUDUL DOKUMEN */
        .doc-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .doc-title h1 {
            margin: 0;
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title p {
            margin: 4px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
        }

        /* ISI SURAT */
        p.content {
            text-align: justify;
            text-indent: 36px;
            margin: 12px 0;
        }

        .party-list {
            margin: 12px 0 16px 20px;
        }
        .party-item {
            margin-bottom: 6px;
            display: flex;
        }
        .party-role { width: 180px; font-weight: bold; }
        .party-name { flex: 1; }

        /* TABEL LAMPIRAN ARSIP */
        table.lampiran {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 11pt;
        }
        table.lampiran th, table.lampiran td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        table.lampiran th {
            background: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        /* BLOK TANDA TANGAN */
        .signatures {
            margin-top: 36px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            text-align: center;
            page-break-inside: avoid;
        }
        .sig-block {
            padding: 8px;
        }
        .sig-space {
            height: 65px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .sig-title {
            font-size: 10pt;
        }

        @media print {
            body { background: #fff; padding: 0; font-size: 12pt; }
            .action-bar { display: none !important; }
            .paper { border: none; box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="{{ route('penyusutan.show', $ba->no_berita_acara) }}" class="btn btn-back">← Kembali ke Detail</a>
    <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Berita Acara / Unduh PDF</button>
</div>

<div class="paper">
    <!-- KOP RESMI KOPERASI -->
    <div class="kop">
        <h2>KOPERASI SIMPAN PINJAM (KSP) ARTHA MAKMUR SEJAHTERA</h2>
        <h3>UNIT PENGELOLA KEARSIPAN & DEPO ARSIP UTAMA</h3>
        <p>Badan Hukum No: AHU-001234.AH.01.26.TAHUN 2020 | Kantor Pusat: Gedung Depo Lt. 2, Jakarta</p>
        <p>Telp: (021) 555-0199 | Website: earsip.ksp-arthamakmur.test | Email: kearsipan@ksp-arthamakmur.test</p>
    </div>

    <!-- JUDUL RESMI -->
    <div class="doc-title">
        <h1>BERITA ACARA PEMUSNAHAN ARSIP</h1>
        <p>Nomor: {{ $ba->no_berita_acara }}</p>
    </div>

    <!-- PARAGRAF PEMBUKA -->
    <p class="content">
        Pada hari ini, <strong>{{ $ba->tanggal_eksekusi ? $ba->tanggal_eksekusi->translatedFormat('l, d F Y') : now()->translatedFormat('l, d F Y') }}</strong>, bertempat di <strong>{{ $ba->lokasi_eksekusi }}</strong>, kami yang bertanda tangan di bawah ini:
    </p>

    <!-- DAFTAR PIHAK -->
    <div class="party-list">
        <div class="party-item">
            <span class="party-role">1. Pelaksana Eksekutor</span>
            <span class="party-name">: <strong>{{ $ba->petugas_eksekutor }}</strong> (Petugas Arsip / Panitia Pemusnahan)</span>
        </div>
        <div class="party-item">
            <span class="party-role">2. Saksi I (Hukum/Legal)</span>
            <span class="party-name">: <strong>{{ $ba->saksi_1 ?: 'Jerry Pratama (Staf Legal & Kepatuhan)' }}</strong></span>
        </div>
        <div class="party-item">
            <span class="party-role">3. Saksi II (Pengawas)</span>
            <span class="party-name">: <strong>{{ $ba->saksi_2 ?: 'Gilang Ramadhan (Satuan Pengawas Internal)' }}</strong></span>
        </div>
        <div class="party-item">
            <span class="party-role">4. Pejabat Penyetuju</span>
            <span class="party-name">: <strong>{{ $ba->approver?->name ?? 'Kepala Bagian Kredit (Pimpinan)' }}</strong></span>
        </div>
    </div>

    <p class="content">
        Berdasarkan Jadwal Retensi Arsip (JRA) Koperasi Simpan Pinjam dan Keputusan Persetujuan Pimpinan tanggal <strong>{{ $ba->tanggal_approval ? $ba->tanggal_approval->format('d/m/Y') : now()->format('d/m/Y') }}</strong>, dengan ini menyatakan secara sah bahwa arsip sebagaimana tercantum dalam daftar di bawah ini telah disusutkan dan dilakukan pemusnahan total secara fisik dengan metode:
    </p>

    <div style="background: #f9fafb; border: 1px dashed #000; padding: 10px 16px; margin: 12px 0; text-align: center; font-weight: bold;">
        "{{ $ba->metode_pemusnahan }}"
    </div>

    <p style="margin: 8px 0; font-size: 11pt;">
        Pemusnahan dilakukan sehingga berkas tersebut telah hancur dan tidak dapat dikenali lagi wujud fisik maupun kandungan informasinya.
    </p>

    <!-- TABEL LAMPIRAN DAFTAR ARSIP YANG DIMUSNAHKAN -->
    <table class="lampiran">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 140px;">No. Registrasi Arsip</th>
                <th>Uraian / Judul Dokumen Arsip</th>
                <th style="width: 70px;">Klasifikasi</th>
                <th style="width: 90px;">Tgl Berkas</th>
                <th style="width: 110px;">Ket. Pelunasan / Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $ba->id_arsip }}</td>
                <td>
                    <strong>{{ $ba->arsip?->judul_arsip }}</strong>
                    @if ($ba->arsip?->referensi_nomor)
                        <br><small>No Kontrak: {{ $ba->arsip->referensi_nomor }}</small>
                    @endif
                </td>
                <td style="text-align: center;">{{ $ba->arsip?->kode_klasifikasi }}</td>
                <td style="text-align: center;">{{ $ba->arsip?->tanggal_arsip }}</td>
                <td style="text-align: center;">
                    @if ($ba->arsip?->pinjaman)
                        Lunas ({{ $ba->arsip->pinjaman->tanggal_lunas ?: 'Lunas' }})
                    @else
                        Inaktif Habis
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- KONSIDERANS KEABSAHAN HUKUM -->
    <p style="font-size: 10pt; line-height: 1.4; color: #222; margin-top: 10px;">
        <strong>Klausula Integritas Kearsipan:</strong> Panitia pemusnahan menjamin bahwa dokumen yang dimusnahkan telah bebas dari penahanan hukum (Legal Hold), bukan merupakan dokumen agunan vital yang wajib diserahkan ke nasabah, dan pinjaman terkait telah dinyatakan lunas tanpa sengketa hukum.
    </p>

    <p style="margin-top: 14px;">
        Demikian Berita Acara Pemusnahan Arsip ini dibuat dengan sebenar-benarnya dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- BLOK TANDA TANGAN 4 PIHAK RESMI -->
    <div class="signatures">
        <!-- Saksi I -->
        <div class="sig-block">
            Saksi I<br>(Bagian Hukum & Kepatuhan)
            <div class="sig-space"></div>
            <div class="sig-name">{{ $ba->saksi_1 ?: 'Jerry Pratama, S.H.' }}</div>
            <div class="sig-title">NIP. KSP-2020-0042</div>
        </div>

        <!-- Saksi II -->
        <div class="sig-block">
            Saksi II<br>(Satuan Pengawas Internal)
            <div class="sig-space"></div>
            <div class="sig-name">{{ $ba->saksi_2 ?: 'Gilang Ramadhan, S.E.' }}</div>
            <div class="sig-title">NIP. KSP-2019-0018</div>
        </div>

        <!-- Pelaksana Eksekutor -->
        <div class="sig-block" style="margin-top: 20px;">
            Pelaksana Pemusnahan<br>(Petugas Arsip Depo)
            <div class="sig-space"></div>
            <div class="sig-name">{{ $ba->petugas_eksekutor }}</div>
            <div class="sig-title">Petugas Pengelola Arsip</div>
        </div>

        <!-- Pimpinan Penyetuju -->
        <div class="sig-block" style="margin-top: 20px;">
            Mengetahui & Menyetujui,<br><strong>Kepala Bagian Kredit (Pimpinan)</strong>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $ba->approver?->name ?? 'Kepala Bagian Kredit' }}</div>
            <div class="sig-title">NIP. KSP-2018-0005</div>
        </div>
    </div>
</div>

</body>
</html>

