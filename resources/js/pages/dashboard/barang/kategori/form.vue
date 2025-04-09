<script setup lang="ts">
import { ref } from "vue";
import { Field, ErrorMessage, Form as VForm } from "vee-validate";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const emit = defineEmits(["close", "refresh"]);

const kategori = ref({ nama_kategori: "" });
const formRef = ref();

const formSchema = Yup.object().shape({
    nama_kategori: Yup.string().required("Nama kategori harus diisi"),
});

function submit() {
    axios
        .post("/tambah/kategori/store", kategori.value)
        .then(() => {
            toast.success("Kategori berhasil ditambahkan");
            emit("refresh");
            emit("close");
            formRef.value.resetForm();
        })
        .catch((err) => {
            if (err.response?.data?.errors) {
                formRef.value.setErrors(err.response.data.errors);
            }
            toast.error("Gagal menambahkan kategori");
        });
}
</script>

<template>
    <VForm
        @submit="submit"
        :validation-schema="formSchema"
        ref="formRef"
        class="card"
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
            <div class="mb-4">
                <label class="form-label fw-bold fs-6 required">Nama Kategori</label>
                <Field
                    name="nama_kategori"
                    v-model="kategori.nama_kategori"
                    class="form-control form-control-solid"
                    placeholder="Contoh: Elektronik"
                />
                <div class="fv-help-block text-danger">
                    <ErrorMessage name="nama_kategori" />
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
        </div>
    </VForm>
</template>
