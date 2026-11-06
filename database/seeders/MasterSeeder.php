<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('klasifikasi_arsip')->insert([
            ['kode_klasifikasi' => 'KS.01', 'nama_klasifikasi' => 'Kearsipan Simpanan Pokok & Wajib', 'retensi_aktif' => 3, 'retensi_inaktif' => 5, 'keterangan_akhir' => 'permanen'],
            ['kode_klasifikasi' => 'KS.02', 'nama_klasifikasi' => 'Rekening Koran & Riwayat Saldo', 'retensi_aktif' => 2, 'retensi_inaktif' => 3, 'keterangan_akhir' => 'musnah'],
            ['kode_klasifikasi' => 'KP.01', 'nama_klasifikasi' => 'Formulir & Berkas Pengajuan Pinjaman', 'retensi_aktif' => 2, 'retensi_inaktif' => 3, 'keterangan_akhir' => 'musnah'],
            ['kode_klasifikasi' => 'KP.02', 'nama_klasifikasi' => 'Perjanjian Kredit & Surat Pengakuan Hutang', 'retensi_aktif' => 2, 'retensi_inaktif' => 5, 'keterangan_akhir' => 'musnah'],
            ['kode_klasifikasi' => 'KP.03', 'nama_klasifikasi' => 'Bukti Agunan / Jaminan Vital (Sertifikat/BPKB)', 'retensi_aktif' => 1, 'retensi_inaktif' => 10, 'keterangan_akhir' => 'diserahkan'],
            ['kode_klasifikasi' => 'KU.01', 'nama_klasifikasi' => 'Bukti Kas Masuk / Keluar (Kuitansi/Slip)', 'retensi_aktif' => 1, 'retensi_inaktif' => 2, 'keterangan_akhir' => 'musnah'],
            ['kode_klasifikasi' => 'KE.01', 'nama_klasifikasi' => 'Akta Pendirian, AD/ART & Notulen RAT', 'retensi_aktif' => 0, 'retensi_inaktif' => 0, 'keterangan_akhir' => 'permanen'],
        ]);

        DB::table('klasifikasi_arsip')->where('kode_klasifikasi', 'KP.03')->update(['jenis_berkas' => 'vital']);
        DB::table('klasifikasi_arsip')->whereIn('kode_klasifikasi', ['KP.02', 'KP.03'])->update(['retensi_dihitung_dari' => 'tanggal_lunas']);

        DB::table('lokasi_arsip')->insert([
            ['kode_lokasi' => 'BK-01.RF-A.OD-01', 'gedung' => 'Brankas Khazanah', 'nomor_rak' => 'BK-01', 'ambalan' => 'RF-A', 'nomor_boks' => 'OD-01'],
            ['kode_lokasi' => 'BK-01.RF-A.OD-02', 'gedung' => 'Brankas Khazanah', 'nomor_rak' => 'BK-01', 'ambalan' => 'RF-A', 'nomor_boks' => 'OD-02'],
            ['kode_lokasi' => 'RK-02.RF-A.OD-04', 'gedung' => 'Gedung Depo Utama', 'nomor_rak' => 'RK-02', 'ambalan' => 'RF-A', 'nomor_boks' => 'OD-04'],
            ['kode_lokasi' => 'RK-02.RF-B.OD-01', 'gedung' => 'Gedung Depo Utama', 'nomor_rak' => 'RK-02', 'ambalan' => 'RF-B', 'nomor_boks' => 'OD-01'],
        ]);
    }
}
