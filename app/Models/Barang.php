<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    
    use HasFactory;

    protected $table = "barang";

    protected $fillable = ['id_kategori', 'nama_barang', 'kategori_barang', 'harga_barang', 'stok_barang'];
}
