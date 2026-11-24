<?php

namespace Tests\Feature;

use App\Models\ArsipDokumen;
use App\Models\PeminjamanArsip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SirkulasiSprint4Test extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;

    protected User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->petugas = User::where('email', 'petugas@earsip.test')->first();
        $this->pimpinan = User::where('email', 'pimpinan@earsip.test')->first();
    }

    /*
    |--------------------------------------------------------------------------
    | SPRINT 4: MODUL SIRKULASI & APPROVAL PEMINJAMAN BERKAS JAMINAN
    |--------------------------------------------------------------------------
    */

    /**
     * Petugas dapat mengakses halaman form permohonan pinjam berkas jaminan.
     */
    public function test_petugas_dapat_mengakses_form_permohonan_pinjam(): void
    {
        $response = $this->actingAs($this->petugas)->get('/sirkulasi/create');

        $response->assertStatus(200);
        $response->assertSee('Formulir Permohonan Peminjaman Berkas');
    }

    /**
     * Peminjaman berkas baru: status awal 'menunggu' approval pimpinan,
     * dan fisik jaminan di brankas/depo MASIH berstatus 'tersedia' (belum boleh keluar).
     */
    public function test_pengajuan_pinjam_berkas_menunggu_approval_dan_fisik_belum_keluar(): void
    {
        // Berkas jaminan tersedia: ARC-KP.03/KSP-KRD/2026/0004
        $idArsip = 'ARC-KP.03/KSP-KRD/2026/0004';
        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->first();
        $this->assertEquals('tersedia', $arsip->status_ketersediaan);

        $response = $this->actingAs($this->petugas)->post('/sirkulasi', [
            'id_arsip' => $idArsip,
            'nama_peminjam' => 'Ahmad Fauzi',
            'divisi' => 'Legal & Kepatuhan',
            'keperluan' => 'Pemeriksaan keabsahan sertifikat untuk proses balik nama agunan',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali_rencana' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect('/sirkulasi');
        $response->assertSessionHas('success');

        // Pastikan record sirkulasi tersimpan dengan status 'menunggu'
        $peminjaman = PeminjamanArsip::where('id_arsip', $idArsip)
            ->where('nama_peminjam', 'Ahmad Fauzi')
            ->first();

        $this->assertNotNull($peminjaman);
        $this->assertEquals('menunggu', $peminjaman->status_approval);
        $this->assertNull($peminjaman->disetujui_oleh);

        // PENCEGAHAN RESIKO: Fisik berkas TIDAK BERUBAH sebelum disetujui pimpinan!
        $arsip->refresh();
        $this->assertEquals('tersedia', $arsip->status_ketersediaan);
    }

    /**
     * Validasi: Berkas yang sedang dipinjam (fisik tidak tersedia) TIDAK BISA diajukan pinjam lagi.
     */
    public function test_tidak_bisa_mengajukan_pinjam_untuk_berkas_yang_sedang_dipinjam(): void
    {
        // ARC-KP.03/KSP-KRD/2026/0001 di seeder berstatus 'dipinjam'
        $idArsip = 'ARC-KP.03/KSP-KRD/2026/0001';

        $response = $this->actingAs($this->petugas)->post('/sirkulasi', [
            'id_arsip' => $idArsip,
            'nama_peminjam' => 'Bambang Sudiro',
            'divisi' => 'Marketing',
            'keperluan' => 'Coba pinjam berkas yang sudah keluar',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali_rencana' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHas('error');

        // Tidak boleh ada permohonan baru untuk Bambang
        $this->assertDatabaseMissing('peminjaman_arsip', [
            'id_arsip' => $idArsip,
            'nama_peminjam' => 'Bambang Sudiro',
        ]);
    }

    /**
     * Otorisasi: Petugas TIDAK BERHAK mengakses modul approval pimpinan (403 Forbidden).
     */
    public function test_petugas_dilarang_mengakses_panel_approval(): void
    {
        $response = $this->actingAs($this->petugas)->get('/approval');
        $response->assertStatus(403);
    }

    /**
     * Pimpinan dapat mengakses panel approval dan melihat daftar permohonan yang menunggu.
     */
    public function test_pimpinan_dapat_melihat_daftar_approval(): void
    {
        $response = $this->actingAs($this->pimpinan)->get('/approval');

        $response->assertStatus(200);
        $response->assertSee('Panel Approval Berjenjang');
        // Skenario 2 di seed: permohonan Jerry Pratama (ARC-KP.03/KSP-KRD/2026/0002)
        $response->assertSee('Jerry Pratama');
    }

    /**
     * Alur Persetujuan (Approve): Pimpinan menyetujui peminjaman berkas jaminan.
     * Sistem mengupdate status approval menjadi 'disetujui' dan ketersediaan fisik berkas menjadi 'dipinjam'.
     */
    public function test_pimpinan_menyetujui_peminjaman_mengupdate_ketersediaan_fisik(): void
    {
        // Ambil permohonan Jerry yang berstatus menunggu
        $peminjaman = PeminjamanArsip::where('nama_peminjam', 'Jerry Pratama')->firstOrFail();
        $this->assertEquals('menunggu', $peminjaman->status_approval);

        $arsip = $peminjaman->arsip;
        $this->assertEquals('tersedia', $arsip->status_ketersediaan);

        $response = $this->actingAs($this->pimpinan)->post("/approval/{$peminjaman->id_pinjam}/setujui", [
            'catatan_approval' => 'Disetujui. Harap disimpan di brankas sementara kantor cabang.',
        ]);

        $response->assertRedirect('/approval');
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $this->assertEquals('disetujui', $peminjaman->status_approval);
        $this->assertEquals($this->pimpinan->id, $peminjaman->disetujui_oleh);
        $this->assertNotNull($peminjaman->tanggal_approval);
        $this->assertEquals('Disetujui. Harap disimpan di brankas sementara kantor cabang.', $peminjaman->catatan_approval);

        // KETERSEDIAAN FISIK BERUBAH: Berkas fisik kini sah tercatat keluar / dipinjam
        $arsip->refresh();
        $this->assertEquals('dipinjam', $arsip->status_ketersediaan);
    }

    /**
     * Alur Penolakan (Reject): Pimpinan menolak permohonan peminjaman dengan alasan wajib.
     * Berkas fisik tetap berstatus 'tersedia' di brankas/depo.
     */
    public function test_pimpinan_menolak_permohonan_pinjam_dengan_alasan(): void
    {
        $peminjaman = PeminjamanArsip::where('nama_peminjam', 'Jerry Pratama')->firstOrFail();

        $response = $this->actingAs($this->pimpinan)->post("/approval/{$peminjaman->id_pinjam}/tolak", [
            'catatan_approval' => 'Ditolak: Keperluan verifikasi cukup menggunakan salinan scan PDF yang terenkripsi di sistem.',
        ]);

        $response->assertRedirect('/approval');
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $this->assertEquals('ditolak', $peminjaman->status_approval);
        $this->assertEquals($this->pimpinan->id, $peminjaman->disetujui_oleh);

        // Berkas fisik tetap aman tersedia di depo
        $peminjaman->arsip->refresh();
        $this->assertEquals('tersedia', $peminjaman->arsip->status_ketersediaan);
    }

    /**
     * Deteksi Keterlambatan Otomatis (Overdue Detection):
     * Peminjaman yang melewati tanggal kembali rencana otomatis terdeteksi terlambat.
     */
    public function test_deteksi_keterlambatan_otomatis(): void
    {
        // Di seeder: ARC-KP.03/KSP-KRD/2026/0001 dipinjam Rudi Hartono dengan rencana kembali 2025-10-27
        $peminjaman = PeminjamanArsip::where('nama_peminjam', 'Rudi Hartono')->firstOrFail();

        $this->assertTrue($peminjaman->isTerlambat());
        $this->assertGreaterThan(0, $peminjaman->hari_terlambat);

        // Akses index sirkulasi akan otomatis mendeteksi dan memperbarui record
        $response = $this->actingAs($this->petugas)->get('/sirkulasi?status=terlambat');
        $response->assertStatus(200);
        $response->assertSee('Rudi Hartono');
        $response->assertSee('Terlambat');
    }

    /**
     * Alur Pengembalian Fisik Berkas Jaminan (Return / Check-in):
     * Petugas memproses pengembalian, mencatat tanggal aktual dan kondisi berkas,
     * serta memulihkan ketersediaan fisik arsip menjadi 'tersedia'.
     */
    public function test_proses_pengembalian_berkas_memulihkan_ketersediaan_fisik(): void
    {
        // Gunakan peminjaman Rudi Hartono yang statusnya disetujui & dipinjam
        $peminjaman = PeminjamanArsip::where('nama_peminjam', 'Rudi Hartono')->firstOrFail();
        $arsip = $peminjaman->arsip;
        $this->assertEquals('dipinjam', $arsip->status_ketersediaan);

        $response = $this->actingAs($this->petugas)->post("/sirkulasi/{$peminjaman->id_pinjam}/kembali", [
            'tanggal_kembali_aktual' => now()->toDateString(),
            'catatan_pengembalian' => 'Sertifikat SHM diterima kembali utuh, tidak ada kerusakan fisik, dan disimpan di rak BK-01.',
        ]);

        $response->assertRedirect('/sirkulasi');
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $this->assertEquals('kembali', $peminjaman->status_sirkulasi);
        $this->assertNotNull($peminjaman->tanggal_kembali_aktual);
        $this->assertEquals($this->petugas->id, $peminjaman->petugas_pengembalian);

        // KETERSEDIAAN FISIK PULIH: Dokumen kembali berstatus 'tersedia' di rak/depo
        $arsip->refresh();
        $this->assertEquals('tersedia', $arsip->status_ketersediaan);
    }

    /**
     * Log Riwayat Sirkulasi & Audit Trail:
     * Detail kartu arsip menampilkan rekam jejak lengkap riwayat sirkulasi peminjaman.
     */
    public function test_kartu_arsip_menampilkan_log_riwayat_sirkulasi(): void
    {
        // ARC-KP.03/KSP-KRD/2026/0003 di seeder memiliki riwayat peminjaman Gilang Ramadhan
        $response = $this->actingAs($this->petugas)->get('/arsip/ARC-KP.03/KSP-KRD/2026/0003');

        $response->assertStatus(200);
        $response->assertSee('Buku Kendali');
        $response->assertSee('Riwayat Sirkulasi Berkas');
        $response->assertSee('Gilang Ramadhan');
        $response->assertSee('Telah Kembali');
    }
}
