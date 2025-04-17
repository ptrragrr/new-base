<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarangsTable extends Migration
{
    public function up()
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();  // Menambahkan kolom ID sebagai primary key
            $table->string('nama_barang');
            $table->foreignId('id_kategori')->constrained('kategori')->onDelete('cascade');
            $table->decimal('harga_barang', 10, 3);  // Menyimpan harga dalam format desimal
            $table->string('stok_barang');
            // $table->string('foto_barang')->nullable();  // Kolom untuk menyimpan nama file gambar
            $table->timestamps();  // Menambahkan created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang');
    }
}
