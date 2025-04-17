<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Barang;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    // public function barang()
    // {
    //     return $this->belongsTo(Barang::class, 'id_barang');
    // }
    
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Barang::all()
        ]);
    }
    
    public function store(Request $request)
{
    $validated = $request->validate([
        'nama_kasir' => 'required|string',
        'metode_pembayaran' => 'required|string',
        'keranjang' => 'required|json',
        'total' => 'required|numeric',
    ]);

    $keranjang = json_decode($validated['keranjang'], true);

    // Simpan ke DB transaksi dan detail barangnya...
    // Loop $keranjang, simpan satu-satu

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil disimpan'
    ]);
}
}
