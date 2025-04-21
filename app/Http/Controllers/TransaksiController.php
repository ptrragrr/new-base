<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Http\Requests\TransaksiRequest;
use App\Models\Barang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        'keranjang' => 'required|string',
        'total' => 'required|numeric',
    ]);

    $keranjang = json_decode($validated['keranjang'], true);

    if ($keranjang === null && json_last_error() !== JSON_ERROR_NONE) {
        return response()->json([
            'message' => 'Keranjang gagal didecode dari JSON',
            'error' => json_last_error_msg(),
        ], 400);
    }
    
    DB::beginTransaction();
    try {
        $transaksi = Transaksi::create([
            'nama_kasir' => $validated['nama_kasir'],
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'total' => $validated['total'],
        ]);

        Log::info($transaksi->id);    
        foreach ($keranjang as $item) {
            Log::info($item);
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id,
                'id_barang' => $item['id_barang'],
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $item['harga_barang'],
                'total_harga' => $item['total_harga'],
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
        ], 500);
    }
}
}
