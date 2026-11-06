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
        Schema::create('peminjaman_arsip', function (Blueprint $table) {
            $table->increments('id_pinjam');
            $table->string('id_arsip', 30);
            $table->string('nama_peminjam', 100);
            $table->string('divisi', 50);
            $table->text('keperluan');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->enum('status_sirkulasi', ['dipinjam', 'kembali', 'terlambat']);
            $table->foreign('id_arsip')->references('id_arsip')->on('arsip_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_arsip');
    }
};
