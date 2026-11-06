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
        Schema::table('klasifikasi_arsip', function (Blueprint $table) {
            $table->enum('jenis_berkas', ['vital', 'non_vital'])->default('non_vital')->after('nama_klasifikasi');
            $table->enum('retensi_dihitung_dari', ['tanggal_arsip', 'tanggal_lunas'])->default('tanggal_arsip')->after('retensi_inaktif');
        });

        Schema::table('lokasi_arsip', function (Blueprint $table) {
            $table->string('ambalan', 10)->nullable()->after('nomor_rak');
        });
    }

    public function down(): void
    {
        Schema::table('klasifikasi_arsip', function (Blueprint $table) {
            $table->dropColumn(['jenis_berkas', 'retensi_dihitung_dari']);
        });

        Schema::table('lokasi_arsip', function (Blueprint $table) {
            $table->dropColumn('ambalan');
        });
    }
};
