<script setup lang="ts">
import { ref, watch, computed } from "vue";
import * as yup from "yup";
import { toast } from "vue3-toastify";
import axios from "@/libs/axios";
import { block, unblock } from "@/libs/utils";
import { useBarang } from "@/services/useBarang";
import type { Barang } from "@/types";
import { Form as VForm, Field, ErrorMessage } from "vee-validate";
import { useRouter } from "vue-router";


const router = useRouter();
const props = defineProps({
  selectedBarang: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "refresh"]);
const grandTotalValue = computed(() => grandTotal.value);
const selectedMetode = computed(() => formRef.value?.values?.metode_pembayaran || "");


const transaksis = ref({
  nama_kasir: "",
  id_barang: null,
  jumlah: 1,
  total_harga: 0,
  bayar: 0,
  metode_pembayaran: "",
  keranjang: [] as any[],
});

const formRef = ref();
const barang = useBarang();

const barangs = computed(() =>
  barang.data.value?.map((item: Barang) => ({
    id: item.id,
    text: item.nama_barang,
    stok: item.stok_barang,
    harga_barang:item.harga_barang // tambahkan stok
  }))
);

// const barangs = computed(() =>
//   barang.data.value?.map((item: Barang) => ({
//     id: String(item.id),
//     text: item.nama_barang,
//   }))
// );

const selectedBarangHarga = computed(() => {
  const selected = barang.data.value.find(
    (b) => b.id === transaksis.value.id_barang
  );
  return selected?.harga_barang || 0;
});

const grandTotal = computed(() =>
  transaksis.value.keranjang.reduce(
    (sum: number, item: any) => sum + Number(item.total_harga),
    0
  )
);

watch(grandTotal, (newTotal) => {
  console.log("Total:", formatRupiah(newTotal));
});

const kembalian = computed(() => {
  if (selectedMetode.value !== "cash") return 0;
  const kembali = transaksis.value.bayar - grandTotal.value;
  return kembali > 0 ? kembali : 0;
});

const formSchema = yup.object({
  nama_kasir: yup.string().required("Nama kasir wajib diisi"),
  metode_pembayaran: yup
    .string()
    .required("Metode pembayaran wajib dipilih")
    .oneOf(["cash", "debit"]),
  keranjang: yup.array().min(1, "Minimal satu barang harus ditambahkan"),
});

// const formSchema = yup.object({
//   nama_kasir: yup.string().required("Nama kasir wajib diisi"),
//   metode_pembayaran: yup.string().required("Metode pembayaran wajib dipilih"),
//   keranjang: yup.array().min(1, "Minimal satu barang harus ditambahkan"),
// });

watch(
  [() => transaksis.value.id_barang, () => transaksis.value.jumlah],
  () => {
    console.log(transaksis.value.id_barang)
    console.log(barang.data.value)
    const selected = barang.data.value?.find(
      (b) => b.id == transaksis.value.id_barang
    );
    console.log(selected)
    if (selected) {
      transaksis.value.total_harga =
        selected.harga_barang * transaksis.value.jumlah;
    } else {
      transaksis.value.total_harga = 1;
    }
  }
);

function adjustQuantity(index: number, delta: number) {
  const item = transaksis.value.keranjang[index];
  const newJumlah = item.jumlah + delta;

  if (delta > 0 && newJumlah > item.stok_barang) {
    toast.error("Stok barang tidak mencukupi.");
    return;
  }

  if (newJumlah <= 0) {
    transaksis.value.keranjang.splice(index, 1);
  } else {
    item.jumlah = newJumlah;
    item.total_harga = item.harga_barang * item.jumlah;
  }
}
// function adjustQuantity(index: number, amount: number) {
//   const item = transaksis.value.keranjang[index];
//   if (!item) return;

//   item.jumlah = Math.max(1, item.jumlah + amount);
//   item.total_harga = item.harga_barang * item.jumlah;
// }

