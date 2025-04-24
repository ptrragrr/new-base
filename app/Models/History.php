<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;    

    public function transaksi()
    {
        return $this->belongsTo(transaksi::class, 'kode_transaksi', 'tanggal', 'total_harga', 'nama_barang');
    }

    protected $table = 'transaksis'; // pastikan sama dengan nama tabel di DB

    protected $fillable = [
        'kode_transaksi',
        'nama_kasir',
        'metode_pembayaran',
    ];    

    // protected $casts = [
    //     'keranjang' => 'array', // biar langsung jadi array saat diakses
    //     'total' => 'float',
    // ];
}

