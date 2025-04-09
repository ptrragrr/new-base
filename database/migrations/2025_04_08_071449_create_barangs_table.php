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
            $table->string('Nama_barang');
            $table->string('Kategori_barang');
            $table->decimal('Harga_barang', 10, 2);  // Menyimpan harga dalam format desimal
            $table->string('Stok_barang');
            // $table->string('foto_barang')->nullable();  // Kolom untuk menyimpan nama file gambar
            $table->timestamps();  // Menambahkan created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang');
    }
}
