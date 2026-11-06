<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiArsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlasifikasiArsipController extends Controller
{
    private function fieldRules(): array
    {
        return [
            'nama_klasifikasi' => ['required', 'max:100'],
            'jenis_berkas' => ['required', 'in:vital,non_vital'],
            'retensi_aktif' => ['required', 'integer', 'min:0', 'max:100'],
            'retensi_inaktif' => ['required', 'integer', 'min:0', 'max:100'],
            'retensi_dihitung_dari' => ['required', 'in:tanggal_arsip,tanggal_lunas'],
            'keterangan_akhir' => ['required', 'in:musnah,permanen,diserahkan'],
        ];
    }

    public function index()
    {
        return view('klasifikasi.index', ['items' => KlasifikasiArsip::orderBy('kode_klasifikasi')->get()]);
    }

    public function create()
    {
        return view('klasifikasi.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge([
            'kode_klasifikasi' => ['required', 'regex:/^[A-Z]{2}\.\d{2}$/', 'unique:klasifikasi_arsip,kode_klasifikasi'],
        ], $this->fieldRules()));

        KlasifikasiArsip::create($data);

        return redirect('/klasifikasi')->with('success', "Klasifikasi {$data['kode_klasifikasi']} tersimpan.");
    }

    public function edit(KlasifikasiArsip $klasifikasi)
    {
        return view('klasifikasi.form', ['item' => $klasifikasi]);
    }

    public function update(Request $request, KlasifikasiArsip $klasifikasi)
    {
        // Kode tidak boleh diubah karena dipakai sebagai foreign key
        $klasifikasi->update($request->validate($this->fieldRules()));

        return redirect('/klasifikasi')->with('success', "Klasifikasi {$klasifikasi->kode_klasifikasi} diperbarui.");
    }

    public function destroy(KlasifikasiArsip $klasifikasi)
    {
        if (DB::table('arsip_dokumen')->where('kode_klasifikasi', $klasifikasi->kode_klasifikasi)->exists()) {
            return back()->with('error', 'Klasifikasi masih dipakai arsip, tidak bisa dihapus.');
        }

        $klasifikasi->delete();

        return back()->with('success', 'Klasifikasi dihapus.');
    }
}
