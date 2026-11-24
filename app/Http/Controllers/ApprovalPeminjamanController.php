<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanArsip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalPeminjamanController extends Controller
{
    /**
     * Menampilkan dashboard persetujuan (approval) peminjaman berkas oleh Pimpinan.
     */
    public function index(): View
    {
        $menungguList = PeminjamanArsip::with([
            'arsip.klasifikasi',
            'arsip.lokasi',
            'arsip.pinjaman.anggota',
        ])
            ->where('status_approval', 'menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        $riwayatList = PeminjamanArsip::with([
            'arsip.klasifikasi',
            'arsip.lokasi',
            'arsip.pinjaman.anggota',
            'approver',
        ])
            ->whereIn('status_approval', ['disetujui', 'ditolak'])
            ->orderBy('tanggal_approval', 'desc')
            ->take(50)
            ->get();

        return view('approval.index', [
            'menungguList' => $menungguList,
            'riwayatList' => $riwayatList,
            'totalMenunggu' => $menungguList->count(),
        ]);
    }

    /**
     * Pimpinan menyetujui (Approve) permohonan peminjaman berkas jaminan.
     * Mengupdate status approval dan mengubah ketersediaan fisik arsip menjadi 'dipinjam'.
     */
    public function setujui(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'catatan_approval' => ['nullable', 'string', 'max:1000'],
        ]);

        $peminjaman = PeminjamanArsip::with('arsip')->findOrFail($id);

        if (! $peminjaman->isMenunggu()) {
            return back()->with('error', 'Permohonan peminjaman ini sudah diproses sebelumnya.');
        }

        if (! $peminjaman->arsip->isTersediaFisik()) {
            return back()->with('error', "Berkas fisik [{$peminjaman->id_arsip}] sudah tidak tersedia di rak/brankas.");
        }

        DB::transaction(function () use ($peminjaman, $request) {
            // Update permohonan peminjaman
            $peminjaman->update([
                'status_approval' => 'disetujui',
                'disetujui_oleh' => auth()->id(),
                'tanggal_approval' => now(),
                'catatan_approval' => $request->input('catatan_approval') ?: 'Disetujui oleh Pimpinan.',
                'status_sirkulasi' => 'dipinjam',
            ]);

            // UPDATE KETERSEDIAAN FISIK: Berkas resmi diserahterimakan dan keluar dari brankas/depo
            $peminjaman->arsip()->update([
                'status_ketersediaan' => 'dipinjam',
            ]);
        });

        return redirect()->route('approval.index')
            ->with('success', "Permohonan pinjam berkas [{$peminjaman->id_arsip}] telah DISETUJUI. Berkas fisik kini sah dikeluarkan (status: DIPINJAM).");
    }

    /**
     * Pimpinan menolak (Reject) permohonan peminjaman berkas jaminan.
     * Berkas fisik tetap aman tersimpan di rak/brankas (status: TERSEDIA).
     */
    public function tolak(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_approval' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan_approval.required' => 'Alasan penolakan permohonan peminjaman wajib diisi oleh pimpinan.',
            'catatan_approval.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $peminjaman = PeminjamanArsip::findOrFail($id);

        if (! $peminjaman->isMenunggu()) {
            return back()->with('error', 'Permohonan peminjaman ini sudah diproses sebelumnya.');
        }

        $peminjaman->update([
            'status_approval' => 'ditolak',
            'disetujui_oleh' => auth()->id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $validated['catatan_approval'],
        ]);

        return redirect()->route('approval.index')
            ->with('success', "Permohonan pinjam berkas [{$peminjaman->id_arsip}] telah DITOLAK. Berkas fisik tetap aman tersimpan di rak/brankas.");
    }
}
