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
        Schema::create('transaksi_pinjaman', function (Blueprint $table) {
            $table->string('no_rekening_pinjaman', 25)->primary();
            $table->string('id_anggota', 20);
            $table->decimal('nominal_pinjaman', 15, 2);
            $table->date('tanggal_akad');
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status_pinjaman', ['berjalan', 'lunas', 'macet']);
            $table->date('tanggal_lunas')->nullable();
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pinjaman');
    }
};
