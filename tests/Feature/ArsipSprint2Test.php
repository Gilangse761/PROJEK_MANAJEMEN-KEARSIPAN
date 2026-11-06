<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArsipSprint2Test extends TestCase
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

    /**
     * 1. Registrasi KP.03, pilih lokasi, referensi KRD-2025-0004, isi judul dan tanggal
     * -> Muncul di /arsip dengan kode ARC-KP.03/KSP-KRD/2026/0005
     */
    public function test_registrasi_kp03_muncul_dengan_kode_0005(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0004',
            'judul_arsip' => 'Sertifikat Agunan Pinjaman Dewi Lestari',
            'tanggal_arsip' => '2026-06-01',
        ]);

        $response->assertRedirect('/arsip');
        $this->assertDatabaseHas('arsip_dokumen', [
            'id_arsip' => 'ARC-KP.03/KSP-KRD/2026/0005',
            'kode_klasifikasi' => 'KP.03',
            'referensi_nomor' => 'KRD-2025-0004',
        ]);

        $indexResponse = $this->actingAs($this->petugas)->get('/arsip');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('ARC-KP.03/KSP-KRD/2026/0005');
    }

    /**
     * 2. Registrasi KP.03 lagi -> Kodenya .../0006, urut tanpa bentrok
     */
    public function test_registrasi_kp03_lagi_urut_0006(): void
    {
        // Pendaftaran 1 (menghasilkan 0005)
        $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0004',
            'judul_arsip' => 'Sertifikat Agunan Pinjaman Dewi',
            'tanggal_arsip' => '2026-06-01',
        ]);

        // Pendaftaran 2 (harus 0006)
        $response2 = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0003',
            'judul_arsip' => 'Sertifikat Agunan Pinjaman Tambahan',
            'tanggal_arsip' => '2026-06-02',
        ]);

        $response2->assertRedirect('/arsip');
        $this->assertDatabaseHas('arsip_dokumen', [
            'id_arsip' => 'ARC-KP.03/KSP-KRD/2026/0006',
        ]);

        $indexResponse = $this->actingAs($this->petugas)->get('/arsip');
        $indexResponse->assertSee('ARC-KP.03/KSP-KRD/2026/0006');
    }

    /**
     * 3. Registrasi KP.03 dengan referensi dikosongkan -> Ditolak, pesan wajib isi
     */
    public function test_registrasi_kp03_tanpa_referensi_ditolak(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => '',
            'judul_arsip' => 'Sertifikat Agunan Tanpa Referensi',
            'tanggal_arsip' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors([
            'referensi_nomor' => 'Berkas pinjaman (KP.*) wajib diisi nomor rekening pinjamannya.',
        ]);
    }

    /**
     * 4. Registrasi KP.03 dengan referensi KRD-9999-0000 -> Ditolak, nomor tidak ditemukan
     */
    public function test_registrasi_kp03_referensi_tidak_ada_ditolak(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-9999-0000',
            'judul_arsip' => 'Sertifikat Agunan Nomor Fiktif',
            'tanggal_arsip' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors([
            'referensi_nomor' => 'Nomor rekening pinjaman tidak ditemukan di data transaksi pinjaman.',
        ]);
    }

    /**
     * 5. Registrasi KS.01 dengan referensi SMP-0008 -> Berhasil, kode ARC-KS.01/KSP-SMP/...
     */
    public function test_registrasi_ks01_dengan_referensi_smp(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KS.01',
            'id_lokasi' => 4,
            'referensi_nomor' => 'SMP-0008',
            'judul_arsip' => 'Buku Simpanan Maya Sari Lanjutan',
            'tanggal_arsip' => '2026-06-01',
        ]);

        $response->assertRedirect('/arsip');
        $this->assertDatabaseHas('arsip_dokumen', [
            'id_arsip' => 'ARC-KS.01/KSP-SMP/2026/0009',
            'referensi_nomor' => 'SMP-0008',
        ]);

        $indexResponse = $this->actingAs($this->petugas)->get('/arsip');
        $indexResponse->assertSee('ARC-KS.01/KSP-SMP/2026/0009');
    }

    /**
     * 6. Registrasi KU.01 tanpa referensi -> Berhasil
     */
    public function test_registrasi_ku01_tanpa_referensi_berhasil(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KU.01',
            'id_lokasi' => 4,
            'referensi_nomor' => null,
            'judul_arsip' => 'Kuitansi Kas Operasional',
            'tanggal_arsip' => '2026-06-01',
        ]);

        $response->assertRedirect('/arsip');
        $this->assertDatabaseHas('arsip_dokumen', [
            'id_arsip' => 'ARC-KU.01/KSP-KEU/2026/0011',
            'referensi_nomor' => null,
        ]);
    }

    /**
     * 7. Tanggal berkas di masa depan -> Ditolak
     */
    public function test_tanggal_berkas_di_masa_depan_ditolak(): void
    {
        $besok = now()->addDay()->toDateString();

        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KU.01',
            'id_lokasi' => 4,
            'judul_arsip' => 'Kuitansi Masa Depan',
            'tanggal_arsip' => $besok,
        ]);

        $response->assertSessionHasErrors([
            'tanggal_arsip' => 'Tanggal berkas tidak boleh di masa depan.',
        ]);
    }

    /**
     * 8. Login pimpinan, buka /arsip/create -> 403
     */
    public function test_pimpinan_buka_arsip_create_403(): void
    {
        $response = $this->actingAs($this->pimpinan)->get('/arsip/create');

        $response->assertStatus(403);
    }
}
