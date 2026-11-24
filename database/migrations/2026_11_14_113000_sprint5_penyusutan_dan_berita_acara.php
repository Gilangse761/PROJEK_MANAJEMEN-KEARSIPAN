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
        Schema::table('berita_acara_penyusutan', function (Blueprint $table) {
            $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])
                ->default('disetujui')
                ->after('petugas_eksekutor');
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
            $table->string('saksi_1', 100)
                ->nullable()
                ->after('catatan_approval');
            $table->string('saksi_2', 100)
                ->nullable()
                ->after('saksi_1');
            $table->text('dasar_pertimbangan')
                ->nullable()
                ->after('saksi_2');
            $table->string('lokasi_eksekusi', 150)
                ->default('Depo Arsip KSP')
                ->after('dasar_pertimbangan');
            $table->timestamps();
        });

        Schema::table('arsip_dokumen', function (Blueprint $table) {
            $table->string('no_berita_acara_pemusnahan', 30)
                ->nullable()
                ->after('status_ketersediaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip_dokumen', function (Blueprint $table) {
            $table->dropColumn('no_berita_acara_pemusnahan');
        });

        Schema::table('berita_acara_penyusutan', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn([
                'status_approval',
                'disetujui_oleh',
                'tanggal_approval',
                'catatan_approval',
                'saksi_1',
                'saksi_2',
                'dasar_pertimbangan',
                'lokasi_eksekusi',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
