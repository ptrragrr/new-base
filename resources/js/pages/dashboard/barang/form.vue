<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Barang, Kategori } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useKategori } from "@/services/useKategori";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const barang = ref<Barang>({} as Barang);
const formRef = ref();
const kategoriOptions = ref<{ id: number; nama: string }[]>([]);
const foto_barang = ref<File[]>([]);
const fileTypes = ["image/png", "image/jpeg", "image/jpg"];

const formSchema = Yup.object().shape({
    nama_barang: Yup.string().required("Nama harus diisi"),
    id_kategori: Yup.string().required("Kategori harus dipilih"),
    harga_barang: Yup.number().required("Masukkan harga"),
    stok_barang: Yup.number().required("Stok harus di isi"),
    foto_barang: Yup.mixed().nullable(),
});

const previewImage = computed(() => {
  if (!foto_barang.value.length) return null;

  const first = foto_barang.value[0];

  if (typeof first === "string") {
    // URL lama dari database
    return first;
  }

  if (first && first.file instanceof File) {
    // File baru dari upload
    return URL.createObjectURL(first.file);
  }

  return null;
});

function getEdit() {
    console.log('ditekan');
    block(document.getElementById("form-barang"));
    ApiService.get("tambah/barang", props.selected)
        .then(({ data }) => {
            console.log(data);
            barang.value = data.barang;
            foto_barang.value = data.product.foto_barang
                ? ["/storage/" + data.product.foto_barang]
                : [];
            console.log("Kategori Produk:", barang.value.id_kategori);
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-barang"));
        });
    console.log("Selected ID:", props.selected);
}

function submit() {
    console.log('submit')
    const formData = new FormData();
    formData.append("nama_barang", barang.value.nama_barang);
    formData.append("id_kategori", barang.value.id_kategori);
    formData.append("harga_barang", barang.value.harga_barang);
    formData.append("stok_barang", barang.value.stok_barang);

    if (foto_barang.value.length) {
        formData.append("foto_barang", foto_barang.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-barang"));
    axios({
        method: "post",
        url: props.selected
            ? `/tambah/barang/${props.selected}`
            : "/tambah/barang/store",
        data: formData,

        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-barang"));
        });
}

const kategori = useKategori();
const kategoris = computed(() =>
    kategori.data.value?.map((item: Kategori) => ({
        id: item.id,
        text: item.nama,
    }))
);

function getKategori() {
    ApiService.get("/kategori-barang")
        .then(({ data }) => {
            kategoriOptions.value = data.kategori;
        })
        .catch((err) => {
            toast.error("Gagal memuat kategori");
        });
}

onMounted(async () => {
    console.log(props.selected);
    if (props.selected) {
        getEdit();
    }
});

watch(
    () => props.selected,
    () => {
        if (props.selected) {
            getEdit();
        }
    }
);
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-barang"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} User</h2>
            <button
                type="button"
                class="btn btn-sm btn-light-danger ms-auto"
                @click="emit('close')"
            >
                Batal
                <i class="la la-times-circle p-0"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nama Barang
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="nama_barang"
                            autocomplete="off"
                            v-model="barang.nama_barang"
                            placeholder="Masukkan Nama"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="nama_barang" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group iki -->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Kategori
                        </label>
                        <Field
                            name="id_kategori"
                            type="hidden"
                            v-model="barang.id_kategori"
                        >
                            <select2
                                placeholder="Pilih kategori"
                                class="form-select-solid"
                                :options="kategoris"
                                name="id_kategori"
                                v-model="barang.id_kategori"
                            >
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="id_kategori" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Harga Barang
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="harga_barang"
                            autocomplete="off"
                            v-model="barang.harga_barang"
                            placeholder="Masukkan harga"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="harga_barang" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Stok Barang
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="stok_barang"
                            autocomplete="off"
                            v-model="barang.stok_barang"
                            placeholder="Masukkan stok barang"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="stok_barang" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
  <!--begin::Input group-->
  <div class="fv-row mb-7">
    <label class="form-label fw-bold fs-6">
      Foto Barang
    </label>
    <!--begin::Input-->
    <file-upload
      :files="foto_barang"
      :accepted-file-types="fileTypes"
      required
      v-on:updatefiles="(file) => (foto_barang = file)"
    ></file-upload>
    <!--end::Input-->

    <!-- Preview gambar -->
<!-- <div v-if="previewImage" class="mt-3">
  <img
    :src="previewImage"
    alt="Preview Foto Barang"
    style="max-width: 200px; max-height: 200px; object-fit: contain; border-radius: 5px;"
  />
</div> -->

    <div class="fv-plugins-message-container">
      <div class="fv-help-block">
        <ErrorMessage name="foto_barang" />
      </div>
    </div>
  </div>
  <!--end::Input group-->
</div>
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
