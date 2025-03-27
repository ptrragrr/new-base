<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengirimanRequest;
use App\Http\Requests\UpdatePengirimanRequest;
use DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Pengiriman;
use App\Models\Kurir;

class PengirimanController extends Controller
{
    // Mengambil semua data pengiriman dengan pagination
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Kurir::when($request->search, function (Builder $query, string $search) {
            $query->where('kurir_id', 'like', "%$search%")
                ->orWhere('paket', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->orWhere('tanggal_pengiriman', 'like', "%$search%")
                ->orWhere('alamat', 'like', "%$search%")
                ->orWhere('biaya', 'like', "%$search%")
                ->orWhere('penerima', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    // Menampilkan detail pengiriman berdasarkan ID
    public function show($id)
    {
        $pengiriman = Pengiriman::with('kurir')->find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail pengiriman',
            'data' => $pengiriman
        ]);
    }

    // Menambahkan data pengiriman baru
    public function store(StorePengirimanRequest $request)
    {
        $request->validate([
            'kurir_id' => 'required|exists:kurirs,id',
            'paket' => 'required|string',
            'status' => 'required|string',
            'tanggal_pengiriman' => 'required|date',
            'biaya' => 'required|numeric',
        ]);

        $pengiriman = Pengiriman::create($request->all());

        return response()->json([
            'message' => 'Pengiriman berhasil ditambahkan',
            'data' => $pengiriman
        ], 201);
    }

    // Mengupdate data pengiriman
    public function update(UpdatePengirimanRequest $request, $id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        $request->validate([
            'kurir_id' => 'required|exists:kurirs,id',
            'paket' => 'required|string',
            'status' => 'required|string',
            'tanggal_pengiriman' => 'required|date',
            'biaya' => 'required|numeric',
        ]);

        $pengiriman->update($request->all());

        return response()->json([
            'message' => 'Pengiriman berhasil diperbarui',
            'data' => $pengiriman
        ]);
    }

    // Menghapus pengiriman
    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
        }

        $pengiriman->delete();

        return response()->json(['message' => 'Pengiriman berhasil dihapus']);
    }
}
