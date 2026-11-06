<?php

namespace App\Http\Controllers;

use App\Models\ArsipDokumen;
use App\Models\KlasifikasiArsip;
use App\Models\LokasiArsip;
use App\Models\TransaksiPinjaman;
use App\Services\DigitalArchiveService;
use App\Services\JraService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ArsipDokumenController extends Controller
{
    // Kode unit kerja diturunkan dari 2 huruf awal klasifikasi
    private const UNIT_KERJA = [
        'KP' => 'KSP-KRD',
        'KS' => 'KSP-SMP',
        'KU' => 'KSP-KEU',
        'KE' => 'KSP-UMM',
    ];

    public function __construct(
        protected JraService $jraService,
        protected DigitalArchiveService $digitalArchiveService
    ) {}

    /**
     * Menampilkan daftar arsip dengan kemampuan pencarian multi-parameter (ARSIP-010).
     */
    public function index(Request $request)
    {
        $query = ArsipDokumen::with(['klasifikasi', 'lokasi', 'pinjaman.anggota']);

        // 1. Pencarian Global (Keyword / Semua Parameter)
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('id_arsip', 'like', "%{$q}%")
                    ->orWhere('judul_arsip', 'like', "%{$q}%")
                    ->orWhere('referensi_nomor', 'like', "%{$q}%")
                    ->orWhereHas('pinjaman.anggota', function ($a) use ($q) {
                        $a->where('nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('nik', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Pencarian Spesifik: Nomor / Kode Arsip
        if ($request->filled('id_arsip')) {
            $query->where('id_arsip', 'like', '%'.trim((string) $request->input('id_arsip')).'%');
        }

        // 3. Pencarian Spesifik: Nama Anggota
        if ($request->filled('nama_anggota')) {
            $nama = trim((string) $request->input('nama_anggota'));
            $query->whereHas('pinjaman.anggota', function ($a) use ($nama) {
                $a->where('nama_lengkap', 'like', "%{$nama}%");
            });
        }

        // 4. Pencarian Spesifik: Nomor Kontrak Pinjaman / Rekening
        if ($request->filled('no_rekening')) {
            $query->where('referensi_nomor', 'like', '%'.trim((string) $request->input('no_rekening')).'%');
        }

        // 5. Filter Spesifik: Kategori / Klasifikasi
        if ($request->filled('kode_klasifikasi')) {
            $query->where('kode_klasifikasi', $request->input('kode_klasifikasi'));
        }

        // 6. Filter Spesifik: Jenis Berkas (Vital / Non-Vital)
        if ($request->filled('jenis_berkas')) {
            $query->whereHas('klasifikasi', function ($k) use ($request) {
                $k->where('jenis_berkas', $request->input('jenis_berkas'));
            });
        }

        // 7. Filter Status Arsip
        if ($request->filled('status_arsip')) {
            $query->where('status_arsip', $request->input('status_arsip'));
        }

        // 8. Filter Lokasi
        if ($request->filled('id_lokasi')) {
            $query->where('id_lokasi', $request->input('id_lokasi'));
        }

        $items = $query->orderBy('id_arsip', 'desc')->get();

        return view('arsip.index', [
            'items' => $items,
            'klasifikasiList' => KlasifikasiArsip::orderBy('kode_klasifikasi')->get(),
            'lokasiList' => LokasiArsip::orderBy('kode_lokasi')->get(),
            'filters' => $request->only(['q', 'id_arsip', 'nama_anggota', 'no_rekening', 'kode_klasifikasi', 'jenis_berkas', 'status_arsip', 'id_lokasi']),
            'totalHasil' => $items->count(),
            'totalKeseluruhan' => ArsipDokumen::count(),
        ]);
    }

    public function create()
    {
        return view('arsip.create', [
            'klasifikasi' => KlasifikasiArsip::orderBy('kode_klasifikasi')->get(),
            'lokasi' => LokasiArsip::orderBy('kode_lokasi')->get(),
            'pinjaman' => TransaksiPinjaman::with('anggota')->orderBy('no_rekening_pinjaman')->get(),
        ]);
    }

    /**
     * Menyimpan arsip baru, menghitung JRA otomatis (ARSIP-008),
     * dan mengunggah pindaian PDF terenkripsi jika ada (ARSIP-009).
     */
    public function store(Request $request)
    {
        $kode = (string) $request->input('kode_klasifikasi');

        // Berkas pinjaman (KP.*) wajib terhubung ke data transaksi_pinjaman yang benar-benar ada
        $aturanReferensi = str_starts_with($kode, 'KP.')
            ? ['required', 'exists:transaksi_pinjaman,no_rekening_pinjaman']
            : ['nullable', 'max:30'];

        $data = $request->validate([
            'kode_klasifikasi' => ['required', 'exists:klasifikasi_arsip,kode_klasifikasi'],
            'id_lokasi' => ['required', 'exists:lokasi_arsip,id_lokasi'],
            'referensi_nomor' => $aturanReferensi,
            'judul_arsip' => ['required', 'max:150'],
            'tanggal_arsip' => ['required', 'date', 'before_or_equal:today'],
            'media_arsip' => ['nullable', 'in:kertas,digital,hybrid'],
            'file_digital' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'referensi_nomor.required' => 'Berkas pinjaman (KP.*) wajib diisi nomor rekening pinjamannya.',
            'referensi_nomor.exists' => 'Nomor rekening pinjaman tidak ditemukan di data transaksi pinjaman.',
            'tanggal_arsip.before_or_equal' => 'Tanggal berkas tidak boleh di masa depan.',
            'file_digital.mimes' => 'Berkas pindaian harus berupa dokumen berformat PDF.',
            'file_digital.max' => 'Ukuran berkas scan PDF tidak boleh melebihi 10 MB.',
        ]);

        $unit = self::UNIT_KERJA[substr($kode, 0, 2)] ?? 'KSP-UMM';
        $prefix = 'ARC-'.$kode.'/'.$unit.'/'.now()->year.'/';

        // Hitung JRA otomatis (ARSIP-008)
        $jra = $this->jraService->hitung(
            $data['kode_klasifikasi'],
            $data['tanggal_arsip'],
            $data['referensi_nomor'] ?? null
        );

        $idArsip = DB::transaction(function () use ($data, $prefix, $jra, $request) {
            // Ambil nomor terakhir dengan prefix yang sama, kunci baris agar tidak bentrok
            $terakhir = DB::table('arsip_dokumen')
                ->where('id_arsip', 'like', $prefix.'%')
                ->lockForUpdate()
                ->max('id_arsip');

            $urut = $terakhir ? ((int) substr($terakhir, strlen($prefix))) + 1 : 1;
            $id = $prefix.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);

            // Simpan pindaian PDF terenkripsi jika diunggah (ARSIP-009)
            $filePath = null;
            $media = $data['media_arsip'] ?? 'kertas';

            if ($request->hasFile('file_digital')) {
                $filePath = $this->digitalArchiveService->storeEncrypted(
                    $request->file('file_digital'),
                    $id
                );
                if ($media === 'kertas') {
                    $media = 'hybrid';
                }
            }

            DB::table('arsip_dokumen')->insert([
                'id_arsip' => $id,
                'kode_klasifikasi' => $data['kode_klasifikasi'],
                'id_lokasi' => $data['id_lokasi'],
                'referensi_nomor' => $data['referensi_nomor'] ?? null,
                'judul_arsip' => $data['judul_arsip'],
                'tanggal_arsip' => $data['tanggal_arsip'],
                'media_arsip' => $media,
                'file_digital_path' => $filePath,
                'status_arsip' => $jra['status_arsip'],
                'jadwal_retensi_inaktif' => $jra['jadwal_retensi_inaktif'],
                'jadwal_penyusutan' => $jra['jadwal_penyusutan'],
            ]);

            return $id;
        });

        return redirect('/arsip')
            ->with('success', "Arsip terdaftar dengan kode {$idArsip}. Jadwal retensi telah dihitung otomatis.");
    }

    /**
     * Menampilkan Kartu Arsip, visualisasi titik rak fisik presisi,
     * dan in-browser preview PDF (ARSIP-011).
     */
    public function show(string $idArsip)
    {
        $arsip = ArsipDokumen::with(['klasifikasi', 'lokasi', 'pinjaman.anggota'])
            ->where('id_arsip', $idArsip)
            ->firstOrFail();

        // Hitung info JRA untuk kartu arsip
        $jraInfo = $this->jraService->hitung(
            $arsip->klasifikasi,
            $arsip->tanggal_arsip,
            $arsip->referensi_nomor
        );

        // Ambil lokasi rak sekitarnya pada gedung & nomor rak yang sama untuk visualisasi presisi
        $titikSekitar = LokasiArsip::where('gedung', $arsip->lokasi?->gedung)
            ->where('nomor_rak', $arsip->lokasi?->nomor_rak)
            ->orderBy('ambalan')
            ->orderBy('nomor_boks')
            ->get();

        return view('arsip.show', [
            'arsip' => $arsip,
            'jraInfo' => $jraInfo,
            'titikSekitar' => $titikSekitar,
            'hasDigitalFile' => $arsip->hasDigitalFile(),
        ]);
    }

    /**
     * Menampilkan preview dokumen PDF langsung di browser (in-browser preview) (ARSIP-011).
     */
    public function preview(string $idArsip): Response
    {
        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->firstOrFail();

        if (! $arsip->file_digital_path) {
            abort(404, 'Berkas pindaian digital PDF belum diunggah untuk arsip ini.');
        }

        $pdfBinary = $this->digitalArchiveService->getDecrypted($arsip->file_digital_path);

        if (! $pdfBinary) {
            abort(404, 'File digital tidak ditemukan pada media penyimpanan server.');
        }

        $filename = 'ARSIP_'.str_replace(['/', ' '], '_', $arsip->id_arsip).'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Mengunduh berkas PDF terenkripsi yang didekripsi saat diunduh.
     */
    public function download(string $idArsip): Response
    {
        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->firstOrFail();

        if (! $arsip->file_digital_path) {
            abort(404, 'Berkas pindaian digital PDF belum diunggah untuk arsip ini.');
        }

        $pdfBinary = $this->digitalArchiveService->getDecrypted($arsip->file_digital_path);

        if (! $pdfBinary) {
            abort(404, 'File digital tidak ditemukan pada media penyimpanan server.');
        }

        $filename = 'ARSIP_'.str_replace(['/', ' '], '_', $arsip->id_arsip).'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Mengunggah / memperbarui pindaian PDF untuk arsip yang sudah ada (ARSIP-009).
     */
    public function uploadFile(Request $request, string $idArsip)
    {
        $arsip = ArsipDokumen::where('id_arsip', $idArsip)->firstOrFail();

        $request->validate([
            'file_digital' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file_digital.required' => 'Silakan pilih berkas PDF yang akan diunggah.',
            'file_digital.mimes' => 'Berkas harus berupa dokumen berformat PDF.',
            'file_digital.max' => 'Ukuran berkas scan PDF tidak boleh melebihi 10 MB.',
        ]);

        // Hapus file lama jika ada
        if ($arsip->file_digital_path) {
            $this->digitalArchiveService->deleteFile($arsip->file_digital_path);
        }

        $newPath = $this->digitalArchiveService->storeEncrypted(
            $request->file('file_digital'),
            $arsip->id_arsip
        );

        $media = $arsip->media_arsip === 'kertas' ? 'hybrid' : $arsip->media_arsip;

        $arsip->update([
            'file_digital_path' => $newPath,
            'media_arsip' => $media,
        ]);

        return redirect()->route('arsip.show', $arsip->id_arsip)
            ->with('success', 'Berkas pindaian PDF berhasil diunggah dan disimpan dengan enkripsi aman AES-256.');
    }
}
