<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Barang } from "@/types";

const column = createColumnHelper<Barang>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteUser } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

function resolveImageUrl(foto: string): string {
    if (!foto) return "";

    // Jika sudah URL absolut
    if (foto.startsWith("http")) return foto;

    // Jika masih nama file atau path lokal
    return `${window.location.origin}${foto.startsWith("/storage") ? foto : "/storage/" + foto}`;
}

const columns = [
    column.accessor("no", {
        header: "No",
    }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("kategori", {
        header: "Kategori Barang",
    }),
    column.accessor("harga_barang", {
    header: "Harga Barang",
    cell: ({ getValue }) => {
        const harga = getValue();
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
        }).format(harga).replace(/,00$/, "");
    },
}),
    column.accessor("foto_barang", {
    header: "Foto Barang",
    cell: ({ getValue }) => {
        const url = resolveImageUrl(getValue());
        return h("img", {
            src: url,
            alt: "Foto Barang",
            style: "width: 60px; height: 60px; object-fit: cover; border-radius: 4px;",
        });
    },
}),
    column.accessor("stok_barang", {
        header: "Stok Barang",
    }),
    // column.accessor("foto_barang", {
    //     header: "Foto Barang",
    // }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteUser(`/tambah/barang/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Menambahkan Barang</h2>
            <button
                type="button"
                class="btn btn-sm btn-primary ms-auto"
                v-if="!openForm"
                @click="openForm = true"
            >
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-barangs"
                url="/tambah/barang"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
