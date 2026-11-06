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
        Schema::create('lokasi_arsip', function (Blueprint $table) {
            $table->increments('id_lokasi');
            $table->string('kode_lokasi', 20)->unique();
            $table->string('gedung', 50);
            $table->string('nomor_rak', 10);
            $table->string('nomor_boks', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_arsip');
    }
};
