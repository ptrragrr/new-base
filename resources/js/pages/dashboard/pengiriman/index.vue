<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { pengiriman } from "@/types"; // Pastikan tipe data sesuai API

const column = createColumnHelper<pengiriman>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID pengiriman yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit

// Hook untuk menghapus data
const { delete: deletePengiriman } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
  onError: (error) => alert(`Gagal menghapus pengiriman: ${error.message}`),
});

// Definisi kolom tabel pengiriman
const columns = [
  column.accessor("no_resi", { header: "No. Resi" }),
  column.accessor("penerima", { header: "Penerima" }),
  column.accessor("alamat", { header: "Alamat" }),
  column.accessor("kurir", { header: "Kurir" }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) =>
      h(
        "span",
        {
          class: `badge ${cell.getValue() === "dikemas" ? "bg-primary" : "bg-success"}`,
        },
        cell.getValue() === "dikemas" ? "Dikirim" : "Diterima"
      ),
  }),
  column.accessor("id", {
    header: "Aksi",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        // Tombol Edit
        h(
          "button",
          {
            class: "btn btn-sm btn-info",
            onClick: () => {
              selected.value = cell.getValue();
              openForm.value = true;
            },
          },
          h("i", { class: "la la-pencil fs-2" })
        ),
        // Tombol Hapus
        h(
          "button",
          {
            class: "btn btn-sm btn-danger",
            onClick: () => {
            //   if (confirm("Apakah Anda yakin ingin menghapus pengiriman ini?")) {
                deletePengiriman(`/pengiriman/${cell.getValue()}`);
            //   }
            },
          },
          h("i", { class: "la la-trash fs-2" })
        ),
      ]),
  }),
];

// Fungsi untuk refresh data tabel
const refresh = () => paginateRef.value?.refetch();

// Reset selected ID saat form ditutup
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo(0, 0);
});
</script>

<template>
  <!-- Form Tambah/Edit Pengiriman -->
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Pengiriman</h2>
      <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah
        <i class="la la-plus"></i>
      </button>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengiriman" url="/pengiriman" :columns="columns"></paginate>
    </div>
  </div>
</template>
