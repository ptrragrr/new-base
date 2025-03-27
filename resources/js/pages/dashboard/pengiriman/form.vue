<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Pengiriman } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const pengiriman = ref<Pengiriman>({} as Pengiriman);
const formRef = ref();

const formSchema = Yup.object().shape({
  no_resi: Yup.string().required("No. Resi harus diisi"),
  penerima: Yup.string().required("Nama penerima harus diisi"),
  alamat: Yup.string().required("Alamat harus diisi"),
  kurir: Yup.string().required("Pilih kurir"),
  status: Yup.string().required("Pilih status pengiriman"),
});

function getEdit() {
  block(document.getElementById("form-pengiriman"));
  ApiService.get("pengiriman", props.selected)
    .then(({ data }) => {
      pengiriman.value = data.pengiriman;
    })
    .catch((err: any) => {
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-pengiriman"));
    });
}

function submit() {
  const formData = new FormData();
  formData.append("no_resi", pengiriman.value.no_resi);
  formData.append("penerima", pengiriman.value.penerima);
  formData.append("alamat", pengiriman.value.alamat);
  formData.append("kurir", pengiriman.value.kurir);
  formData.append("status", pengiriman.value.status);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-pengiriman"));
  axios({
    method: "post",
    url: props.selected ? `/pengiriman/${props.selected}` : "/pengiriman/store",
    data: formData,
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data pengiriman berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error("Gagal menyimpan data: " + err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-pengiriman"));
    });
}

onMounted(() => {
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
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-pengiriman" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Pengiriman</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">No. Resi</label>
            <Field class="form-control" type="text" name="no_resi" v-model="pengiriman.no_resi" placeholder="Masukkan No. Resi" />
            <ErrorMessage name="no_resi" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Penerima</label>
            <Field class="form-control" type="text" name="penerima" v-model="pengiriman.penerima" placeholder="Masukkan Nama Penerima" />
            <ErrorMessage name="penerima" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Alamat</label>
            <Field class="form-control" type="text" name="alamat" v-model="pengiriman.alamat" placeholder="Masukkan Alamat" />
            <ErrorMessage name="alamat" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Kurir</label>
            <Field class="form-control" type="text" name="kurir" v-model="pengiriman.kurir" placeholder="Masukkan Nama Kurir" />
            <ErrorMessage name="kurir" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Status</label>
            <Field as="select" class="form-select" name="status" v-model="pengiriman.status">
              <option value="dikemas">Dikemas</option>
              <option value="dikirim">Dikirim</option>
              <option value="diterima">Diterima</option>
            </Field>
            <ErrorMessage name="status" class="text-danger" />
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
