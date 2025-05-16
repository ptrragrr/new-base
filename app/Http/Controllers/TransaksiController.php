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
use Illuminate\Support\Str;

class TransaksiController extends Controller
{
    // public function barang()
    // {
    //     return $this->belongsTo(Barang::class, 'id_barang');
    // }
    
    public function get(Request $request)
{
    // Ambil barang yang stoknya > 0 saja
    $barang = Barang::where('stok_barang', '>', 0)->get();

    return response()->json([
        'success' => true,
        'data' => $barang
    ]);
}

    // use App\Models\Transaksi;

// app/Http/Controllers/TransaksiController.php

public function detail($id)
{
    $transaksi = Transaksi::with(['details.barang'])->find($id);

    if (!$transaksi) {
        return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
    }

    $detail = $transaksi->detail->map(function ($item) {
        return [
            'nama_barang' => $item->barang->nama_barang,
            'jumlah' => $item->jumlah,
            'harga_satuan' => $item->harga_satuan,
            'total_harga' => $item->total_harga,
        ];
    });

    return response()->json($detail);
}



public function create()
{
    $barangs = Barang::where('stok', '>', 0)->get(); // hanya barang dengan stok > 0
    $kasir = Auth::user(); // contoh jika kasir login

    return view('pembayaran.create', compact('barangs', 'kasir'));
}

public function show($id)
{
    $transaksi = Transaksi::with(['details.barang'])->findOrFail($id);

    return response()->json([
        'detail' => $transaksi->detail,
    ]);
}

public function cetakStruk($id)
{
    // Logika untuk mengambil dan mencetak struk berdasarkan ID
    // Contoh: ambil data transaksi dari database dan kirim ke view
    $transaksi = Transaksi::find($id);
    return view('struk', compact('transaksi'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'nama_kasir' => 'required|string',
        'metode_pembayaran' => 'required|string',
        'keranjang' => 'required|string',
        'total' => 'required|numeric',
        'kembalian' => 'required|numeric',
        'bayar' => 'required|numeric',
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
        $transaksiKode = 'PTR-' . strtoupper(Str::random(8));

        $transaksi = Transaksi::create([
            'kode_transaksi' => $transaksiKode,
            'nama_kasir' => $validated['nama_kasir'],
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'total_transaksi' => $validated['total'],
            'bayar' => $validated['bayar'],
            'kembalian' => $validated['kembalian'],
        ]);

        foreach ($keranjang as $item) {
            $barang = Barang::find($item['id_barang']);
            if (!$barang) {
                throw new \Exception("Barang dengan ID {$item['id_barang']} tidak ditemukan.");
            }

            if ($barang->stok_barang < $item['jumlah']) {
                throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi. Tersedia: {$barang->stok_barang}, diminta: {$item['jumlah']}");
            }

            // Kurangi stok
            $barang->stok_barang -= $item['jumlah'];
            $barang->save();

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
            'message' => 'Transaksi berhasil disimpan dan stok diperbarui'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
        ], 500);
    }
}

// private function generateKodeTransaksi(): string
// {
//     $datePart = date('Ymd'); // Contoh: 20250421
//     $randomPart = strtoupper(substr(md5(uniqid()), 0, 6)); // Contoh: 7G9K2L
//     return "PTR-{$datePart}-{$randomPart}";
// }
}
