# e-Arsip KSP

Sistem Informasi Manajemen Arsip Dokumen Koperasi Simpan Pinjam (KSP).

---

## Panduan Setup Proyek

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal:

### 1. Clone Repository
```bash
git clone https://github.com/jerry-sutisno/earsip-ksp.git
cd earsip-ksp
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env` lalu generate application key:
```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan sesuaikan pengaturan database MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=earsip_ksp
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan database `earsip_ksp` sudah dibuat di MySQL/phpMyAdmin)*

### 4. Migrasi & Seeder Database
Jalankan migrasi tabel beserta data dummy (Master, Transaksi, dan User):
```bash
php artisan migrate:fresh --seed
```

> **Catatan Sprint 3:** 
> - Jalankan `php artisan migrate:fresh --seed` untuk memperbarui database beserta perhitungan otomatis JRA dan berkas pindaian scan terenkripsi.
> - Tersedia file dump database terbaru di [`database/earsip_ksp_sprint3.sql`](database/earsip_ksp_sprint3.sql).
> - **Fitur Baru Sprint 3:**
>   - **Kalkulasi Otomatis JRA (ARSIP-008):** Perhitungan otomatis tanggal jatuh tempo inaktif dan tanggal penyusutan/pemusnahan sesuai aturan klasifikasi dan status pelunasan pinjaman nasabah (dokumen jaminan pinjaman macet otomatis diproteksi tetap aktif).
>   - **Upload & Penyimpanan Terenkripsi (ARSIP-009):** Upload pindaian scan PDF (maks. 10 MB) dengan penyimpanan terisolasi dan enkripsi AES-256 di sisi server.
>   - **Temu Balik Multi-Parameter (ARSIP-010):** Mesin pencarian dokumen arsip berdasarkan nomor registrasi, nama anggota nasabah, nomor rekening pinjaman, kategori klasifikasi, jenis berkas vital, status arsip, dan lokasi rak.
>   - **Kartu Arsip & Visualisasi Rak Fisik Presisi (ARSIP-011):** Halaman kartu arsip komprehensif dengan denah visual rak fisik 4-tingkat (Gedung, Rak, Ambalan, Boks) serta penampil *in-browser preview* berkas digital PDF.

### 5. Jalankan Server
```bash
php artisan serve
```
Akses aplikasi melalui browser di: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Akun Demo

Gunakan akun berikut untuk login dan menguji hak akses role:

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Petugas Arsip** | `petugas@earsip.test` | `password` | Dashboard, Menu Klasifikasi (CRUD), Menu Lokasi (CRUD), Menu Arsip (Registrasi & Lihat) |
| **Pimpinan** | `pimpinan@earsip.test` | `password` | Dashboard, Menu Klasifikasi (Read-only), Menu Lokasi (Read-only), Menu Arsip (Read-only), Menu Approval (`/approval`) |

---

## Menjalankan Unit/Feature Test

Untuk memverifikasi fungsionalitas autentikasi, role middleware, dan dashboard:
```bash
php artisan test
```
