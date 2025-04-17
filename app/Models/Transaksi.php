<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    protected $fillable = [
        'nama_kasir',
        'id_barang',
        'jumlah',
        'total_harga',
        'tanggal',
    ];
}
