<script setup lang="ts">
import { h, ref } from "vue";
import { useDelete } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Transaksi } from "@/types";

const column = createColumnHelper<Transaksi>();
const paginateRef = ref<any>(null);

const { delete: deleteTransaksi } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("kode_transaksi", {
        header: "Kode Transaksi",
    }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("tanggal", {
        header: "Tanggal",
    }),
    column.accessor("total", {
        header: "Total Bayar",
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            // Ganti dengan rute atau modal detail
                            alert(`Lihat detail transaksi ID: ${cell.getValue()}`);
                        },
                    },
                    h("i", { class: "la la-eye fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteTransaksi(`/transaksi/pembayaran/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Riwayat Transaksi Pembayaran</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-transaksi"
                url="/transaksi/pembayaran"
                :columns="columns"
            />
        </div>
    </div>
</template>
