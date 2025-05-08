<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const transaksi = ref<any>(null);

function formatRupiah(angka: number): string {
  return "Rp " + angka.toLocaleString("id-ID");
}

function printStruk() {
  window.print();
}

onMounted(() => {
  const route = useRoute();
  const query = route.query;

  try {
    if (query.nama_kasir && query.total_harga && query.bayar && query.kembalian && query.detail_produk) {
      transaksi.value = {
        nama_kasir: query.nama_kasir as string,
        metode_pembayaran: query.metode_pembayaran as string || 'Tidak ada',
        total_harga: Number(query.total_harga),
        bayar: Number(query.bayar),
        kembalian: Number(query.kembalian),
        detail_produk: JSON.parse(query.detail_produk as string),
        tanggal: new Date().toLocaleString("id-ID")
      };
    } else {
      console.error("Data transaksi tidak lengkap di query!");
    }
  } catch (error) {
    console.error("Gagal parsing detail_produk:", error);
  }
});
</script>

<template>
  <div class="container py-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center no-print">
        <h2 class="text-lg font-bold mb-0">Struk Transaksi</h2>
        <button @click="printStruk" class="btn btn-primary">Print Struk</button>
      </div>

      <div class="card-body" v-if="transaksi">
        <div class="print-area">
          <h4 class="text-center">INDOKU GROCERY</h4>
          <p class="text-center">
            Jl. MANUKAN No. 123<br />
            Telp: 0812-3456-7890
          </p>
          <hr />

          <p><strong>Nama Kasir:</strong> {{ transaksi.nama_kasir }}</p>
          <p><strong>Tanggal:</strong> {{ transaksi.tanggal }}</p>
          <p><strong>Metode Pembayaran:</strong> {{ transaksi.metode_pembayaran }}</p>

          <hr />
          <h5>Daftar Barang</h5>
          <div v-for="(item, index) in transaksi.detail_produk" :key="index" class="py-1 border-bottom">
            <div class="d-flex justify-content-between">
              <div>
                {{ item.nama_barang }}<br />
                <small>{{ formatRupiah(item.harga_barang) }} x {{ item.jumlah }}</small>
              </div>
              <div class="fw-bold">{{ formatRupiah(item.total_harga) }}</div>
            </div>
          </div>

          <hr />
          <p><strong>Total Harga:</strong> {{ formatRupiah(transaksi.total_harga) }}</p>
          <p><strong>Bayar:</strong> {{ formatRupiah(transaksi.bayar) }}</p>
          <p><strong>Kembalian:</strong> {{ formatRupiah(transaksi.kembalian) }}</p>

          <hr />
          <p class="text-center">--- Terima Kasih ---</p>
          <p class="text-center">Barang yang sudah dibeli tidak bisa dikembalikan kecuali atas kesepakatan pihak toko dan pelanggan</p>
        </div>
      </div>

      <div class="card-body text-center" v-else>
        <em>Memuat struk transaksi...</em>
      </div>
    </div>
  </div>
</template>

<style>
.print-area {
  font-family: monospace;
  font-size: 12px;
  width: 80mm;
  padding: 10px;
  margin: 0 auto; /* Ini memastikan struk berada di tengah */
  background: rgb(255, 255, 255);
  color: rgb(0, 0, 0);
}

@media print {
  @page {
    size: 80mm auto;
    margin: 0;
  }

  html, body {
    margin: 0;
    padding: 0;
    background: white;
  }

  body * {
    visibility: hidden;
  }

  .print-area,
  .print-area * {
    visibility: visible;
  }

  .print-area {
    position: absolute;
    left: 50%;
    top: 0;
    transform: translateX(-50%);
  }

  .sidebar,
  .navbar,
  .footer,
  .btn,
  .no-print {
    display: none !important;
  }
}
</style>



