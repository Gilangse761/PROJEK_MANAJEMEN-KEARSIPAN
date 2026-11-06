<?php

namespace Tests\Feature;

use App\Models\ArsipDokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArsipSprint3Test extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;

    protected User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed();

        $this->petugas = User::where('email', 'petugas@earsip.test')->first();
        $this->pimpinan = User::where('email', 'pimpinan@earsip.test')->first();
    }

    /*
    |--------------------------------------------------------------------------
    | ARSIP-008: Kalkulasi Otomatis JRA & Status Lunas
    |--------------------------------------------------------------------------
    */

    /**
     * Berkas pinjaman dengan status lunas: JRA dihitung dari tanggal_lunas.
     * KRD-2023-0005 lunas pada 2025-01-10.
     * KP.03: retensi aktif 1 th -> inaktif 2026-01-10, retensi inaktif 10 th -> penyusutan 2036-01-10.
     */
    public function test_kalkulasi_jra_otomatis_untuk_pinjaman_lunas(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2023-0005', // pinjaman lunas: 2025-01-10
            'judul_arsip' => 'Sertifikat Agunan Pinjaman Lunas Agus',
            'tanggal_arsip' => '2023-01-10',
        ]);

        $response->assertRedirect('/arsip');

        $arsip = ArsipDokumen::where('referensi_nomor', 'KRD-2023-0005')
            ->where('judul_arsip', 'Sertifikat Agunan Pinjaman Lunas Agus')
            ->first();

        $this->assertNotNull($arsip);
        $this->assertEquals('2026-01-10', $arsip->jadwal_retensi_inaktif);
        $this->assertEquals('2036-01-10', $arsip->jadwal_penyusutan);
    }

    /**
     * Berkas pinjaman kredit macet / berjalan: JRA belum berjalan (null).
     * Berkas tetap aktif dan terlindungi dari usul musnah.
     */
    public function test_kalkulasi_jra_untuk_pinjaman_macet_tidak_dimusnahkan(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2024-0001', // status: macet
            'judul_arsip' => 'Sertifikat Agunan Pinjaman Macet Budi Santoso',
            'tanggal_arsip' => '2024-03-01',
        ]);

        $response->assertRedirect('/arsip');

        $arsip = ArsipDokumen::where('referensi_nomor', 'KRD-2024-0001')
            ->where('judul_arsip', 'Sertifikat Agunan Pinjaman Macet Budi Santoso')
            ->first();

        $this->assertNotNull($arsip);
        $this->assertNull($arsip->jadwal_retensi_inaktif);
        $this->assertNull($arsip->jadwal_penyusutan);
        $this->assertEquals('aktif', $arsip->status_arsip);
    }

    /**
     * Berkas umum non-pinjaman (retensi_dihitung_dari = tanggal_arsip):
     * Dihitung dari tanggal berkas.
     * KU.01: retensi aktif 1 th, inaktif 2 th.
     */
    public function test_kalkulasi_jra_dari_tanggal_arsip(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KU.01',
            'id_lokasi' => 4,
            'referensi_nomor' => null,
            'judul_arsip' => 'Kuitansi Pengadaan Komputer Kantor',
            'tanggal_arsip' => '2026-05-10',
        ]);

        $response->assertRedirect('/arsip');

        $arsip = ArsipDokumen::where('judul_arsip', 'Kuitansi Pengadaan Komputer Kantor')->first();
        $this->assertNotNull($arsip);
        $this->assertEquals('2027-05-10', $arsip->jadwal_retensi_inaktif);
        $this->assertEquals('2029-05-10', $arsip->jadwal_penyusutan);
    }

    /**
     * Arsip permanen tanpa batas retensi (KE.01: retensi aktif 0, inaktif 0).
     */
    public function test_kalkulasi_jra_arsip_permanen(): void
    {
        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KE.01',
            'id_lokasi' => 4,
            'referensi_nomor' => null,
            'judul_arsip' => 'Notulen Rapat Anggota Tahunan KSP 2026',
            'tanggal_arsip' => '2026-02-01',
        ]);

        $response->assertRedirect('/arsip');

        $arsip = ArsipDokumen::where('judul_arsip', 'Notulen Rapat Anggota Tahunan KSP 2026')->first();
        $this->assertNotNull($arsip);
        $this->assertNull($arsip->jadwal_retensi_inaktif);
        $this->assertNull($arsip->jadwal_penyusutan);
        $this->assertEquals('permanen', $arsip->status_arsip);
    }

    /*
    |--------------------------------------------------------------------------
    | ARSIP-009: Upload Berkas PDF, Validasi & Penyimpanan Terenkripsi
    |--------------------------------------------------------------------------
    */

    /**
     * Upload berkas pindaian scan PDF saat registrasi:
     * File tersimpan aman terenkripsi di server, media berubah jadi hybrid.
     */
    public function test_upload_pdf_saat_registrasi_tersimpan_terenkripsi(): void
    {
        $file = UploadedFile::fake()->create('sertifikat_tanah.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0003',
            'judul_arsip' => 'Sertifikat Hak Milik No 9988 An Siti Rahayu',
            'tanggal_arsip' => '2026-01-15',
            'media_arsip' => 'kertas',
            'file_digital' => $file,
        ]);

        $response->assertRedirect('/arsip');

        $arsip = ArsipDokumen::where('judul_arsip', 'Sertifikat Hak Milik No 9988 An Siti Rahayu')->first();
        $this->assertNotNull($arsip);
        $this->assertNotNull($arsip->file_digital_path);
        $this->assertEquals('hybrid', $arsip->media_arsip);

        // Verifikasi fisik file tersimpan di storage
        Storage::disk('local')->assertExists($arsip->file_digital_path);

        // Verifikasi bahwa isi file di disk berstatus terenkripsi (bukan plain text)
        $rawStored = Storage::disk('local')->get($arsip->file_digital_path);
        $this->assertNotEquals('%PDF', substr($rawStored, 0, 4), 'File di storage harus terenkripsi AES-256');

        // Verifikasi bahwa dekripsi menghasilkan isi asli
        $decrypted = Crypt::decrypt($rawStored);
        $this->assertNotNull($decrypted);
    }

    /**
     * Validasi gagal jika berkas bukan berupa PDF (misal .docx / .exe / .jpg).
     */
    public function test_upload_bukan_pdf_ditolak(): void
    {
        $file = UploadedFile::fake()->create('dokumen_palsu.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0003',
            'judul_arsip' => 'File Word Salah Format',
            'tanggal_arsip' => '2026-01-15',
            'file_digital' => $file,
        ]);

        $response->assertSessionHasErrors([
            'file_digital' => 'Berkas pindaian harus berupa dokumen berformat PDF.',
        ]);
    }

    /**
     * Validasi gagal jika ukuran file melebihi 10 MB.
     */
    public function test_upload_pdf_melebihi_10mb_ditolak(): void
    {
        $file = UploadedFile::fake()->create('berkas_raksasa.pdf', 11000, 'application/pdf');

        $response = $this->actingAs($this->petugas)->post('/arsip', [
            'kode_klasifikasi' => 'KP.03',
            'id_lokasi' => 1,
            'referensi_nomor' => 'KRD-2025-0003',
            'judul_arsip' => 'File Terlalu Besar',
            'tanggal_arsip' => '2026-01-15',
            'file_digital' => $file,
        ]);

        $response->assertSessionHasErrors([
            'file_digital' => 'Ukuran berkas scan PDF tidak boleh melebihi 10 MB.',
        ]);
    }

    /**
     * Petugas dapat mengunggah file scan PDF pada arsip yang sudah ada.
     */
    public function test_petugas_dapat_mengunggah_scan_pdf_pada_detail_arsip(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0002';
        $file = UploadedFile::fake()->create('scan_bpkb_mobil.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->petugas)->post('/arsip/upload/'.$targetId, [
            'file_digital' => $file,
        ]);

        $response->assertRedirect('/arsip/'.$targetId);

        $arsip = ArsipDokumen::find($targetId);
        $this->assertNotNull($arsip->file_digital_path);
        Storage::disk('local')->assertExists($arsip->file_digital_path);
    }

    /**
     * Pimpinan dilarang mengunggah file (403 forbidden).
     */
    public function test_pimpinan_dilarang_mengunggah_file(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0002';
        $file = UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->pimpinan)->post('/arsip/upload/'.$targetId, [
            'file_digital' => $file,
        ]);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | ARSIP-010: Temu Balik Arsip (Pencarian Multi-Parameter)
    |--------------------------------------------------------------------------
    */

    /**
     * Pencarian berdasarkan nomor / kode arsip spesifik.
     */
    public function test_pencarian_berdasarkan_nomor_arsip(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?id_arsip=0008');

        $response->assertStatus(200);
        $response->assertSee('ARC-KS.01/KSP-SMP/2026/0008');
        $response->assertDontSee('ARC-KP.03/KSP-KRD/2026/0001');
    }

    /**
     * Pencarian berdasarkan nama anggota nasabah pinjaman.
     */
    public function test_pencarian_berdasarkan_nama_anggota(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?nama_anggota=Budi+Santoso');

        $response->assertStatus(200);
        $response->assertSee('Sertifikat SHM An. Budi Santoso');
        $response->assertSee('Perjanjian Kredit An. Budi Santoso');
        $response->assertDontSee('BPKB Mobil An. Hendra Gunawan');
    }

    /**
     * Pencarian berdasarkan nomor rekening pinjaman / kontrak.
     */
    public function test_pencarian_berdasarkan_nomor_rekening_pinjaman(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?no_rekening=KRD-2023-0005');

        $response->assertStatus(200);
        $response->assertSee('Sertifikat SHM An. Agus Wijaya');
        $response->assertDontSee('BPKB Mobil An. Hendra Gunawan');
    }

    /**
     * Filter berdasarkan kode klasifikasi kearsipan.
     */
    public function test_filter_berdasarkan_kode_klasifikasi(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?kode_klasifikasi=KS.01');

        $response->assertStatus(200);
        $response->assertSee('ARC-KS.01/KSP-SMP/2026/0008');
        $response->assertDontSee('ARC-KU.01/KSP-KEU/2026/0010');
    }

    /**
     * Filter berdasarkan jenis berkas (vital vs non-vital).
     */
    public function test_filter_berdasarkan_jenis_berkas_vital(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?jenis_berkas=vital');

        $response->assertStatus(200);
        $response->assertSee('ARC-KP.03/KSP-KRD/2026/0001');
        $response->assertDontSee('ARC-KU.01/KSP-KEU/2026/0010');
    }

    /**
     * Pencarian global (keyword multi-field).
     */
    public function test_pencarian_keyword_global(): void
    {
        $response = $this->actingAs($this->petugas)->get('/arsip?q=Kuitansi');

        $response->assertStatus(200);
        $response->assertSee('Kuitansi Kas Keluar Oktober 2026');
        $response->assertDontSee('Sertifikat SHM An. Budi Santoso');
    }

    /*
    |--------------------------------------------------------------------------
    | ARSIP-011: Halaman Detail Kartu Arsip, Visualisasi Rak & In-Browser Preview
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman detail kartu arsip menampilkan informasi lengkap, titik rak presisi,
     * dan data nasabah.
     */
    public function test_halaman_detail_kartu_arsip_berhasil_dibuka(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0001';

        $response = $this->actingAs($this->petugas)->get('/arsip/'.$targetId);

        $response->assertStatus(200);
        $response->assertSee('Sertifikat SHM An. Budi Santoso');
        $response->assertSee('Budi Santoso');
        $response->assertSee('KRD-2024-0001');
        $response->assertSee('Brankas Khazanah');
        $response->assertSee('BK-01');
        $response->assertSee('OD-01');
        $response->assertSee('Visualisasi Titik Rak Fisik Presisi');
    }

    /**
     * In-browser preview mengalirkan dokumen PDF asli yang didekripsi langsung.
     */
    public function test_in_browser_preview_pdf_mengalirkan_konten_pdf_terdekripsi(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0001';

        $response = $this->actingAs($this->petugas)->get('/arsip/preview/'.$targetId);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /**
     * Pimpinan juga berhak melihat kartu arsip dan preview dokumen PDF.
     */
    public function test_pimpinan_dapat_melihat_kartu_arsip_dan_preview(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0001';

        $response = $this->actingAs($this->pimpinan)->get('/arsip/'.$targetId);
        $response->assertStatus(200);

        $previewResponse = $this->actingAs($this->pimpinan)->get('/arsip/preview/'.$targetId);
        $previewResponse->assertStatus(200);
        $previewResponse->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Mengunduh berkas PDF berfungsi dengan header attachment.
     */
    public function test_unduh_berkas_pdf_berhasil(): void
    {
        $targetId = 'ARC-KP.03/KSP-KRD/2026/0001';

        $response = $this->actingAs($this->petugas)->get('/arsip/download/'.$targetId);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
