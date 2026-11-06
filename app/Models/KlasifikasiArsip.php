<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiArsip extends Model
{
    protected $table = 'klasifikasi_arsip';

    protected $primaryKey = 'kode_klasifikasi';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
