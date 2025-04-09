<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangRequest;
use App\Models\Barang;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Barang::all()
        ]);
    }

    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Barang::
            when($request->search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%$search%")
                    ->orWhere('kategori_barang', 'like', "%$search%")
                    ->orWhere('harga_barang', 'like', "%$search%")
                    ->orWhere('stok_barang', 'like', "%$search%")
                    ->orWhere('foto_barang', 'like', "%$search%");
            })->latest()->paginate($per);
            // })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    // Menyimpan barang ke database
    public function store(BarangRequest $request)
    {
        $validatedData = $request->validated();
        $barang = Barang::create([
            'nama_barang' => $validatedData['nama_barang'],
            'kategori_barang' => $validatedData['kategori_barang'],
            'harga_barang' => $validatedData['harga_barang'],
            'stok_barang' => $validatedData['stok_barang'],
        ]);

        return response()->json([
            'success' => true,
            'barang' => $barang,
        ]);
    }

    public function update(BarangRequest $request, Barang $barang)
{
    $validatedData = $request->validated();

    $barang->update([
        'nama_barang' => $validatedData['nama_barang'],
        'kategori_barang' => $validatedData['kategori_barang'],
        'harga_barang' => $validatedData['harga_barang'],
        'stok_barang' => $validatedData['stok_barang'],
    ]);

    return response()->json([
        'success' => true,
        'barang' => $barang,
    ]);
}

public function destroy(Barang $barang)
{
    $barang->delete();

    return response()->json([
        'success' => true,
        'message' => 'Barang berhasil dihapus.',
    ]);
}

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
{
    return response()->json([
        'barang' => [
            'nama_barang' => $barang->nama_barang,
            'kategori_barang' => $barang->kategori_barang,
            'harga_barang' => $barang->harga_barang,
            'stok_barang' => $barang->stok_barang,
        ]
    ]);
}
}
