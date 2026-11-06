<?php

namespace App\Services;

use App\Models\KlasifikasiArsip;
use App\Models\TransaksiPinjaman;
use Carbon\Carbon;

class JraService
{
    /**
     * Menghitung jadwal retensi inaktif, jadwal penyusutan, dan status arsip
     * berdasarkan aturan JRA dan status pelunasan pinjaman.
     *
     * @return array{
     *     jadwal_retensi_inaktif: ?string,
     *     jadwal_penyusutan: ?string,
     *     status_arsip: string,
     *     dasar_perhitungan: string,
     *     keterangan_jra: string
     * }
     */
    public function hitung(
        KlasifikasiArsip|string $klasifikasi,
        string $tanggalArsip,
        ?string $referensiNomor = null
    ): array {
        if (is_string($klasifikasi)) {
            $klasifikasi = KlasifikasiArsip::findOrFail($klasifikasi);
        }

        $retensiAktif = (int) $klasifikasi->retensi_aktif;
        $retensiInaktif = (int) $klasifikasi->retensi_inaktif;
        $dihitungDari = $klasifikasi->retensi_dihitung_dari;

        // Arsip permanen tanpa masa retensi (misal Akta Pendirian KE.01)
        if ($retensiAktif === 0 && $retensiInaktif === 0) {
            return [
                'jadwal_retensi_inaktif' => null,
                'jadwal_penyusutan' => null,
                'status_arsip' => $klasifikasi->keterangan_akhir === 'permanen' ? 'permanen' : 'aktif',
                'dasar_perhitungan' => 'Permanen (tanpa batas waktu retensi)',
                'keterangan_jra' => 'Arsip bernilai guna abadi/permanen, disimpan selamanya.',
            ];
        }

        // Tentukan tanggal acuan perhitungan
        $tanggalAcuan = null;
        $dasarPerhitungan = '';

        if ($dihitungDari === 'tanggal_lunas') {
            $pinjaman = $referensiNomor
                ? TransaksiPinjaman::find($referensiNomor)
                : null;

            if ($pinjaman && $pinjaman->status_pinjaman === 'lunas' && $pinjaman->tanggal_lunas) {
                $tanggalAcuan = Carbon::parse($pinjaman->tanggal_lunas);
                $dasarPerhitungan = "Tanggal Lunas Pinjaman ({$pinjaman->tanggal_lunas})";
            } else {
                // Pinjaman masih berjalan atau macet: retensi belum dapat dihitung
                $statusPinjaman = $pinjaman ? strtoupper($pinjaman->status_pinjaman) : 'BELUM TERIKAT PINJAMAN LUNAS';

                return [
                    'jadwal_retensi_inaktif' => null,
                    'jadwal_penyusutan' => null,
                    'status_arsip' => 'aktif',
                    'dasar_perhitungan' => "Menunggu Pelunasan (Status Pinjaman: {$statusPinjaman})",
                    'keterangan_jra' => 'Arsip jaminan vital/kredit tetap AKTIF. Masa inaktif dan pemusnahan belum berjalan selama pinjaman belum dinyatakan lunas.',
                ];
            }
        } else {
            $tanggalAcuan = Carbon::parse($tanggalArsip);
            $dasarPerhitungan = "Tanggal Penciptaan Berkas ({$tanggalArsip})";
        }

        // Hitung tanggal jatuh tempo retensi inaktif (Tanggal Acuan + Retensi Aktif)
        $jadwalInaktif = $tanggalAcuan->copy()->addYears($retensiAktif)->toDateString();

        // Hitung tanggal jatuh tempo penyusutan / pemusnahan (Tanggal Inaktif + Retensi Inaktif)
        $jadwalPenyusutan = $retensiInaktif > 0
            ? Carbon::parse($jadwalInaktif)->addYears($retensiInaktif)->toDateString()
            : null;

        // Tentukan status arsip saat ini berdasarkan tanggal hari ini
        $hariIni = now()->startOfDay();
        $inaktifDt = Carbon::parse($jadwalInaktif)->startOfDay();

        $statusArsip = 'aktif';
        if ($hariIni->gte($inaktifDt)) {
            $statusArsip = 'inaktif';
        }

        return [
            'jadwal_retensi_inaktif' => $jadwalInaktif,
            'jadwal_penyusutan' => $jadwalPenyusutan,
            'status_arsip' => $statusArsip,
            'dasar_perhitungan' => $dasarPerhitungan,
            'keterangan_jra' => "Retensi aktif {$retensiAktif} th, inaktif {$retensiInaktif} th. Keterangan akhir: {$klasifikasi->keterangan_akhir}.",
        ];
    }
}
