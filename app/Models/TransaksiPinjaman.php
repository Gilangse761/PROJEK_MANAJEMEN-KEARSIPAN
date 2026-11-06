<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPinjaman extends Model
{
    protected $table = 'transaksi_pinjaman';

    protected $primaryKey = 'no_rekening_pinjaman';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
}
