<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'id_transaksi',
        'id_barang',
        'jumlah',
        'total_harga',
        'harga_satuan',
    ];

    public function barang(){
        return $this->belongsTo(Barang::class,'id_barang');
    }
}   
