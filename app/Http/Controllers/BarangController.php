<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangRequest;
use App\Models\Barang;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function get(Request $request)
{
    $barang = Barang::join('kategori', 'kategori.id', '=', 'barang.id_kategori')
        ->select(
            'barang.*',
            'kategori.nama as kategori_nama',
            DB::raw("CONCAT('" . asset('storage') . "/', barang.foto_barang) as foto_barang_url")
        )
        ->get();

    return response()->json([
        'success' => true,
        'data' => $barang
    ]);
}
    // public function get(Request $request)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' => Barang::all()
    //     ]);
    // }

    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Barang::
        join('kategori', 'kategori.id', '=', 'barang.id_kategori')->
            when($request->search, function ($query, $search) {
                $query->where('barang.nama_barang', 'like', "%$search%")
                    ->orWhere('kategori.nama', 'like', "%$search%")
                    ->orWhere('harga_barang', 'like', "%$search%")
                    ->orWhere('stok_barang', 'like', "%$search%")
                    ->orWhere('foto_barang', 'like', "%$search%");
            })->select('barang.*', 'kategori.nama as kategori')->latest()->paginate($per);
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

    // Upload file jika ada
    if ($request->hasFile('foto_barang')) {
        $file = $request->file('foto_barang');
        $path = $file->store('barang', 'public'); // simpan di storage/app/public/barang
        $validatedData['foto_barang'] = $path;
    } else {
        $validatedData['foto_barang'] = null;
    }

    $barang = Barang::create([
        'nama_barang' => $validatedData['nama_barang'],
        'id_kategori' => $validatedData['id_kategori'],
        'harga_barang' => $validatedData['harga_barang'],
        'stok_barang' => $validatedData['stok_barang'],
        'foto_barang' => $validatedData['foto_barang'],
    ]);

    return response()->json([
        'success' => true,
        'barang' => $barang,
    ]);
}

   public function update(BarangRequest $request, Barang $barang)
{
    $validatedData = $request->validated();

    if ($request->hasFile('foto_barang')) {
        $file = $request->file('foto_barang');
        $path = $file->store('barang', 'public');
        $validatedData['foto_barang'] = $path;
    } else {
        // Jika tidak upload file baru, bisa simpan yang lama atau null tergantung logikamu
        $validatedData['foto_barang'] = $barang->foto_barang;
    }

    $barang->update([
        'nama_barang' => $validatedData['nama_barang'],
        'id_kategori' => $validatedData['id_kategori'],
        'harga_barang' => $validatedData['harga_barang'],
        'stok_barang' => $validatedData['stok_barang'],
        'foto_barang' => $validatedData['foto_barang'],
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
            'id_kategori' => $barang->id_kategori, // GANTI INI
            'harga_barang' => $barang->harga_barang,
            'stok_barang' => $barang->stok_barang,
            'foto_barang' => $barang->foto_barang,

        ]
    ]);
}

public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_barang');
    }
}
