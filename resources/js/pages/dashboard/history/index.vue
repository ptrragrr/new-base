<script setup lang="ts">
import { h, ref } from "vue";
import { useDelete } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Transaksi } from "@/types";
import { useAuthStore } from "@/stores/auth";
import axios from "axios";

const column = createColumnHelper<Transaksi>();
const paginateRef = ref<any>(null);
const user = useAuthStore().user;

const selectedTransaksi = ref<Transaksi | null>(null)
const detailItems = ref<any[]>([])

async function handleShowDetail(trx: Transaksi) {
  selectedTransaksi.value = trx;
  const res = await axios.get(`/history/detail/${trx.id}`);
  detailItems.value = res.data;
  console.log(detailItems) // Ambil array detail saja
}

function rupiah(angka: number | string): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(Number(angka));
}
  
const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("kode_transaksi", { header: "Kode Transaksi" }),
  column.accessor("nama_kasir", { header: "Nama Kasir" }),
  column.display({
    id: "aksi",
    header: "Aksi",
    cell: ({ row }) =>
      h(
        "button",
        {
          class: "btn btn-sm btn-icon btn-info w-50",
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
    <h2 class="text-lg font-bold">Riwayat Transaksi Pembayaran</h2>
    <a
      href="/transaksi/download-pdf"
      target="_blank"
      class="flex items-center btn btn-sm btn-icon btn-info w-35"
    >
     🖨️
    </a>
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
  class="mt-6 p-4 border rounded bg-blue-100 shadow"
>
  <h3 class="text-lg font-bold mb-2 ">Detail Transaksi</h3>
  <p><strong>Kode Transaksi:</strong> {{ selectedTransaksi.kode_transaksi }}</p>
  <p><strong>Nama Kasir:</strong> {{ selectedTransaksi.nama_kasir }}</p>
  <p><strong>Tanggal Transaksi:</strong> {{ formatTanggal(selectedTransaksi.created_at) }}</p>

  <table class="w-full mt-4 text-sm border">
    <thead class="bg-blue-200 text-left">
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
        <td class="p-2">{{ rupiah(item.harga_satuan) }}</td>
        <td class="p-2">{{ rupiah(item.total_harga) }}</td>
      </tr>
    </tbody>
  </table>

  <p class="mt-2 text-right font-semibold">
    Total Bayar: {{
      rupiah(detailItems.reduce((acc, item) => acc + Number(item.total_harga), 0)) 
    }}
  </p>

  <button
    class="btn btn-sm btn-icon btn-info w-50"
    @click="selectedTransaksi = null"
  >
    Tutup
  </button>
</div>
      </div>
    </div>
</template>
