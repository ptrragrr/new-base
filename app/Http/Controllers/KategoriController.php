<?php
// app/Http/Controllers/KategoriController.php
namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::select('id', 'nama_kategori')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori = Kategori::create(['nama_kategori' => $request->nama_kategori]);

        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }

    public function show(Kategori $kategori)
{
    return response()->json([
        'kategori' => [
            'kategori_barang' => $barang->kategori_barang,
        ]
    ]);
}
}
