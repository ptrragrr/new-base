<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;
use App\Models\Transaksi;

class HistoryController extends Controller
{
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => History::all()
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
         $data = History::when($request->search, function (Builder $query, string $search) {
             $query->where('kode_transaksi', 'like', "%$search%")
                 ->orWhere('nama_kasir', 'like', "%$search%")
                 ->orWhere('metode_pembayaran', 'like', "%$search%");
         })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
 
         return response()->json($data);
     }

//      public function view_pdf()
// {
//     $data = Transaksi::with('kasir') // jika pakai relasi
//         ->orderBy('created_at', 'desc')
//         ->get();

//     $html = View::make('transaksi.pdf', compact('data'))->render();

//     $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
//     $mpdf->WriteHTML($html);

//     return response($mpdf->Output('', 'S'), 200)
//         ->header('Content-Type', 'application/pdf');
// }

public function preview_pdf()
{
    $data = Transaksi::with(['kasir', 'details'])->orderBy('created_at', 'desc')->get();

    Log::info($data);
    return view('pdf', compact('data'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kasir' => 'required|string',
            'metode_pembayaran' => 'required|string',
            'total' => 'required|numeric',
            'keranjang' => 'required|json',
        ]);

        $kodeTransaksi = 'TRX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        $history = History::create([
            'kode_transaksi' => $kodeTransaksi,
            'nama_kasir' => $validated['nama_kasir'],
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'total_transaksi' => $validated['total'],
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

    public function detail($id)
{
    $detail = \DB::table('detail_transaksi')
        ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id')
        ->where('detail_transaksi.id_transaksi', $id)
        ->select(
            'barang.nama_barang as nama_barang',
            'detail_transaksi.jumlah',
            'detail_transaksi.harga_satuan',
            'detail_transaksi.total_harga'
        )
        ->get();

    return response()->json($detail);
}
}
