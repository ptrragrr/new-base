<?php
// app/Models/Kategori.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori'];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function store(Kategori $request)
{
    $request->validate([
        'nama_kategori' => 'required|string|max:255',
    ]);

    Kategori::create([
        'nama_kategori' => $request->nama_kategori,
    ]);

    return response()->json(['message' => 'Kategori berhasil ditambahkan']);
}
}