function goToStruk() {
  router.push({
    name: "struk",
    query: {
      nama_kasir: transaksis.value.nama_kasir,
      metode_pembayaran: transaksis.value.metode_pembayaran,
      total_harga: grandTotal.value,
      bayar: transaksis.value.bayar,
      kembalian: kembalian.value,
      detail_produk: JSON.stringify(transaksis.value.keranjang),
      tanggal: new Date().toLocaleString("id-ID")
    },
  })
}

function tambahKeKeranjang() {
  const selected = barang.data.value?.find(
    (b) => b.id == transaksis.value.id_barang
  );
  if (!selected) return;

  const existingItem = transaksis.value.keranjang.find(
    (item: any) => item.id_barang === selected.id
  );

  const totalJumlah = (existingItem?.jumlah || 0) + transaksis.value.jumlah;

  if (totalJumlah > selected.stok_barang) {
    toast.error("Stok tidak tersedia.");
    return;
  }

  if (existingItem) {
    existingItem.jumlah += transaksis.value.jumlah;
    existingItem.total_harga =
      existingItem.harga_barang * existingItem.jumlah;
  } else {
    transaksis.value.keranjang.push({
      id_barang: selected.id,
      nama_barang: selected.nama_barang,
      harga_barang: selected.harga_barang,
      jumlah: transaksis.value.jumlah,
      total_harga: selected.harga_barang * transaksis.value.jumlah,
      stok_barang: selected.stok_barang, // simpan untuk validasi qty di tempat lain
    });
  }

  transaksis.value.id_barang = null;
  transaksis.value.jumlah = 1;
  transaksis.value.total_harga = 0;
}

// function tambahKeKeranjang() {
//   const selected = barang.data.value?.find(
//     (b) => b.id == transaksis.value.id_barang
//   );
//   if (!selected) return;

//   const existingItem = transaksis.value.keranjang.find(
//     (item: any) => item.id_barang === selected.id
//   );

//   if (existingItem) {
//     existingItem.jumlah += transaksis.value.jumlah;
//     existingItem.total_harga =
//       existingItem.harga_barang * existingItem.jumlah;
//   } else {
//     transaksis.value.keranjang.push({
//       id_barang: selected.id,
//       nama_barang: selected.nama_barang,
//       harga_barang: selected.harga_barang,
//       jumlah: transaksis.value.jumlah,
//       total_harga: selected.harga_barang * transaksis.value.jumlah,
//     });
//   }

  transaksis.value.id_barang = null;
  transaksis.value.jumlah = 1;
  transaksis.value.total_harga = 0;


function syncKeranjangWithLatestBarang() {
  transaksis.value.keranjang = transaksis.value.keranjang.map((item) => {
    const updated = barang.data.value?.find(b => b.id === item.id_barang);
    if (updated) {
      return {
        ...item,
        nama_barang: updated.nama_barang,
        harga_barang: updated.harga_barang,
        total_harga: updated.harga_barang * item.jumlah,
      };
    }
    return item; // fallback kalau barang tidak ditemukan
  });
}

function formatRupiah(angka: number): string {
  return "Rp " + angka.toLocaleString("id-ID");
}

function removeProduct(index: number) {
  transaksis.value.keranjang.splice(index, 1);
}

function batal() {
  formRef.value?.resetForm();
  transaksis.value = {
    nama_kasir: "", 
    id_barang: null,
    jumlah: 1,
    total_harga: 0,
    bayar: 0,
    keranjang: [],
  };
  emit("close");
}

function submit(values: any, { resetForm }: any) {
  const formData = new FormData();

  formData.append("nama_kasir", values.nama_kasir);
  formData.append("metode_pembayaran", values.metode_pembayaran);
  formData.append("keranjang", JSON.stringify(transaksis.value.keranjang));
  formData.append("total", grandTotal.value.toString());
  formData.append("bayar", transaksis.value.bayar.toString());
  formData.append("kembalian", kembalian.value.toString());

  console.log("Submit metode:", values.metode_pembayaran);

  block(formRef.value?.$el); // ✅ Fix di sini

  axios
    .post("/transaksi/store", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(() => {
      toast.success("Transaksi berhasil disimpan");
      goToStruk();
      resetForm();
      transaksis.value.keranjang = [];
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || "Terjadi kesalahan.");
    })
    .finally(() => {
      unblock(formRef.value?.$el); // ✅ Fix di sini
    });
}

