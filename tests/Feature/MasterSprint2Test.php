<?php

namespace Tests\Feature;

use App\Models\KlasifikasiArsip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSprint2Test extends TestCase
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
     * 1. Login petugas, buka /klasifikasi -> 7 baris, KP.03 berjenis vital
     */
    public function test_petugas_can_view_klasifikasi_list_with_7_rows_and_kp03_vital(): void
    {
        $response = $this->actingAs($this->petugas)->get('/klasifikasi');

        $response->assertStatus(200);
        $response->assertSee('Master Klasifikasi Dokumen');
        $this->assertEquals(7, KlasifikasiArsip::count());
        $response->assertSee('KP.03');
        $response->assertSee('vital');

        $kp03 = KlasifikasiArsip::find('KP.03');
        $this->assertNotNull($kp03);
        $this->assertEquals('vital', $kp03->jenis_berkas);
    }

    /**
     * 2. Tambah klasifikasi KX.01, edit, lalu hapus -> Berhasil semua
     */
    public function test_crud_klasifikasi_kx01_success(): void
    {
        // Create
        $storeResponse = $this->actingAs($this->petugas)->post('/klasifikasi', [
            'kode_klasifikasi' => 'KX.01',
            'nama_klasifikasi' => 'Klasifikasi Percobaan',
            'jenis_berkas' => 'non_vital',
            'retensi_aktif' => 1,
            'retensi_inaktif' => 2,
            'retensi_dihitung_dari' => 'tanggal_arsip',
            'keterangan_akhir' => 'musnah',
        ]);

        $storeResponse->assertRedirect('/klasifikasi');
        $this->assertDatabaseHas('klasifikasi_arsip', ['kode_klasifikasi' => 'KX.01']);

        // Edit / Update
        $updateResponse = $this->actingAs($this->petugas)->put('/klasifikasi/KX.01', [
            'nama_klasifikasi' => 'Klasifikasi Percobaan Diedit',
            'jenis_berkas' => 'vital',
            'retensi_aktif' => 2,
            'retensi_inaktif' => 3,
            'retensi_dihitung_dari' => 'tanggal_lunas',
            'keterangan_akhir' => 'permanen',
        ]);

        $updateResponse->assertRedirect('/klasifikasi');
        $this->assertDatabaseHas('klasifikasi_arsip', [
            'kode_klasifikasi' => 'KX.01',
            'nama_klasifikasi' => 'Klasifikasi Percobaan Diedit',
            'jenis_berkas' => 'vital',
        ]);

        // Delete
        $deleteResponse = $this->actingAs($this->petugas)->delete('/klasifikasi/KX.01');
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('klasifikasi_arsip', ['kode_klasifikasi' => 'KX.01']);
    }

    /**
     * 3. Tambah kp.03 atau kode yang sudah ada -> Ditolak validasi
     */
    public function test_add_existing_code_or_lowercase_is_rejected_by_validation(): void
    {
        // Lowercase
        $response1 = $this->actingAs($this->petugas)->post('/klasifikasi', [
            'kode_klasifikasi' => 'kp.03',
            'nama_klasifikasi' => 'Percobaan Huruf Kecil',
            'jenis_berkas' => 'non_vital',
            'retensi_aktif' => 1,
            'retensi_inaktif' => 2,
            'retensi_dihitung_dari' => 'tanggal_arsip',
            'keterangan_akhir' => 'musnah',
        ]);
        $response1->assertSessionHasErrors('kode_klasifikasi');

        // Existing uppercase code
        $response2 = $this->actingAs($this->petugas)->post('/klasifikasi', [
            'kode_klasifikasi' => 'KP.03',
            'nama_klasifikasi' => 'Duplikat KP.03',
            'jenis_berkas' => 'vital',
            'retensi_aktif' => 1,
            'retensi_inaktif' => 2,
            'retensi_dihitung_dari' => 'tanggal_arsip',
            'keterangan_akhir' => 'musnah',
        ]);
        $response2->assertSessionHasErrors('kode_klasifikasi');
    }

    /**
     * 4. Hapus KP.03 -> Ditolak karena masih dipakai arsip
     */
    public function test_delete_kp03_is_rejected_because_used_by_arsip(): void
    {
        $response = $this->actingAs($this->petugas)->delete('/klasifikasi/KP.03');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Klasifikasi masih dipakai arsip, tidak bisa dihapus.');
        $this->assertDatabaseHas('klasifikasi_arsip', ['kode_klasifikasi' => 'KP.03']);
    }

    /**
     * 5. Tambah lokasi RK-03 / RF-A / OD-01 -> Kode RK-03.RF-A.OD-01 terbentuk otomatis
     */
    public function test_add_lokasi_automatically_generates_kode_lokasi(): void
    {
        $response = $this->actingAs($this->petugas)->post('/lokasi', [
            'gedung' => 'Gedung Depo Utama',
            'nomor_rak' => 'RK-03',
            'ambalan' => 'RF-A',
            'nomor_boks' => 'OD-01',
        ]);

        $response->assertRedirect('/lokasi');
        $this->assertDatabaseHas('lokasi_arsip', [
            'nomor_rak' => 'RK-03',
            'ambalan' => 'RF-A',
            'nomor_boks' => 'OD-01',
            'kode_lokasi' => 'RK-03.RF-A.OD-01',
        ]);
    }

    /**
     * 6. Tambah lokasi yang sama dua kali -> Ditolak, "sudah terdaftar"
     */
    public function test_add_duplicate_lokasi_rejected_with_sudah_terdaftar(): void
    {
        // Pertama kali tambah
        $this->actingAs($this->petugas)->post('/lokasi', [
            'gedung' => 'Gedung Depo Utama',
            'nomor_rak' => 'RK-03',
            'ambalan' => 'RF-A',
            'nomor_boks' => 'OD-01',
        ]);

        // Kedua kali tambah dengan kombinasi yang sama
        $response = $this->actingAs($this->petugas)->post('/lokasi', [
            'gedung' => 'Gedung Depo Utama',
            'nomor_rak' => 'RK-03',
            'ambalan' => 'RF-A',
            'nomor_boks' => 'OD-01',
        ]);

        $response->assertSessionHasErrors([
            'nomor_boks' => 'Lokasi RK-03.RF-A.OD-01 sudah terdaftar.',
        ]);
    }

    /**
     * 7. Login pimpinan, buka /klasifikasi -> Bisa dilihat, tanpa tombol tambah/edit/hapus
     */
    public function test_pimpinan_can_view_klasifikasi_without_action_buttons(): void
    {
        $response = $this->actingAs($this->pimpinan)->get('/klasifikasi');

        $response->assertStatus(200);
        $response->assertSee('Master Klasifikasi Dokumen');
        $response->assertSee('KP.03');

        // Tidak boleh ada tombol tambah
        $response->assertDontSee('+ Tambah');
        $response->assertDontSee(route('klasifikasi.create'));

        // Tidak boleh ada aksi edit/hapus
        $response->assertDontSee('klasifikasi/KP.03/edit');
        $response->assertDontSee('Hapus klasifikasi ini?');
        $response->assertDontSee('<th>Aksi</th>', false);
    }

    /**
     * 8. Pimpinan buka /klasifikasi/create -> 403
     */
    public function test_pimpinan_access_klasifikasi_create_returns_403(): void
    {
        $response = $this->actingAs($this->pimpinan)->get('/klasifikasi/create');

        $response->assertStatus(403);
    }
}
