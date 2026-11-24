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
        Schema::table('arsip_dokumen', function (Blueprint $table) {
            $table->enum('status_ketersediaan', ['tersedia', 'dipinjam'])
                ->default('tersedia')
                ->after('status_arsip');
        });

        Schema::table('peminjaman_arsip', function (Blueprint $table) {
            $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])
                ->default('menunggu')
                ->after('status_sirkulasi');
            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->after('status_approval')
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('tanggal_approval')
                ->nullable()
                ->after('disetujui_oleh');
            $table->text('catatan_approval')
                ->nullable()
                ->after('tanggal_approval');
            $table->date('tanggal_kembali_aktual')
                ->nullable()
                ->after('catatan_approval');
            $table->text('catatan_pengembalian')
                ->nullable()
                ->after('tanggal_kembali_aktual');
            $table->foreignId('petugas_pengembalian')
                ->nullable()
                ->after('catatan_pengembalian')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip_dokumen', function (Blueprint $table) {
            $table->dropColumn('status_ketersediaan');
        });

        Schema::table('peminjaman_arsip', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropForeign(['petugas_pengembalian']);
            $table->dropColumn([
                'status_approval',
                'disetujui_oleh',
                'tanggal_approval',
                'catatan_approval',
                'tanggal_kembali_aktual',
                'catatan_pengembalian',
                'petugas_pengembalian',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
