<?php

namespace App\Http\Controllers;

use App\Models\ArsipDokumen;
use App\Models\BeritaAcaraPenyusutan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenyusutanArsipController extends Controller
{
    /**
     * Menampilkan dashboard modul penyusutan arsip, daftar usul musnah (DUM),
     * daftar proteksi legal hold, dan riwayat berita acara legal.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'usul-musnah');

        $allArchives = ArsipDokumen::with(['klasifikasi', 'lokasi', 'pinjaman.anggota', 'beritaAcara'])->get();

        // 1. Daftar Usul Musnah (DUM): Memenuhi syarat retensi inaktif dan keterangan akhir musnah
        $usulMusnah = $allArchives->filter(fn (ArsipDokumen $a) => $a->isLayakMusnah())->values();

        // 2. Daftar Proteksi Hukum (Legal Hold): Dokumen yang DITAHAN dari pemusnahan demi kepatuhan kearsipan
        $legalHold = $allArchives->filter(fn (ArsipDokumen $a) => $a->isLegalHold() && $a->status_arsip !== 'musnah')->values();

        // 3. Daftar Berkas yang Telah Resmi Dimusnahkan
        $telahMusnah = $allArchives->filter(fn (ArsipDokumen $a) => $a->status_arsip === 'musnah')->values();

        // 4. Daftar Berita Acara Penyusutan
        $beritaAcaraList = BeritaAcaraPenyusutan::with(['arsip.klasifikasi', 'arsip.pinjaman.anggota', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        $menungguApproval = $beritaAcaraList->where('status_approval', 'menunggu')->count();

        return view('penyusutan.index', [
            'currentTab' => $tab,
            'usulMusnah' => $usulMusnah,
            'legalHold' => $legalHold,
            'telahMusnah' => $telahMusnah,
            'beritaAcaraList' => $beritaAcaraList,
            'totalUsulMusnah' => $usulMusnah->count(),
            'totalLegalHold' => $legalHold->count(),
            'totalTelahMusnah' => $telahMusnah->count(),
            'totalBeritaAcara' => $beritaAcaraList->count(),
            'totalMenungguApproval' => $menungguApproval,
        ]);
    }

    /**
     * Menampilkan form pengajuan usulan pemusnahan berkas inaktif.
     */
    public function create(Request $request): View
    {
        $selectedArsipId = $request->query('id_arsip');

        // Ambil arsip yang layak musnah saja
        $arsipEligible = ArsipDokumen::with(['klasifikasi', 'lokasi', 'pinjaman.anggota'])
            ->get()
            ->filter(fn (ArsipDokumen $a) => $a->isLayakMusnah())
            ->values();

        return view('penyusutan.create', [
            'arsipEligible' => $arsipEligible,
            'selectedArsipId' => $selectedArsipId,
        ]);
    }

    /**
     * Menyimpan pengajuan Berita Acara Pemusnahan Berkas.
     * Menerapkan validasi ketat anti-pelanggaran fatal kearsipan (Filter Validasi JRA).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_arsip' => ['required', 'exists:arsip_dokumen,id_arsip'],
            'metode_pemusnahan' => ['required', 'string', 'max:100'],
            'petugas_eksekutor' => ['required', 'string', 'max:100'],
            'saksi_1' => ['required', 'string', 'max:100'],
            'saksi_2' => ['required', 'string', 'max:100'],
            'dasar_pertimbangan' => ['required', 'string', 'min:5', 'max:1000'],
            'lokasi_eksekusi' => ['nullable', 'string', 'max:150'],
        ], [
            'id_arsip.required' => 'Pilih berkas arsip yang diusulkan untuk dimusnahkan.',
            'metode_pemusnahan.required' => 'Metode pemusnahan fisik/digital wajib diisi.',
            'petugas_eksekutor.required' => 'Nama petugas / ketua panitia eksekutor pemusnahan wajib diisi.',
            'saksi_1.required' => 'Saksi 1 (Bagian Hukum & Kepatuhan) wajib dicantumkan.',
            'saksi_2.required' => 'Saksi 2 (Satuan Pengawas Internal) wajib dicantumkan.',
            'dasar_pertimbangan.required' => 'Dasar pertimbangan dan rujukan aturan JRA wajib dijelaskan.',
        ]);

        $arsip = ArsipDokumen::with(['klasifikasi', 'pinjaman'])->where('id_arsip', $validated['id_arsip'])->firstOrFail();

        // 🛡️ FILTER VALIDASI JRA: Cegah Pelanggaran Fatal Kearsipan!
        if ($arsip->isLegalHold()) {
            return back()->withInput()->with('error', "PELANGGARAN FATAL DITOLAK! Berkas [{$arsip->id_arsip}] DILARANG DIMUSNAHKAN karena berada dalam status: {$arsip->alasan_legal_hold}");
        }

        if (! $arsip->isLayakMusnah()) {
            return back()->withInput()->with('error', "Berkas [{$arsip->id_arsip}] belum memenuhi syarat Jadwal Retensi Arsip (JRA) untuk dimusnahkan.");
        }

        // Cek apakah sudah ada Berita Acara aktif untuk berkas ini
        $existingBa = BeritaAcaraPenyusutan::where('id_arsip', $arsip->id_arsip)->first();
        if ($existingBa) {
            return back()->withInput()->with('error', "Berkas [{$arsip->id_arsip}] sudah memiliki Berita Acara [{$existingBa->no_berita_acara}].");
        }

        // Generate Nomor Berita Acara Legal: BA-MSN/KSP/{Tahun}/{NomorUrut}
        $year = now()->year;
        $prefix = "BA-MSN/KSP/{$year}/";

        $noBa = DB::transaction(function () use ($prefix, $validated) {
            $last = DB::table('berita_acara_penyusutan')
                ->where('no_berita_acara', 'like', $prefix.'%')
                ->lockForUpdate()
                ->max('no_berita_acara');

            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $nomor = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);

            $isPimpinan = auth()->user()->role === 'pimpinan';

            BeritaAcaraPenyusutan::create([
                'no_berita_acara' => $nomor,
                'id_arsip' => $validated['id_arsip'],
                'tanggal_eksekusi' => now()->toDateString(),
                'jenis_tindakan' => 'pemusnahan',
                'metode_pemusnahan' => $validated['metode_pemusnahan'],
                'petugas_eksekutor' => $validated['petugas_eksekutor'],
                'status_approval' => $isPimpinan ? 'disetujui' : 'menunggu',
                'disetujui_oleh' => $isPimpinan ? auth()->id() : null,
                'tanggal_approval' => $isPimpinan ? now() : null,
                'catatan_approval' => $isPimpinan ? 'Disetujui langsung oleh pimpinan saat pendaftaran Berita Acara.' : null,
                'saksi_1' => $validated['saksi_1'],
                'saksi_2' => $validated['saksi_2'],
                'dasar_pertimbangan' => $validated['dasar_pertimbangan'],
                'lokasi_eksekusi' => ! empty($validated['lokasi_eksekusi']) ? $validated['lokasi_eksekusi'] : 'Ruang Pemusnahan Depo Arsip KSP',
            ]);

            return $nomor;
        });

        if (auth()->user()->role === 'pimpinan') {
            return redirect()->route('penyusutan.show', $noBa)
                ->with('success', "Usulan pemusnahan berhasil disetujui pimpinan dengan Berita Acara [{$noBa}]. Petugas kini dapat mengeksekusi pemusnahan fisik.");
        }

        return redirect()->route('penyusutan.index', ['tab' => 'berita-acara'])
            ->with('success', "Usulan pemusnahan berkas [{$arsip->id_arsip}] berhasil diajukan dengan nomor Berita Acara [{$noBa}]. Menunggu persetujuan pimpinan sebelum eksekusi fisik.");
    }

    /**
     * Pimpinan menyetujui (Approve) usulan pemusnahan berkas.
     */
    public function setujui(Request $request, string $noBa): RedirectResponse
    {
        $ba = BeritaAcaraPenyusutan::with('arsip.pinjaman')->findOrFail($noBa);

        if (! $ba->isMenunggu()) {
            return back()->with('error', 'Berita Acara ini sudah diproses persetujuannya sebelumnya.');
        }

        if ($ba->arsip->isLegalHold()) {
            return back()->with('error', "PELANGGARAN FATAL DITOLAK! Berkas ini terdeteksi berada dalam proteksi hukum: {$ba->arsip->alasan_legal_hold}");
        }

        $ba->update([
            'status_approval' => 'disetujui',
            'disetujui_oleh' => auth()->id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $request->input('catatan_approval') ?: 'Disetujui. Telah diverifikasi bahwa berkas inaktif telah habis masa retensinya dan tidak terkait pinjaman macet/agunan aktif.',
        ]);

        return redirect()->route('penyusutan.show', $noBa)
            ->with('success', "Pemusnahan berkas untuk Berita Acara [{$noBa}] telah DISETUJUI oleh pimpinan. Siap dieksekusi pemusnahan fisik.");
    }

    /**
     * Pimpinan menolak (Reject) usulan pemusnahan berkas.
     */
    public function tolak(Request $request, string $noBa): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_approval' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan_approval.required' => 'Alasan penolakan pemusnahan wajib diisi oleh pimpinan.',
        ]);

        $ba = BeritaAcaraPenyusutan::findOrFail($noBa);

        if (! $ba->isMenunggu()) {
            return back()->with('error', 'Berita Acara ini sudah diproses persetujuannya sebelumnya.');
        }

        $ba->update([
            'status_approval' => 'ditolak',
            'disetujui_oleh' => auth()->id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $validated['catatan_approval'],
        ]);

        return redirect()->route('penyusutan.index', ['tab' => 'berita-acara'])
            ->with('success', "Usulan pemusnahan [{$noBa}] telah DITOLAK. Berkas fisik tetap aman tersimpan.");
    }

    /**
     * Eksekusi pemusnahan fisik berkas setelah disetujui pimpinan.
     * Mengubah status arsip menjadi 'musnah'.
     */
    public function eksekusi(Request $request, string $noBa): RedirectResponse
    {
        $ba = BeritaAcaraPenyusutan::with('arsip')->findOrFail($noBa);

        if (! $ba->isDisetujui()) {
            return back()->with('error', 'Pemusnahan tidak dapat dieksekusi sebelum mendapatkan persetujuan (approval) resmi dari pimpinan!');
        }

        if ($ba->arsip->status_arsip === 'musnah') {
            return back()->with('error', 'Berkas arsip ini sudah berstatus musnah sebelumnya.');
        }

        DB::transaction(function () use ($ba) {
            // Update status arsip menjadi musnah secara permanen
            $ba->arsip()->update([
                'status_arsip' => 'musnah',
                'no_berita_acara_pemusnahan' => $ba->no_berita_acara,
            ]);

            $ba->update([
                'tanggal_eksekusi' => now()->toDateString(),
            ]);
        });

        return redirect()->route('penyusutan.cetak', $ba->no_berita_acara)
            ->with('success', "Eksekusi pemusnahan fisik berkas [{$ba->id_arsip}] berhasil diselesaikan! Status arsip kini resmi MUSNAH. Berita Acara Legal siap dicetak / diunduh.");
    }

    /**
     * Menampilkan rincian Berita Acara Penyusutan & Legal Log.
     */
    public function show(string $noBa): View
    {
        $ba = BeritaAcaraPenyusutan::with([
            'arsip.klasifikasi',
            'arsip.lokasi',
            'arsip.pinjaman.anggota',
            'approver',
        ])->findOrFail($noBa);

        return view('penyusutan.show', [
            'ba' => $ba,
        ]);
    }

    /**
     * Menampilkan format cetak resmi Berita Acara Pemusnahan PDF / Legal Document (ANRI / Kemenkop).
     */
    public function cetak(string $noBa): View
    {
        $ba = BeritaAcaraPenyusutan::with([
            'arsip.klasifikasi',
            'arsip.lokasi',
            'arsip.pinjaman.anggota',
            'approver',
        ])->findOrFail($noBa);

        return view('penyusutan.cetak', [
            'ba' => $ba,
        ]);
    }
}
