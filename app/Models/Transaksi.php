<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kasir',
        'jumlah',
        'metode_pembayaran',
        'total_harga',
        'kode_transaksi',
        'total_transaksi',
    ];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'nama_kasir');
    }

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi')->with('Barang');
    }

}