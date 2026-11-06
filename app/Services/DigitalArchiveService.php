<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalArchiveService
{
    private const DISK = 'local';

    private const DIRECTORY = 'arsip_digital';

    /**
     * Menyimpan berkas pindaian PDF ke storage terisolasi dengan enkripsi AES-256.
     *
     * @return string Path relatif file terenkripsi pada disk lokal
     */
    public function storeEncrypted(UploadedFile $file, string $idArsip): string
    {
        $sanitizedId = Str::slug($idArsip, '_');
        $filename = sprintf(
            '%s_%s_%s.pdf.enc',
            $sanitizedId,
            now()->format('YmdHis'),
            Str::random(8)
        );

        $storagePath = self::DIRECTORY.'/'.$filename;

        $rawBinary = file_get_contents($file->getRealPath());
        $encryptedBinary = Crypt::encrypt($rawBinary);

        Storage::disk(self::DISK)->put($storagePath, $encryptedBinary);

        return $storagePath;
    }

    /**
     * Membaca dan mendekripsi konten file PDF dari storage aman.
     */
    public function getDecrypted(string $storagePath): ?string
    {
        if (! Storage::disk(self::DISK)->exists($storagePath)) {
            return null;
        }

        $rawContent = Storage::disk(self::DISK)->get($storagePath);

        try {
            return Crypt::decrypt($rawContent);
        } catch (Exception) {
            // Fallback apabila file disimpan tanpa enkripsi (misal file warisan)
            return $rawContent;
        }
    }

    /**
     * Menghapus file dari storage.
     */
    public function deleteFile(?string $storagePath): bool
    {
        if ($storagePath && Storage::disk(self::DISK)->exists($storagePath)) {
            return Storage::disk(self::DISK)->delete($storagePath);
        }

        return false;
    }

    /**
     * Membuat string binary PDF standar yang valid (PDF-1.4) untuk keperluan dummy dan testing.
     */
    public function generateDummyPdf(string $title, string $documentId): string
    {
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s\-\.\,\(\)]/', '', $title);
        $cleanId = preg_replace('/[^a-zA-Z0-9\s\-\.\,\/\:]/', '', $documentId);

        $streamContent = "BT\n/F1 18 Tf\n50 720 Td\n(KSP E-ARSIP DIGITAL REPOSITORY) Tj\n"
            ."/F1 14 Tf\n0 -35 Td\n(DOKUMEN JAMINAN / BERKAS VITAL RESMI) Tj\n"
            ."/F1 11 Tf\n0 -30 Td\n(Kode Registrasi : {$cleanId}) Tj\n"
            ."0 -20 Td\n(Judul Dokumen   : {$cleanTitle}) Tj\n"
            ."0 -20 Td\n(Status Keamanan : ENCRYPTED WITH AES-256) Tj\n"
            ."0 -20 Td\n(Tanggal Pindai  : ".date('Y-m-d H:i:s').") Tj\n"
            ."0 -40 Td\n(Dokumen ini adalah salinan digital sah yang telah diotentikasi oleh sistem e-Arsip KSP.) Tj\nET";

        $length = strlen($streamContent);

        $pdf = "%PDF-1.4\n"
            ."1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n"
            ."2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n"
            ."3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n"
            ."4 0 obj\n<< /Length {$length} >>\nstream\n{$streamContent}\nendstream\nendobj\n"
            ."5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n"
            ."xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000244 00000 n \n"
            .sprintf("%010d 00000 n \n", 244 + 40 + $length)
            ."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n"
            .(350 + $length)."\n%%EOF\n";

        return $pdf;
    }

    /**
     * Menyimpan dummy PDF terenkripsi langsung (berguna untuk seeder).
     */
    public function storeDummyEncrypted(string $title, string $idArsip): string
    {
        $sanitizedId = Str::slug($idArsip, '_');
        $filename = sprintf('%s_dummy.pdf.enc', $sanitizedId);
        $storagePath = self::DIRECTORY.'/'.$filename;

        $pdfContent = $this->generateDummyPdf($title, $idArsip);
        $encrypted = Crypt::encrypt($pdfContent);

        Storage::disk(self::DISK)->put($storagePath, $encrypted);

        return $storagePath;
    }
}
