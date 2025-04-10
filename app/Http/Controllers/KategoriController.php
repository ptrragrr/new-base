<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class KategoriController extends Controller
{
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::all()
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
        $data = Kategori::
            when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%$search%");
                // $query->where('deskripsi', 'like', "%$search%");
            })->latest()->paginate($per);
            // })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    // Menyimpan barang ke database
    public function store(KategoriRequest $request)
{
    $kategori = Kategori::create($request->validated());
    
    return response()->json([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan!',
        'data' => $kategori,
    ]);
    if($kategori){
    }
}

    public function update(KategoriRequest $request, Kategori $kategori)
{
    $validatedData = $request->validated();

    $kategori->update([
        'nama' => $validatedData['nama'],
    ]);

    return response()->json([
        'success' => true,
        'kategori' => $kategori,
    ]);
}

public function destroy(Kategori $kategori)
{
    $kategori->delete();

    return response()->json([
        'success' => true,
        'message' => 'Kategori berhasil dihapus.',
    ]);
}

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori)
{
    return response()->json([
        'kategori' => [
            'nama' => $kategori->nama,
        ]
    ]);
}
}
