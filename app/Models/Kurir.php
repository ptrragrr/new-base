<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'photo',
        'password',
        'rating',
        'status',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relasi dengan model Pengiriman
    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'kurir_id');
    }
    protected $table = 'kurir';
}
