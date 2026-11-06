<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function hasDigitalFile(): bool
    {
        return ! empty($this->file_digital_path) && Storage::disk('local')->exists($this->file_digital_path);
    }
}
