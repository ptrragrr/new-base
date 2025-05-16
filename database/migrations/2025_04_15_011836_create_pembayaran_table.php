<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->string('nama_kasir');
            $table->string('metode_pembayaran');  // ini belum ada di migrasi kamu, tapi dipakai di controller
            $table->decimal('total_transaksi', 12, 2);
            $table->decimal('bayar', 12, 2)->nullable();
            $table->decimal('kembalian', 12, 2);
            $table->date('tanggal')->default(DB::raw('CURRENT_DATE')); // tanggal, default hari ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
