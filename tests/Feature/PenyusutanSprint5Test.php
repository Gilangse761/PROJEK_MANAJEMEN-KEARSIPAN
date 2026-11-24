<?php

namespace Tests\Feature;

use App\Models\ArsipDokumen;
use App\Models\BeritaAcaraPenyusutan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenyusutanSprint5Test extends TestCase
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
    | SPRINT 5: MODUL PENYUSUTAN, EKSEKUSI PEMUSNAHAN & BERITA ACARA LEGAL
    |--------------------------------------------------------------------------
    */

    /**
     * Filter Otomatis: Berkas inaktif dengan keterangan akhir 'musnah' masuk dalam Daftar Usul Musnah (DUM).
     */
    public function test_filter_otomatis_daftar_usul_musnah_berkas_inaktif(): void
    {
        // ARC-KU.01/KSP-KEU/2026/0010 adalah kuitansi kas inaktif (keterangan akhir: musnah)
        $arsip = ArsipDokumen::where('id_arsip', 'ARC-KU.01/KSP-KEU/2026/0010')->firstOrFail();

        $this->assertTrue($arsip->isLayakMusnah());
        $this->assertFalse($arsip->isLegalHold());

        $response = $this->actingAs($this->petugas)->get('/penyusutan?tab=usul-musnah');

        $response->assertStatus(200);
        $response->assertSee('Daftar Usul Musnah');
        $response->assertSee('ARC-KU.01/KSP-KEU/2026/0010');
    }

    /**
     * PENCEGAHAN PELANGGARAN FATAL KEARSIPAN:
     * Dokumen kredit macet dan berkas jaminan vital MUTLAK DILARANG masuk usul musnah (Legal Hold).
     */
    public function test_proteksi_legal_hold_mencegah_pemusnahan_agunan_dan_kredit_macet(): void
    {
        // 1. Sertifikat SHM Budi Santoso (Agunan Vital + Kredit Macet)
        $agunanMacet = ArsipDokumen::where('id_arsip', 'ARC-KP.03/KSP-KRD/2026/0001')->firstOrFail();
        $this->assertTrue($agunanMacet->isLegalHold());
        $this->assertFalse($agunanMacet->isLayakMusnah());
        $this->assertStringContainsString('LEGAL HOLD', $agunanMacet->alasan_legal_hold);

        // 2. Perjanjian Kredit Budi Santoso (Kredit Macet)
        $pkMacet = ArsipDokumen::where('id_arsip', 'ARC-KP.02/KSP-KRD/2026/0005')->firstOrFail();
        $this->assertTrue($pkMacet->isLegalHold());
        $this->assertFalse($pkMacet->isLayakMusnah());

        // 3. Sertifikat SHM Siti Rahayu (Kredit Berjalan)
        $agunanBerjalan = ArsipDokumen::where('id_arsip', 'ARC-KP.03/KSP-KRD/2026/0003')->firstOrFail();
        $this->assertTrue($agunanBerjalan->isLegalHold());
        $this->assertFalse($agunanBerjalan->isLayakMusnah());

        // Tab Legal Hold menampilkan berkas-berkas yang dilindungi ini
        $response = $this->actingAs($this->petugas)->get('/penyusutan?tab=legal-hold');
        $response->assertStatus(200);
        $response->assertSee('Proteksi Legal Hold');
        $response->assertSee('ARC-KP.03/KSP-KRD/2026/0001');
        $response->assertSee('DILARANG MUSNAH');
    }

    /**
     * Sistem MENOLAK keras upaya pengajuan pemusnahan untuk berkas yang berstatus Legal Hold.
     */
    public function test_sistem_menolak_pengajuan_pemusnahan_berkas_legal_hold(): void
    {
        $response = $this->actingAs($this->petugas)->post('/penyusutan', [
            'id_arsip' => 'ARC-KP.03/KSP-KRD/2026/0001', // Agunan Vital Kredit Macet!
            'metode_pemusnahan' => 'Pencacahan Fisik',
            'petugas_eksekutor' => 'Petugas Arsip',
            'saksi_1' => 'Jerry Pratama',
            'saksi_2' => 'Gilang Ramadhan',
            'dasar_pertimbangan' => 'Coba musnahkan agunan macet secara ilegal',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('PELANGGARAN FATAL DITOLAK', session('error'));

        // Tidak boleh ada Berita Acara yang terbuat untuk berkas agunan macet ini
        $this->assertDatabaseMissing('berita_acara_penyusutan', [
            'id_arsip' => 'ARC-KP.03/KSP-KRD/2026/0001',
        ]);
    }

    /**
     * Petugas dapat mengajukan usulan pemusnahan untuk berkas yang sah layak musnah,
     * status awal BA adalah 'menunggu' persetujuan pimpinan.
     */
    public function test_petugas_mengajukan_usulan_pemusnahan_berita_acara(): void
    {
        $idArsip = 'ARC-KU.01/KSP-KEU/2026/0010';

        $response = $this->actingAs($this->petugas)->post('/penyusutan', [
            'id_arsip' => $idArsip,
            'metode_pemusnahan' => 'Pencacahan Fisik (Cross-Cut Shredder) & Peleburan Kertas',
            'petugas_eksekutor' => 'Ahmad Petugas Arsip',
            'saksi_1' => 'Jerry Pratama (Staf Legal & Kepatuhan)',
            'saksi_2' => 'Gilang Ramadhan (Pengawas Internal KSP)',
            'dasar_pertimbangan' => 'Telah lampau masa inaktif JRA 2 tahun dan tidak menyangkut perkara hukum.',
            'lokasi_eksekusi' => 'Ruang Pemusnahan Depo Arsip KSP',
        ]);

        $response->assertRedirect('/penyusutan?tab=berita-acara');
        $response->assertSessionHas('success');

        $ba = BeritaAcaraPenyusutan::where('id_arsip', $idArsip)->first();
        $this->assertNotNull($ba);
        $this->assertEquals('menunggu', $ba->status_approval);
        $this->assertStringStartsWith('BA-MSN/KSP/', $ba->no_berita_acara);
        $this->assertEquals('Ahmad Petugas Arsip', $ba->petugas_eksekutor);
    }

    /**
     * Pimpinan menyetujui (Approve) usulan pemusnahan berkas inaktif.
     */
    public function test_pimpinan_menyetujui_usulan_pemusnahan(): void
    {
        // Buat usulan BA terlebih dahulu
        $this->actingAs($this->petugas)->post('/penyusutan', [
            'id_arsip' => 'ARC-KU.01/KSP-KEU/2026/0010',
            'metode_pemusnahan' => 'Pencacahan Fisik Total',
            'petugas_eksekutor' => 'Petugas Arsip',
            'saksi_1' => 'Jerry Pratama',
            'saksi_2' => 'Gilang Ramadhan',
            'dasar_pertimbangan' => 'Retensi inaktif habis sesuai aturan JRA.',
        ]);

        $ba = BeritaAcaraPenyusutan::where('id_arsip', 'ARC-KU.01/KSP-KEU/2026/0010')->firstOrFail();
        $this->assertEquals('menunggu', $ba->status_approval);

        // Pimpinan login dan menyetujui
        $response = $this->actingAs($this->pimpinan)->post("/penyusutan/{$ba->no_berita_acara}/setujui", [
            'catatan_approval' => 'Disetujui. Telah diverifikasi bahwa berkas inaktif telah lampau dan tidak terikat kredit macet.',
        ]);

        $response->assertRedirect("/penyusutan/{$ba->no_berita_acara}");
        $response->assertSessionHas('success');

        $ba->refresh();
        $this->assertEquals('disetujui', $ba->status_approval);
        $this->assertEquals($this->pimpinan->id, $ba->disetujui_oleh);
        $this->assertNotNull($ba->tanggal_approval);
    }

    /**
     * Eksekusi Pemusnahan Fisik: Setelah disetujui pimpinan,
     * petugas mengeksekusi pemusnahan dan status arsip berubah permanen menjadi 'musnah'.
     */
    public function test_eksekusi_pemusnahan_mengubah_status_arsip_menjadi_musnah(): void
    {
        $idArsip = 'ARC-KU.01/KSP-KEU/2026/0010';
        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->firstOrFail();
        $this->assertEquals('inaktif', $arsip->status_arsip);

        // Pimpinan membuat/menyetujui usulan BA
        $this->actingAs($this->pimpinan)->post('/penyusutan', [
            'id_arsip' => $idArsip,
            'metode_pemusnahan' => 'Pencacahan Fisik Cross-Cut Shredder',
            'petugas_eksekutor' => 'Petugas Eksekutor',
            'saksi_1' => 'Jerry Pratama',
            'saksi_2' => 'Gilang Ramadhan',
            'dasar_pertimbangan' => 'Memenuhi masa retensi inaktif JRA.',
        ]);

        $ba = BeritaAcaraPenyusutan::where('id_arsip', $idArsip)->firstOrFail();
        $this->assertEquals('disetujui', $ba->status_approval);

        // Petugas melakukan eksekusi fisik
        $response = $this->actingAs($this->petugas)->post("/penyusutan/{$ba->no_berita_acara}/eksekusi");

        $response->assertRedirect("/penyusutan/{$ba->no_berita_acara}/cetak");
        $response->assertSessionHas('success');

        // Status arsip kini resmi MUSNAH dan mencatat nomor Berita Acara
        $arsip->refresh();
        $this->assertEquals('musnah', $arsip->status_arsip);
        $this->assertEquals($ba->no_berita_acara, $arsip->no_berita_acara_pemusnahan);
    }

    /**
     * Eksekusi pemusnahan DITOLAK jika usulan Berita Acara belum disetujui pimpinan.
     */
    public function test_eksekusi_pemusnahan_gagal_jika_belum_disetujui_pimpinan(): void
    {
        $idArsip = 'ARC-KU.01/KSP-KEU/2026/0010';

        // Petugas mengajukan (status masih 'menunggu')
        $this->actingAs($this->petugas)->post('/penyusutan', [
            'id_arsip' => $idArsip,
            'metode_pemusnahan' => 'Pencacahan Fisik',
            'petugas_eksekutor' => 'Petugas Arsip',
            'saksi_1' => 'Jerry Pratama',
            'saksi_2' => 'Gilang Ramadhan',
            'dasar_pertimbangan' => 'Dasar JRA.',
        ]);

        $ba = BeritaAcaraPenyusutan::where('id_arsip', $idArsip)->firstOrFail();
        $this->assertEquals('menunggu', $ba->status_approval);

        // Coba langsung eksekusi tanpa approval
        $response = $this->actingAs($this->petugas)->post("/penyusutan/{$ba->no_berita_acara}/eksekusi");

        $response->assertSessionHas('error');

        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->first();
        $this->assertNotEquals('musnah', $arsip->status_arsip);
    }

    /**
     * Format Cetak Berita Acara Legal (PDF View):
     * Menampilkan kop surat resmi, konsiderans hukum JRA, tabel rincian berkas, dan 4 kolom tanda tangan legal.
     */
    public function test_cetak_berita_acara_legal_menampilkan_format_resmi(): void
    {
        $idArsip = 'ARC-KU.01/KSP-KEU/2026/0010';

        $this->actingAs($this->pimpinan)->post('/penyusutan', [
            'id_arsip' => $idArsip,
            'metode_pemusnahan' => 'Pencacahan Fisik (Cross-Cut Shredder) & Peleburan Kertas',
            'petugas_eksekutor' => 'Budi Petugas Eksekutor',
            'saksi_1' => 'Jerry Pratama, S.H. (Legal)',
            'saksi_2' => 'Gilang Ramadhan, S.E. (SPI)',
            'dasar_pertimbangan' => 'JRA KSP Nomor 04 Tahun 2026.',
        ]);

        $ba = BeritaAcaraPenyusutan::where('id_arsip', $idArsip)->firstOrFail();

        $response = $this->actingAs($this->petugas)->get("/penyusutan/{$ba->no_berita_acara}/cetak");

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA PEMUSNAHAN ARSIP');
        $response->assertSee($ba->no_berita_acara);
        $response->assertSee('KOPERASI SIMPAN PINJAM (KSP) ARTHA MAKMUR SEJAHTERA');
        $response->assertSee('Budi Petugas Eksekutor');
        $response->assertSee('Jerry Pratama, S.H. (Legal)');
        $response->assertSee('Gilang Ramadhan, S.E. (SPI)');
        $response->assertSee('Kepala Bagian Kredit (Pimpinan)');
        $response->assertSee('Klausula Integritas Kearsipan');
    }
}