// function submit(values: any, { resetForm }: any) {
//   const formData = new FormData();

//   formData.append("nama_kasir", values.nama_kasir); // dari Field
//   formData.append("tanggal_transaksi", values.tanggal_transaksi);
//   formData.append("metode_pembayaran", values.metode_pembayaran); // dari Field
//   formData.append("keranjang", JSON.stringify(transaksis.value.keranjang)); // ⬅️ masih dari transaksis
//   formData.append("total", grandTotal.value.toString());

//   console.log("Submit metode:", values.metode_pembayaran); // debug

//   if (formRef.value instanceof HTMLElement) {
//   block(formRef.value);
// } else {
//   console.warn("formRef.value is not an HTMLElement", formRef.value);
// }

//   // block(formRef.value);

//   axios.post("/transaksi/store", formData, {
//     headers: {
//       "Content-Type": "multipart/form-data",
//     },
//   })
//   .then(() => {
//     emit("close");
//     emit("refresh");
//     toast.success("Transaksi berhasil disimpan");
//     resetForm();
//     transaksis.value.keranjang = [];
//   })
//   .catch((err: any) => {
//     toast.error(err.response?.data?.message || "Terjadi kesalahan.");
//   })
//   .finally(() => {
//     unblock(formRef.value);
//   });
// }

// function submit(values: any, { resetForm }: any) {
//   const formData = new FormData();
// formData.append("nama_kasir", values.nama_kasir);
// formData.append("metode_pembayaran", values.metode_pembayaran);
// formData.append("keranjang", JSON.stringify(transaksis.value.keranjang)); // ⬅️ WAJIB stringify
// formData.append("total", grandTotal.value.toString()); // ⬅️ harus string

// axios.post("/transaksi/store", formData, {
//   headers: {
//     "Content-Type": "multipart/form-data"
//   }
// })
// .then(() => {
//   emit("close");
//   emit("refresh");
//   toast.success("Transaksi berhasil disimpan");
//   resetForm();
//   transaksis.value.keranjang = [];
// })
// .catch((err: any) => {
//   toast.error(err.response?.data?.message || "Terjadi kesalahan.");
// })
// .finally(() => {
//   unblock(formEl);
// });
// }
</script>

