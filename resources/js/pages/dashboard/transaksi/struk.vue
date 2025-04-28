<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

// Data transaksi
const transaksi = ref<any>(null);  // Inisialisasi variabel transaksi

// Fungsi untuk memformat angka menjadi format Rupiah
function formatRupiah(angka: number): string {
  return "Rp " + angka.toLocaleString("id-ID");
}

// Mengambil data dari query saat komponen dimuat
onMounted(() => {
  const route = useRoute();  // Menggunakan useRoute untuk mengakses query
  const query = route.query;  // Mendapatkan query parameters dari URL
  
  // Mengambil data transaksi dari query parameters
  try {
    if (query.nama_kasir && query.total_harga && query.bayar && query.kembalian && query.detail_produk) {
      transaksi.value = {
        nama_kasir: query.nama_kasir as string,
        metode_pembayaran: query.metode_pembayaran as string || 'Tidak ada',
        total_harga: Number(query.total_harga),
        bayar: Number(query.bayar),
        kembalian: Number(query.kembalian),
        detail_produk: JSON.parse(query.detail_produk as string) // Mengparse detail produk yang berupa JSON string
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
      <div class="card-header align-items-center">
        <h2 class="text-lg font-bold">Struk Transaksi</h2>
</div>

      <div class="card-body" v-if="transaksi">
        <p><strong>Nama Kasir:</strong> {{ transaksi.nama_kasir }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ transaksi.metode_pembayaran || 'Tidak ada' }}</p>
        
        <hr />

        <h5>Daftar Barang</h5>
        <div v-for="(item, index) in transaksi.detail_produk" :key="index" class="border-bottom py-2">
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
      </div>

      <div class="card-body text-center" v-else>
        <em>Memuat struk transaksi...</em>
      </div>
    </div>
  </div>
</template>

