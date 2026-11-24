<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanArsip extends Model
{
    protected $table = 'peminjaman_arsip';

    protected $primaryKey = 'id_pinjam';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_rencana' => 'date',
            'tanggal_approval' => 'datetime',
            'tanggal_kembali_aktual' => 'date',
        ];
    }

    public function arsip(): BelongsTo
    {
        return $this->belongsTo(ArsipDokumen::class, 'id_arsip', 'id_arsip');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function petugasKembali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_pengembalian');
    }

    public function isMenunggu(): bool
    {
        return $this->status_approval === 'menunggu';
    }

    public function isDisetujui(): bool
    {
        return $this->status_approval === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status_approval === 'ditolak';
    }

    public function isKembali(): bool
    {
        return $this->status_sirkulasi === 'kembali';
    }

    /**
     * Deteksi keterlambatan otomatis:
     * Peminjaman yang sudah disetujui, belum dikembalikan, dan melewati tanggal rencana kembali.
     */
    public function isTerlambat(): bool
    {
        if ($this->isKembali() || $this->isDitolak() || $this->isMenunggu()) {
            return false;
        }

        if ($this->status_sirkulasi === 'terlambat') {
            return true;
        }

        if (! $this->tanggal_kembali_rencana) {
            return false;
        }

        return $this->tanggal_kembali_rencana->isPast() && ! $this->tanggal_kembali_rencana->isToday();
    }

    /**
     * Menghitung selisih hari keterlambatan pengembalian fisik berkas jaminan.
     */
    public function getHariTerlambatAttribute(): int
    {
        if (! $this->isTerlambat() || ! $this->tanggal_kembali_rencana) {
            return 0;
        }

        return (int) $this->tanggal_kembali_rencana->diffInDays(now()->startOfDay());
    }

    /**
     * Label status komprehensif untuk antarmuka sirkulasi.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isMenunggu()) {
            return 'Menunggu Approval';
        }

        if ($this->isDitolak()) {
            return 'Ditolak Pimpinan';
        }

        if ($this->isKembali()) {
            return 'Telah Kembali';
        }

        if ($this->isTerlambat()) {
            $hari = $this->hari_terlambat;

            return $hari > 0 ? "Terlambat {$hari} Hari" : 'Terlambat';
        }

        return 'Sedang Dipinjam';
    }
}