<template>
   <div class="container py-4">
    <div class="col-md-12 mx-auto"> <!-- Tambahkan ini -->
      <div class="ringkasan-wrapper">
        <VForm @submit="submit" :validation-schema="formSchema" ref="formRef" id="form-transaksi">
        <div class="card-body px-4 py-3">
          <!-- Nama Kasir -->
          <div class="mb-3">
            <label class="form-label">Nama Kasir</label>
            <Field name="nama_kasir" class="form-control" v-model="transaksis.nama_kasir" />
            <ErrorMessage name="nama_kasir" class="text-danger small" />
          </div>

          <!-- Dropdown Barang -->
           <!-- Dropdown Barang -->
          <div class="mb-3">
            <label class="form-label">Barang</label>
            <Field
              name="id"
              type="hidden"
              v-model="transaksis.id_barang"
          >
              <select2
                  placeholder="Pilih Barang"
                  class="form-select-solid"
                  :options="barangs"
                  name="id"
                  v-model="transaksis.id_barang"
              >
              </select2>
          </Field>
            <ErrorMessage name="id_barang" class="text-danger small" />
          </div>

          <!-- <div class="mb-3">
            <label class="form-label">Barang</label>
            <select class="form-select" v-model.number="transaksis.id_barang">
              <option disabled value="">Pilih barang</option>
              <option
              v-for="item in barangs"
              :key="item.id"
              :value="item.id"
              :disabled="item.stok === 0"
              :style="item.stok === 0 ? 'color: gray; background-color: #f8f9fa;' : ''"
              >
              {{ item.text }} ({{ item.stok === 0 ? 'Stok habis' : 'Stok: ' + item.stok }})
            </option>
          </select>
            <ErrorMessage name="id_barang" class="text-danger small" />
            <ErrorMessage name="metode_pembayaran" class="text-danger small" />
            <ErrorMessage name="keranjang" class="text-danger small" />
          </div> -->

          <!-- Jumlah dan Total -->
          <div v-if="transaksis.id_barang" class="mb-3">
            <label class="form-label">Jumlah</label>
            <div class="input-group">
              <button class="btn btn-outline-secondary" @click="transaksis.jumlah = Math.max(1, transaksis.jumlah - 1)">-</button>
              <input type="number" v-model="transaksis.jumlah" class="form-control" min="1" />
              <button class="btn btn-outline-secondary" @click="transaksis.jumlah++">+</button>
            </div>
            <p class="mt-2">
              Total: {{ formatRupiah(transaksis.total_harga) }}
              <span v-if="selectedBarangHarga && transaksis.jumlah">
                ({{ formatRupiah(selectedBarangHarga) }} x {{ transaksis.jumlah }})
              </span>
            </p>
            <button class="btn btn-success mt-2" type="button" @click="tambahKeKeranjang">Tambah ke Keranjang</button>
          </div>

          <!-- Keranjang -->
          <div class="mb-3" v-if="transaksis.keranjang.length">
            <h5>Keranjang:</h5>
            <div v-for="(item, index) in transaksis.keranjang" :key="item.id_barang" class="border p-2 mb-2 rounded">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <strong>{{ item.nama_barang }}</strong><br />
                  <small>{{ formatRupiah(item.harga_barang) }}</small>
                </div>
                <div>
                  <button class="btn btn-sm btn-light" @click="adjustQuantity(index, -1)">-</button>
                  <span class="mx-2">{{ item.jumlah }}</span>
                  <button class="btn btn-sm btn-light" @click="adjustQuantity(index, 1)">+</button>
                </div>
                <div><strong>{{ formatRupiah(item.total_harga) }}</strong></div>
                <button class="btn btn-sm btn-danger ms-2" @click="removeProduct(index)">X</button>
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="mb-3">
            <label class="form-label">Total Semua Barang:</label>
            <p class="fw-bold">{{ formatRupiah(grandTotalValue) }}</p>
          </div>

          <div class="mb-3" v-if="selectedMetode === 'cash'">
  <label class="form-label">Bayar</label>
  <input type="number" class="form-control" v-model.number="transaksis.bayar" min="0" />
</div>

<!-- Kembalian -->
<div class="mb-3" v-if="selectedMetode === 'cash'">
  <label class="form-label">Kembalian</label>
  <p :class="{ 'text-danger': kembalian < 0, 'fw-bold': true }">
    {{ formatRupiah(kembalian) }}
  </p>
</div>

          <!-- Metode Pembayaran -->
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <Field
  as="select"
  name="metode_pembayaran"
  v-model="transaksis.metode_pembayaran"
  class="form-control"
>
  <option disabled value="">Pilih metode</option>
  <option value="cash">Cash</option>
  <option value="debit">Debit</option>
</Field>

            <!-- <Field as="select" name="metode_pembayaran" class="form-control">
              <option disabled value="">Pilih metode</option>
              <option value="cash">Cash</option>
              <option value="debit">Debit</option>
            </Field> -->
            <ErrorMessage name="metode_pembayaran" class="text-danger small" />
          </div>
        </div>

        <!-- Submit -->
        <div class="card-footer d-flex justify-content-between gap-2">
          <button type="button" class="btn btn-secondary w-50" @click="batal">Batal</button>
          <button type="submit" class="btn btn-primary w-50">Simpan Transaksi</button>
        </div>
        <!-- <div class="card-footer">
          <button type="submit" class="btn btn-primary w-100">Simpan Transaksi</button>
        </div> -->
      </VForm>
    </div>
  </div>
</div>
</template>

<style scoped>
html, body, #app {
  height: 100%;
  margin: 0;
}

.container-fluid {
  padding: 0;
}

.ringkasan-wrapper {
  width: 100%;
  height: 100%;
}

.card-body {
  overflow-y: auto;
}
</style>
