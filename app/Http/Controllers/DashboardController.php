<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;
class DashboardController extends Controller
{
    public function getSalesData()
    {
            $data = Transaksi::selectRaw('MONTH(created_at) as month, SUM(total_transaksi) as total')
                    ->groupBy('month')
                    ->get();
    
            return response()->json($data);
        
    }
}
