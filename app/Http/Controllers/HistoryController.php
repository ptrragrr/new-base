<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;

class HistoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => History::all()
        ]);
    }

    use Illuminate\Support\Str;

public function store(Request $request)
{
    $validated = $request->validate([
        'nama_kasir' => 'required|string',
        'metode_pembayaran' => 'required|string',
        'total' => 'required|numeric',
        'keranjang' => 'required|json',
    ]);

    // Generate kode transaksi unik
    $kodeTransaksi = 'TRX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

    $history = History::create([
        'kode_transaksi' => $kodeTransaksi,
        'nama_kasir' => $validated['nama_kasir'],
        'metode_pembayaran' => $validated['metode_pembayaran'],
        'total' => $validated['total'],
        'keranjang' => $validated['keranjang'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil disimpan',
        'data' => $history
    ]);
}

    public function show($id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'History tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function update(Request $request, $id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'History tidak ditemukan'], 404);
        }

        $history->update($request->all());

        return response()->json(['success' => true, 'message' => 'History berhasil diupdate']);
    }

    public function destroy($id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'History tidak ditemukan'], 404);
        }

        $history->delete();

        return response()->json(['success' => true, 'message' => 'History berhasil dihapus']);
    }
}
