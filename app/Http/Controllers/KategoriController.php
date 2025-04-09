<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // Menampilkan form tambah kategori (jika diperlukan)
    public function create()
    {
        return view('kategori.create');
    }

    // Menyimpan kategori ke database
    public function store(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        // Simpan data kategori ke database
        Kategori::create([
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
        ]);

        // Redirect atau memberi pesan sukses
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan');
    }
}
