<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('klasifikasi_arsip', function (Blueprint $table) {
            $table->string('kode_klasifikasi', 10)->primary();
            $table->string('nama_klasifikasi', 100);
            $table->integer('retensi_aktif');
            $table->integer('retensi_inaktif');
            $table->enum('keterangan_akhir', ['musnah', 'permanen', 'diserahkan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasi_arsip');
    }
};
