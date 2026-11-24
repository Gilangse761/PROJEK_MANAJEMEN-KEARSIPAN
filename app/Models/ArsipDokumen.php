<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class ArsipDokumen extends Model
{
    protected $table = 'arsip_dokumen';

    protected $primaryKey = 'id_arsip';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiArsip::class, 'kode_klasifikasi', 'kode_klasifikasi');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiArsip::class, 'id_lokasi', 'id_lokasi');
    }

    // referensi_nomor bukan FK, tapi bisa dipakai sebagai relasi untuk berkas pinjaman (KP.*)
    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(TransaksiPinjaman::class, 'referensi_nomor', 'no_rekening_pinjaman');
    }

    public function riwayatPeminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanArsip::class, 'id_arsip', 'id_arsip')->orderBy('id_pinjam', 'desc');
    }

    public function peminjamanAktif(): HasOne
    {
        return $this->hasOne(PeminjamanArsip::class, 'id_arsip', 'id_arsip')
            ->where('status_approval', 'disetujui')
            ->whereIn('status_sirkulasi', ['dipinjam', 'terlambat']);
    }

    public function beritaAcara(): HasOne
    {
        return $this->hasOne(BeritaAcaraPenyusutan::class, 'id_arsip', 'id_arsip');
    }

    public function isTersediaFisik(): bool
    {
        return ($this->status_ketersediaan ?? 'tersedia') === 'tersedia';
    }

    public function isSedangDipinjam(): bool
    {
        return ($this->status_ketersediaan ?? 'tersedia') === 'dipinjam';
    }

    public function isBerkasJaminan(): bool
    {
        return $this->kode_klasifikasi === 'KP.03' || ($this->klasifikasi?->jenis_berkas === 'vital');
    }

    /**
     * Memeriksa apakah berkas berada di bawah penahanan hukum (Legal Hold) /
     * dilarang keras dimusnahkan demi kepatuhan hukum kearsipan koperasi.
     */
    public function isLegalHold(): bool
    {
        // 1. Debitur Macet: mutlak dilarang musnah (barang bukti penagihan sengketa)
        if ($this->pinjaman && $this->pinjaman->status_pinjaman === 'macet') {
            return true;
        }

        // 2. Agunan Vital (Sertifikat/BPKB): tidak pernah dimusnahkan (harus diserahkan ke nasabah)
        if ($this->isBerkasJaminan() || $this->klasifikasi?->keterangan_akhir === 'diserahkan') {
            return true;
        }

        // 3. Kredit Berjalan: pinjaman belum lunas
        if ($this->pinjaman && $this->pinjaman->status_pinjaman === 'berjalan') {
            return true;
        }

        // 4. Fisik sedang dipinjam: tidak dapat dimusnahkan secara fisik
        if ($this->isSedangDipinjam()) {
            return true;
        }

        // 5. Arsip Permanen (Akta dll)
        if ($this->klasifikasi?->keterangan_akhir === 'permanen') {
            return true;
        }

        return false;
    }

    /**
     * Menjelaskan alasan mengapa berkas dilarang masuk daftar usul musnah.
     */
    public function getAlasanLegalHoldAttribute(): ?string
    {
        if ($this->pinjaman && $this->pinjaman->status_pinjaman === 'macet') {
            return 'LEGAL HOLD: Debitur Kredit Macet. Berkas ditahan sebagai alat bukti penagihan & penyelesaian hukum kearsipan.';
        }

        if ($this->isBerkasJaminan() || $this->klasifikasi?->keterangan_akhir === 'diserahkan') {
            return 'PROTEKSI AGUNAN VITAL: Dokumen jaminan nasabah (Sertifikat/BPKB). Wajib diserahkan kembali saat lunas, dilarang dimusnahkan.';
        }

        if ($this->pinjaman && $this->pinjaman->status_pinjaman === 'berjalan') {
            return 'KREDIT BERJALAN: Pinjaman nasabah masih aktif belum lunas.';
        }

        if ($this->isSedangDipinjam()) {
            return 'SEDANG DIPINJAM: Fisik berkas sedang beredar keluar brankas.';
        }

        if ($this->klasifikasi?->keterangan_akhir === 'permanen') {
            return 'ARSIP PERMANEN: Dokumen statis bernilai guna abadi koperasi.';
        }

        return null;
    }

    /**
     * Menentukan kelayakan berkas masuk Daftar Usul Musnah (DUM):
     * Syarat: Keterangan akhir 'musnah', status arsip 'inaktif' (atau jadwal penyusutan tiba),
     * bukan berkas yang terkena Legal Hold/Kredit Macet/Agunan Vital, dan belum dimusnahkan.
     */
    public function isLayakMusnah(): bool
    {
        if ($this->status_arsip === 'musnah') {
            return false;
        }

        if ($this->klasifikasi?->keterangan_akhir !== 'musnah') {
            return false;
        }

        if ($this->isLegalHold()) {
            return false;
        }

        if ($this->status_arsip === 'inaktif') {
            return true;
        }

        if ($this->jadwal_penyusutan) {
            return Carbon::parse($this->jadwal_penyusutan)->startOfDay()->lte(now()->startOfDay());
        }

        return false;
    }

    public function hasDigitalFile(): bool
    {
        return ! empty($this->file_digital_path) && Storage::disk('local')->exists($this->file_digital_path);
    }
}
