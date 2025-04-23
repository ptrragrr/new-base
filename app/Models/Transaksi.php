<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    public function kasir()
{
    return $this->belongsTo(User::class, 'kasir_id');
}

// app/Models/Transaksi.php
public function details()
{
    return $this->hasMany(DetailTransaksi::class, 'id_barang'); // ganti dengan nama FK yang benar
}

    protected $fillable = [
        'nama_kasir',
        'jumlah',
        'metode_pembayaran',
        'total_harga',
        'kode_transaksi',
        'total_transaksi',
    ];

}