<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";
import ApiService from "@/core/services/ApiService";


const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const kurir = ref<kurir>({} as kurir);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const photo = ref<any>([]);
const formRef = ref();

const formSchema = Yup.object().shape({
    name: Yup.string().required("Nama kurir harus diisi"),
    email: Yup.string().email("Email harus valid").required("Email harus diisi"),
    phone: Yup.string().required("Nomor Telepon harus diisi"),
    status: Yup.string().required("Pilih status kurir"),
});

function getEdit() {
    block(document.getElementById("form-kurir"));
    ApiService.get("kurir", props.selected)
        .then(({ data }) => {
            console.log('data');
            kurir.value = data.kurir;
            photo.value = data.kurir.photo ? ["/storage/" + data.kurir.photo] : [];
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-kurir"));
        });
}

function submit() {
    const formData = new FormData();
    formData.append("name", kurir.value.name);
    formData.append("email", kurir.value.email);
    formData.append("phone", kurir.value.phone);
    formData.append("status", kurir.value.status);

    if (photo.value.length) {
        formData.append("photo", photo.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-kurir"));
    axios({
        method: "post",
        url: props.selected ? `/kurir/${props.selected}` : "/kurir/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data kurir berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            // toast.error(err.response.data.message);
            toast.error("Gagal menyimpan data: " + err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-kurir"));
        });
}

onMounted(async () => {
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
        id="form-kurir"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kurir</h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal <i class="la la-times-circle p-0"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">Nama Kurir</label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="name" v-model="kurir.name" placeholder="Masukkan Nama" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">Email</label>
                        <Field class="form-control form-control-lg form-control-solid" type="email" name="email" v-model="kurir.email" placeholder="Masukkan Email" />
                        <ErrorMessage name="email" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">Nomor Telepon</label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="phone" v-model="kurir.phone" placeholder="Masukkan Nomor Telepon" />
                        <ErrorMessage name="phone" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">Status</label>
                        <Field as="select" class="form-select form-select-solid" name="status" v-model="kurir.status">
                            <option value="">Pilih Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </Field>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Foto Profil</label>
                        <file-upload :files="photo" :accepted-file-types="fileTypes" v-on:updatefiles="(file) => (photo = file)"></file-upload>
                        <ErrorMessage name="photo" class="text-danger" />
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
        </div>
    </VForm>
</template>
