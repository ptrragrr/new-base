<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Kategori } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useRole } from "@/services/useRole";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const kategori = ref<Kategori>({} as Kategori);
const formRef = ref();

const formSchema = Yup.object().shape({
    nama: Yup.string().required("Kategori harus diisi"),
    // photo: Yup.mixed().nullable(),
});

function getEdit() {
    console.log('ditekan');
    block(document.getElementById("form-barang"));
    ApiService.get("tambah/kategori", props.selected)
        .then(({ data }) => {
            console.log(data);
            kategori.value = data.kategori;
            // photo.value = data.product.photo
            //     ? ["/storage/" + data.product.photo]
            //     : [];
            // console.log("Kategori Produk:", barang.value.id_category);
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
    const formData = new FormData();
    formData.append("kategori_barang", kategori.value.nama);
    // formData.append("kategori_barang", barang.value.kategori_barang);
    // formData.append("harga_barang", barang.value.harga_barang);
    // formData.append("stok_barang", barang.value.stok_barang);

    // if (photo.value.length) {
    //     formData.append("photo", photo.value[0].file);
    // }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-kategori"));
    axios({
        method: "post",
        url: props.selected
            ? `/tambah/kategori/${props.selected}`
            : "/tambah/kategori/store",
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
            unblock(document.getElementById("form-kategori"));
        });
}

// const role = useRole();
// const roles = computed(() =>
//     role.data.value?.map((item: Role) => ({
//         id: item.id,
//         text: item.full_name,
//     }))
// );

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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kategori</h2>
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
                            Nama Kategori
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="nama"
                            autocomplete="off"
                            v-model="kategori.nama"
                            placeholder="Masukkan Kategori"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="kategori_barang" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <!-- <div class="col-md-6"> -->
                    <!--begin::Input group-->
                    <!-- <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Foto Barang
                        </label>-->
                        <!--begin::Input-->
                        <!-- <file-upload
                            :files="photo"
                            :accepted-file-types="fileTypes"
                            required
                            v-on:updatefiles="(file) => (photo = file)"
                        ></file-upload> -->
                        <!--end::Input-->
                        <!-- <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="photo" />
                            </div>
                        </div>
                    </div> -->
                    <!--end::Input group-->
                <!-- </div> -->
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>