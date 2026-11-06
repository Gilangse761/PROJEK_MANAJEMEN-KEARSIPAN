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
        Schema::create('berita_acara_penyusutan', function (Blueprint $table) {
            $table->string('no_berita_acara', 30)->primary();
            $table->string('id_arsip', 30);
            $table->date('tanggal_eksekusi');
            $table->enum('jenis_tindakan', ['pemusnahan', 'penyerahan_statis']);
            $table->string('metode_pemusnahan', 100);
            $table->string('petugas_eksekutor', 100);
            $table->foreign('id_arsip')->references('id_arsip')->on('arsip_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acara_penyusutan');
    }
};
