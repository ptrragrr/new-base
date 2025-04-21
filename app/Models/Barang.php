<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    // Definisikan relasi one-to-many
    
    protected $table = "barang";

    protected $fillable = ['id_kategori', 'nama_barang', 'kategori_barang', 'harga_barang', 'stok_barang'];

    public function transaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_barang');
    }

    public function kategori()
    {
        return $this->hasMany(Kategori::class, 'id_kategori');
    }
}
