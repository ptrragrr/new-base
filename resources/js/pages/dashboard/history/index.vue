<script setup lang="ts">
import { h, ref } from "vue";
import { useDelete } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Transaksi } from "@/types";
import axios from "axios";

const column = createColumnHelper<Transaksi>();
const paginateRef = ref<any>(null);

const selectedTransaksi = ref<Transaksi | null>(null)
const detailItems = ref<any[]>([])

async function handleShowDetail(trx: Transaksi) {
  selectedTransaksi.value = trx;
  const res = await axios.get(`/history/detail/${trx.id}`);
  detailItems.value = res.data;
  console.log(detailItems) // Ambil array detail saja
}
  
const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("kode_transaksi", { header: "Kode Transaksi" }),
  column.accessor("nama_kasir", { header: "Nama Kasir" }),
  column.display({
    id: "aksi",
    header: "Aksi",
    cell: ({ row }) =>
      h(
        "button",
        {
          class: "text-blue-500 hover:underline",
          onClick: () => handleShowDetail(row.original),
        },
        "Lihat Detail"
      ),
  }),
];

function formatTanggal(tanggal: string) {
  const options: Intl.DateTimeFormatOptions = {
    day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  };
  return new Date(tanggal).toLocaleDateString('id-ID', options);
}
</script>

<template>
  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">Riwayat Transaksi Pembayaran</h2>
    </div>
    <div class="card-body">
      <paginate
        ref="paginateRef"
        id="table-detail_transaksi"
        url="/history"
        :columns="columns"
      />

      <!-- Detail Transaksi -->
      <div
        v-if="selectedTransaksi"
        class="mt-6 p-4 border rounded bg-gray-50 shadow"
      >
        <h3 class="text-lg font-bold mb-2">Detail Transaksi</h3>
        <p><strong>Kode Transaksi:</strong> {{ selectedTransaksi.kode_transaksi }}</p>
        <p><strong>Nama Kasir:</strong> {{ selectedTransaksi.nama_kasir }}</p>
        <p><strong>Tanggal Transaksi:</strong> {{ formatTanggal(selectedTransaksi.created_at) }}</p>

        <table class="w-full mt-4 text-sm border">
          <thead class="bg-gray-200 text-left">
            <tr>
              <th class="p-2">Nama Barang</th>
              <th class="p-2">Jumlah</th>   
              <th class="p-2">Harga Satuan</th>
              <th class="p-2">Total Harga</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in detailItems" :key="index">
              <td class="p-2">{{ item.nama_barang ?? '-' }}</td>
              <td class="p-2">{{ item.jumlah }}</td>
              <td class="p-2">Rp {{ item.harga_satuan }}</td>
              <td class="p-2">Rp {{ item.total_harga }}</td>
            </tr>
          </tbody>
        </table>

        <p class="mt-2 text-right font-semibold">
          Total Bayar: Rp {{
            detailItems.reduce((acc, item) => acc + Number(item.total_harga), 0)
          }}
        </p>

        <button
          class="mt-4 px-4 py-2 bg-red-500 text-black rounded hover:bg-red-600"
          @click="selectedTransaksi = null"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>
