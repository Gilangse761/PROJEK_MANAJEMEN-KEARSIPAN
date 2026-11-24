<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaAcaraPenyusutan extends Model
{
    protected $table = 'berita_acara_penyusutan';

    protected $primaryKey = 'no_berita_acara';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_eksekusi' => 'date',
            'tanggal_approval' => 'datetime',
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

    public function isPemusnahan(): bool
    {
        return $this->jenis_tindakan === 'pemusnahan';
    }

    public function isPenyerahanStatis(): bool
    {
        return $this->jenis_tindakan === 'penyerahan_statis';
    }

    public function isDisetujui(): bool
    {
        return $this->status_approval === 'disetujui';
    }

    public function isMenunggu(): bool
    {
        return $this->status_approval === 'menunggu';
    }
}
