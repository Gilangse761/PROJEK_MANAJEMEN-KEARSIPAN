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
        Schema::create('arsip_dokumen', function (Blueprint $table) {
            $table->string('id_arsip', 30)->primary();
            $table->string('kode_klasifikasi', 10);
            $table->unsignedInteger('id_lokasi');
            $table->string('referensi_nomor', 30)->nullable();
            $table->string('judul_arsip', 150);
            $table->date('tanggal_arsip');
            $table->enum('media_arsip', ['kertas', 'digital', 'hybrid']);
            $table->string('file_digital_path', 255)->nullable();
            $table->enum('status_arsip', ['aktif', 'inaktif', 'musnah', 'permanen']);
            $table->date('jadwal_retensi_inaktif')->nullable();
            $table->date('jadwal_penyusutan')->nullable();
            $table->index('referensi_nomor');
            $table->foreign('kode_klasifikasi')->references('kode_klasifikasi')->on('klasifikasi_arsip');
            $table->foreign('id_lokasi')->references('id_lokasi')->on('lokasi_arsip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumen');
    }
};
