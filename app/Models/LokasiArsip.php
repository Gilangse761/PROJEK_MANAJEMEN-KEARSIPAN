<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiArsip extends Model
{
    protected $table = 'lokasi_arsip';

    protected $primaryKey = 'id_lokasi';

    public $timestamps = false;

    protected $guarded = [];
}
