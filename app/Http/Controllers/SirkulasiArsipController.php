<?php

namespace App\Http\Controllers;

use App\Models\ArsipDokumen;
use App\Models\PeminjamanArsip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SirkulasiArsipController extends Controller
{
    /**
     * Menampilkan daftar peminjaman berkas sirkulasi dengan deteksi keterlambatan otomatis.
     */
    public function index(Request $request): View
    {
        // 1. Deteksi keterlambatan otomatis untuk peminjaman aktif yang melewati batas tanggal
        PeminjamanArsip::where('status_approval', 'disetujui')
            ->where('status_sirkulasi', 'dipinjam')
            ->whereDate('tanggal_kembali_rencana', '<', now()->toDateString())
            ->update(['status_sirkulasi' => 'terlambat']);

        $query = PeminjamanArsip::with(['arsip.klasifikasi', 'arsip.lokasi', 'approver', 'petugasKembali']);

        // Filter status sirkulasi / approval
        $status = $request->input('status', 'semua');
        if ($status === 'menunggu') {
            $query->where('status_approval', 'menunggu');
        } elseif ($status === 'dipinjam') {
            $query->where('status_approval', 'disetujui')->where('status_sirkulasi', 'dipinjam');
        } elseif ($status === 'terlambat') {
            $query->where('status_approval', 'disetujui')->where('status_sirkulasi', 'terlambat');
        } elseif ($status === 'kembali') {
            $query->where('status_sirkulasi', 'kembali');
        } elseif ($status === 'ditolak') {
            $query->where('status_approval', 'ditolak');
        }

        // Filter kata kunci pencarian
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_peminjam', 'like', "%{$q}%")
                    ->orWhere('id_arsip', 'like', "%{$q}%")
                    ->orWhere('keperluan', 'like', "%{$q}%")
                    ->orWhere('divisi', 'like', "%{$q}%")
                    ->orWhereHas('arsip', function ($a) use ($q) {
                        $a->where('judul_arsip', 'like', "%{$q}%")
                            ->orWhere('referensi_nomor', 'like', "%{$q}%");
                    });
            });
        }

        // Filter divisi peminjam
        if ($request->filled('divisi')) {
            $query->where('divisi', $request->input('divisi'));
        }

        $items = $query->orderBy('id_pinjam', 'desc')->get();

        // Statistik Sirkulasi
        $totalMenunggu = PeminjamanArsip::where('status_approval', 'menunggu')->count();
        $totalDipinjam = PeminjamanArsip::where('status_approval', 'disetujui')->where('status_sirkulasi', 'dipinjam')->count();
        $totalTerlambat = PeminjamanArsip::where('status_approval', 'disetujui')->where('status_sirkulasi', 'terlambat')->count();
        $totalKembali = PeminjamanArsip::where('status_sirkulasi', 'kembali')->count();

        $divisiList = PeminjamanArsip::select('divisi')->distinct()->whereNotNull('divisi')->pluck('divisi');

        return view('sirkulasi.index', [
            'items' => $items,
            'currentStatus' => $status,
            'totalMenunggu' => $totalMenunggu,
            'totalDipinjam' => $totalDipinjam,
            'totalTerlambat' => $totalTerlambat,
            'totalKembali' => $totalKembali,
            'divisiList' => $divisiList,
            'filters' => $request->only(['q', 'status', 'divisi']),
        ]);
    }

    /**
     * Menampilkan formulir pengajuan permohonan peminjaman berkas jaminan / arsip.
     */
    public function create(Request $request): View
    {
        $selectedArsipId = $request->query('id_arsip');

        // Hanya arsip dokumen yang ketersediaan fisiknya sedang TERSEDIA dan tidak musnah
        $arsipTersedia = ArsipDokumen::with(['klasifikasi', 'lokasi', 'pinjaman.anggota'])
            ->where('status_ketersediaan', 'tersedia')
            ->where('status_arsip', '!=', 'musnah')
            ->orderBy('kode_klasifikasi')
            ->orderBy('id_arsip')
            ->get();

        return view('sirkulasi.create', [
            'arsipTersedia' => $arsipTersedia,
            'selectedArsipId' => $selectedArsipId,
        ]);
    }

    /**
     * Menyimpan permohonan pinjam berkas jaminan.
     * Pencegahan resiko: Berkas TIDAK langsung keluar, melainkan berstatus 'menunggu' persetujuan pimpinan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_arsip' => ['required', 'exists:arsip_dokumen,id_arsip'],
            'nama_peminjam' => ['required', 'string', 'max:100'],
            'divisi' => ['required', 'string', 'max:50'],
            'keperluan' => ['required', 'string', 'min:5', 'max:1000'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
        ], [
            'id_arsip.required' => 'Pilih berkas arsip jaminan yang ingin dipinjam.',
            'id_arsip.exists' => 'Berkas arsip tidak ditemukan dalam database.',
            'nama_peminjam.required' => 'Nama lengkap peminjam wajib diisi.',
            'divisi.required' => 'Divisi peminjam wajib diisi.',
            'keperluan.required' => 'Alasan / keperluan peminjaman berkas wajib dijelaskan.',
            'keperluan.min' => 'Alasan peminjaman minimal 5 karakter.',
            'tanggal_pinjam.required' => 'Tanggal mulai peminjaman wajib diisi.',
            'tanggal_kembali_rencana.required' => 'Rencana tanggal pengembalian wajib ditentukan.',
            'tanggal_kembali_rencana.after_or_equal' => 'Rencana tanggal pengembalian tidak boleh sebelum tanggal pinjam.',
        ]);

        $arsip = ArsipDokumen::where('id_arsip', $validated['id_arsip'])->firstOrFail();

        // Validasi ketersediaan fisik berkas
        if (! $arsip->isTersediaFisik()) {
            return back()
                ->withInput()
                ->with('error', "Berkas fisik [{$arsip->id_arsip}] saat ini SEDANG DIPINJAM atau tidak tersedia di depo. Tidak dapat diajukan permohonan baru.");
        }

        // Cek apakah sudah ada permohonan peminjaman lain yang masih menggantung (menunggu approval)
        $pendingLoan = PeminjamanArsip::where('id_arsip', $arsip->id_arsip)
            ->where('status_approval', 'menunggu')
            ->exists();

        if ($pendingLoan) {
            return back()
                ->withInput()
                ->with('error', "Berkas [{$arsip->id_arsip}] sudah memiliki permohonan pinjam lain yang sedang MENUNGGU PERSETUJUAN pimpinan.");
        }

        PeminjamanArsip::create([
            'id_arsip' => $validated['id_arsip'],
            'nama_peminjam' => $validated['nama_peminjam'],
            'divisi' => $validated['divisi'],
            'keperluan' => $validated['keperluan'],
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'tanggal_kembali_rencana' => $validated['tanggal_kembali_rencana'],
            'status_sirkulasi' => 'dipinjam',
            'status_approval' => 'menunggu',
            'disetujui_oleh' => null,
            'tanggal_approval' => null,
            'catatan_approval' => null,
            'tanggal_kembali_aktual' => null,
            'catatan_pengembalian' => null,
            'petugas_pengembalian' => null,
        ]);

        return redirect()->route('sirkulasi.index')
            ->with('success', "Permohonan pinjam berkas [{$arsip->id_arsip}] berhasil diajukan! Berkas fisik tetap aman di brankas/depo hingga pimpinan memberikan persetujuan (approval).");
    }

    /**
     * Menampilkan rincian sirkulasi dan log riwayat peminjaman spesifik.
     */
    public function show(int $id): View
    {
        $peminjaman = PeminjamanArsip::with([
            'arsip.klasifikasi',
            'arsip.lokasi',
            'arsip.pinjaman.anggota',
            'approver',
            'petugasKembali',
        ])->findOrFail($id);

        return view('sirkulasi.show', [
            'peminjaman' => $peminjaman,
        ]);
    }

    /**
     * Memproses serah terima pengembalian berkas fisik jaminan oleh petugas.
     * Mengembalikan status fisik arsip menjadi 'tersedia'.
     */
    public function kembali(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_kembali_aktual' => ['required', 'date'],
            'catatan_pengembalian' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'tanggal_kembali_aktual.required' => 'Tanggal penerimaan pengembalian berkas wajib diisi.',
            'catatan_pengembalian.required' => 'Catatan kondisi fisik berkas saat diterima wajib dicatat.',
        ]);

        $peminjaman = PeminjamanArsip::with('arsip')->findOrFail($id);

        if (! $peminjaman->isDisetujui()) {
            return back()->with('error', 'Peminjaman ini belum disetujui pimpinan, pengembalian tidak dapat diproses.');
        }

        if ($peminjaman->isKembali()) {
            return back()->with('error', 'Berkas jaminan ini sudah tercatat telah dikembalikan sebelumnya.');
        }

        DB::transaction(function () use ($peminjaman, $validated) {
            // Update status sirkulasi menjadi kembali
            $peminjaman->update([
                'status_sirkulasi' => 'kembali',
                'tanggal_kembali_aktual' => $validated['tanggal_kembali_aktual'],
                'catatan_pengembalian' => $validated['catatan_pengembalian'],
                'petugas_pengembalian' => auth()->id(),
            ]);

            // Pulihkan ketersediaan fisik arsip di brankas / depo
            $peminjaman->arsip()->update([
                'status_ketersediaan' => 'tersedia',
            ]);
        });

        return redirect()->route('sirkulasi.index')
            ->with('success', "Pengembalian berkas [{$peminjaman->id_arsip}] berhasil dicatat. Status fisik dokumen kini kembali TERSEDIA di rak/brankas!");
    }
}
