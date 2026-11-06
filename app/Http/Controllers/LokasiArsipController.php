<?php

namespace App\Http\Controllers;

use App\Models\LokasiArsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LokasiArsipController extends Controller
{
    private function rules(): array
    {
        $segmen = ['required', 'regex:/^[A-Z]{2}-[A-Z0-9]{1,3}$/'];

        return [
            'gedung' => ['required', 'max:50'],
            'nomor_rak' => $segmen,
            'ambalan' => $segmen,
            'nomor_boks' => $segmen,
        ];
    }

    public function index()
    {
        return view('lokasi.index', ['items' => LokasiArsip::orderBy('kode_lokasi')->get()]);
    }

    public function create()
    {
        return view('lokasi.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        return $this->simpan($request, new LokasiArsip);
    }

    public function edit(LokasiArsip $lokasi)
    {
        return view('lokasi.form', ['item' => $lokasi]);
    }

    public function update(Request $request, LokasiArsip $lokasi)
    {
        return $this->simpan($request, $lokasi);
    }

    private function simpan(Request $request, LokasiArsip $lokasi)
    {
        $data = $request->validate($this->rules());

        // Kode lokasi dirakit otomatis supaya selalu sinkron dengan rak, ambalan, dan boks
        $data['kode_lokasi'] = "{$data['nomor_rak']}.{$data['ambalan']}.{$data['nomor_boks']}";

        $kembar = LokasiArsip::where('kode_lokasi', $data['kode_lokasi'])
            ->when($lokasi->exists, fn ($q) => $q->where('id_lokasi', '!=', $lokasi->id_lokasi))
            ->exists();

        if ($kembar) {
            return back()->withErrors(['nomor_boks' => "Lokasi {$data['kode_lokasi']} sudah terdaftar."])->withInput();
        }

        $lokasi->fill($data)->save();

        return redirect('/lokasi')->with('success', "Lokasi {$data['kode_lokasi']} tersimpan.");
    }

    public function destroy(LokasiArsip $lokasi)
    {
        if (DB::table('arsip_dokumen')->where('id_lokasi', $lokasi->id_lokasi)->exists()) {
            return back()->with('error', 'Lokasi masih dipakai arsip, tidak bisa dihapus.');
        }

        $lokasi->delete();

        return back()->with('success', 'Lokasi dihapus.');
    }
}
