<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Pengiriman;

class Pengiriman extends Model
{
    use HasFactory;

    // Tentukan tabel yang digunakan
    protected $table = 'pengirimans';

    // Tentukan kolom yang bisa diisi (fillable)
    protected $fillable = [
        'kurir_id',
        'paket',
        'status',
        'tanggal_pengiriman',
        'biaya',
    ];

    /**
     * Relasi ke model Kurir (Setiap pengiriman dimiliki oleh satu kurir).
     */
    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'kurir_id');
    }
}
