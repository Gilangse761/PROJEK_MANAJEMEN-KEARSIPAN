<?php

namespace Database\Seeders;

use App\Services\DigitalArchiveService;
use App\Services\JraService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('anggota')->insert([
            ['id_anggota' => 'ANG-202601-0001', 'nik' => '3216010101900001', 'nama_lengkap' => 'Budi Santoso', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0002', 'nik' => '3216010101900002', 'nama_lengkap' => 'Siti Rahayu', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0003', 'nik' => '3216010101900003', 'nama_lengkap' => 'Agus Wijaya', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0004', 'nik' => '3216010101900004', 'nama_lengkap' => 'Dewi Lestari', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0005', 'nik' => '3216010101900005', 'nama_lengkap' => 'Hendra Gunawan', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0006', 'nik' => '3216010101900006', 'nama_lengkap' => 'Rina Marlina', 'status_keanggotaan' => 'aktif'],
            ['id_anggota' => 'ANG-202601-0007', 'nik' => '3216010101900007', 'nama_lengkap' => 'Joko Prasetyo', 'status_keanggotaan' => 'keluar'],
            ['id_anggota' => 'ANG-202601-0008', 'nik' => '3216010101900008', 'nama_lengkap' => 'Maya Sari', 'status_keanggotaan' => 'aktif'],
        ]);

        DB::table('transaksi_pinjaman')->insert([
            ['no_rekening_pinjaman' => 'KRD-2024-0001', 'id_anggota' => 'ANG-202601-0001', 'nominal_pinjaman' => 150000000, 'tanggal_akad' => '2024-03-01', 'tanggal_jatuh_tempo' => '2026-03-01', 'status_pinjaman' => 'macet', 'tanggal_lunas' => null],
            ['no_rekening_pinjaman' => 'KRD-2024-0002', 'id_anggota' => 'ANG-202601-0005', 'nominal_pinjaman' => 75000000, 'tanggal_akad' => '2024-05-10', 'tanggal_jatuh_tempo' => '2026-05-10', 'status_pinjaman' => 'macet', 'tanggal_lunas' => null],
            ['no_rekening_pinjaman' => 'KRD-2025-0003', 'id_anggota' => 'ANG-202601-0002', 'nominal_pinjaman' => 50000000, 'tanggal_akad' => '2025-02-15', 'tanggal_jatuh_tempo' => '2027-02-15', 'status_pinjaman' => 'berjalan', 'tanggal_lunas' => null],
            ['no_rekening_pinjaman' => 'KRD-2025-0004', 'id_anggota' => 'ANG-202601-0004', 'nominal_pinjaman' => 25000000, 'tanggal_akad' => '2025-06-01', 'tanggal_jatuh_tempo' => '2026-12-01', 'status_pinjaman' => 'berjalan', 'tanggal_lunas' => null],
            ['no_rekening_pinjaman' => 'KRD-2023-0005', 'id_anggota' => 'ANG-202601-0003', 'nominal_pinjaman' => 100000000, 'tanggal_akad' => '2023-01-10', 'tanggal_jatuh_tempo' => '2025-01-10', 'status_pinjaman' => 'lunas', 'tanggal_lunas' => '2025-01-10'],
            ['no_rekening_pinjaman' => 'KRD-2023-0006', 'id_anggota' => 'ANG-202601-0006', 'nominal_pinjaman' => 30000000, 'tanggal_akad' => '2023-08-01', 'tanggal_jatuh_tempo' => '2025-08-01', 'status_pinjaman' => 'lunas', 'tanggal_lunas' => '2025-07-28'],
            ['no_rekening_pinjaman' => 'KRD-2022-0007', 'id_anggota' => 'ANG-202601-0007', 'nominal_pinjaman' => 20000000, 'tanggal_akad' => '2022-04-01', 'tanggal_jatuh_tempo' => '2024-04-01', 'status_pinjaman' => 'lunas', 'tanggal_lunas' => '2024-03-30'],
        ]);

        // Kalkulasi JRA otomatis di Sprint 3
        $jraService = app(JraService::class);
        $digitalService = app(DigitalArchiveService::class);

        // id_lokasi: 1-2 = brankas, 3-4 = depo.
        $arsip = [
            ['ARC-KP.03/KSP-KRD/2026/0001', 'KP.03', 1, 'KRD-2024-0001', 'Sertifikat SHM An. Budi Santoso', '2019-06-15'],
            ['ARC-KP.03/KSP-KRD/2026/0002', 'KP.03', 1, 'KRD-2024-0002', 'BPKB Mobil An. Hendra Gunawan', '2022-11-20'],
            ['ARC-KP.03/KSP-KRD/2026/0003', 'KP.03', 1, 'KRD-2025-0003', 'Sertifikat SHM An. Siti Rahayu', '2018-03-05'],
            ['ARC-KP.03/KSP-KRD/2026/0004', 'KP.03', 2, 'KRD-2023-0005', 'Sertifikat SHM An. Agus Wijaya', '2017-09-09'],
            ['ARC-KP.02/KSP-KRD/2026/0005', 'KP.02', 3, 'KRD-2024-0001', 'Perjanjian Kredit An. Budi Santoso', '2024-03-01'],
            ['ARC-KP.02/KSP-KRD/2026/0006', 'KP.02', 3, 'KRD-2023-0006', 'Perjanjian Kredit An. Rina Marlina', '2023-08-01'],
            ['ARC-KP.01/KSP-KRD/2026/0007', 'KP.01', 4, 'KRD-2025-0004', 'Formulir Pengajuan Pinjaman An. Dewi Lestari', '2025-05-20'],
            ['ARC-KS.01/KSP-SMP/2026/0008', 'KS.01', 4, 'SMP-0008', 'Buku Simpanan Pokok & Wajib An. Maya Sari', '2020-01-15'],
            ['ARC-KS.02/KSP-SMP/2026/0009', 'KS.02', 4, 'SMP-0002', 'Rekening Koran An. Siti Rahayu (Semester I 2026)', '2026-07-01'],
            ['ARC-KU.01/KSP-KEU/2026/0010', 'KU.01', 4, null, 'Kuitansi Kas Keluar Oktober 2026', '2026-10-31'],
        ];

        foreach ($arsip as $a) {
            $jra = $jraService->hitung($a[1], $a[5], $a[3]);

            $filePath = null;
            $media = 'kertas';

            // Siapkan berkas pindaian scan PDF contoh untuk arsip jaminan vital Budi dan Agus
            if (in_array($a[0], ['ARC-KP.03/KSP-KRD/2026/0001', 'ARC-KP.03/KSP-KRD/2026/0004'])) {
                $filePath = $digitalService->storeDummyEncrypted($a[4], $a[0]);
                $media = 'hybrid';
            }

            DB::table('arsip_dokumen')->insert([
                'id_arsip' => $a[0],
                'kode_klasifikasi' => $a[1],
                'id_lokasi' => $a[2],
                'referensi_nomor' => $a[3],
                'judul_arsip' => $a[4],
                'tanggal_arsip' => $a[5],
                'media_arsip' => $media,
                'file_digital_path' => $filePath,
                'status_arsip' => $jra['status_arsip'],
                'jadwal_retensi_inaktif' => $jra['jadwal_retensi_inaktif'],
                'jadwal_penyusutan' => $jra['jadwal_penyusutan'],
            ]);
        }

        // Kasus skenario: sertifikat Budi dipinjam setahun lalu dan belum kembali
        DB::table('peminjaman_arsip')->insert([
            ['id_arsip' => 'ARC-KP.03/KSP-KRD/2026/0001', 'nama_peminjam' => 'Rudi Hartono', 'divisi' => 'Marketing', 'keperluan' => 'Verifikasi ulang data agunan', 'tanggal_pinjam' => '2025-10-20', 'tanggal_kembali_rencana' => '2025-10-27', 'status_sirkulasi' => 'terlambat'],
        ]);
    }
}
