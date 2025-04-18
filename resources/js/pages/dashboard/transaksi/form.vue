<script setup lang="ts">
import { ref, watch, computed } from "vue";
import * as yup from "yup";
import { toast } from "vue3-toastify";
import axios from "@/libs/axios";
import { block, unblock } from "@/libs/utils";
import { useBarang } from "@/services/useBarang";
import type { Barang } from "@/types";
import { Form as VForm, Field, ErrorMessage } from "vee-validate";

const props = defineProps({
  selectedBarang: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "refresh"]);
const grandTotalValue = computed(() => grandTotal.value);

const transaksis = ref({
  nama_kasir: "",
  id_barang: null,
  jumlah: 1,
  total_harga: 0,
  metode_pembayaran: "",
  keranjang: [] as any[],
});

const formRef = ref();
const barang = useBarang();

const barangs = computed(() =>
  barang.data.value?.map((item: Barang) => ({
    id: String(item.id),
    text: item.nama_barang,
  }))
);

const selectedBarangHarga = computed(() => {
  const selected = barang.data.value?.find(
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

const formSchema = yup.object({
  nama_kasir: yup.string().required("Nama kasir wajib diisi"),
  metode_pembayaran: yup.string().required("Metode pembayaran wajib dipilih"),
  keranjang: yup.array().min(1, "Minimal satu barang harus ditambahkan"),
});

watch(
  [() => transaksis.value.id_barang, () => transaksis.value.jumlah],
  () => {
    const selected = barang.data.value?.find(
      (b) => b.id === transaksis.value.id_barang
    );
    if (selected) {
      transaksis.value.total_harga =
        selected.harga_barang * transaksis.value.jumlah;
    } else {
      transaksis.value.total_harga = 0;
    }
  }
);

function adjustQuantity(index: number, amount: number) {
  const item = transaksis.value.keranjang[index];
  if (!item) return;

  item.jumlah = Math.max(1, item.jumlah + amount);
  item.total_harga = item.harga_barang * item.jumlah;
}

function tambahKeKeranjang() {
  const selected = barang.data.value?.find(
    (b) => b.id === transaksis.value.id_barang
  );
  if (!selected) return;

  const existingItem = transaksis.value.keranjang.find(
    (item: any) => item.id_barang === selected.id
  );

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
    });
  }

  transaksis.value.id_barang = null;
  transaksis.value.jumlah = 1;
  transaksis.value.total_harga = 0;
}

function formatRupiah(angka: number): string {
  return "Rp " + angka.toLocaleString("id-ID");
}

function removeProduct(index: number) {
  transaksis.value.keranjang.splice(index, 1);
}

function submit(values: any, { resetForm }: any) {
  const formData = new FormData();
  formData.append("nama_kasir", values.nama_kasir);
  formData.append("metode_pembayaran", values.metode_pembayaran);
  formData.append("keranjang", JSON.stringify(transaksis.value.keranjang));
  formData.append("total", String(grandTotal.value));

  const formEl = document.getElementById("form-transaksi");
  block(formEl);

  axios({
    method: "post",
    url: "/transaksi/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Transaksi berhasil disimpan");
      resetForm();
      transaksis.value.keranjang = [];
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || "Terjadi kesalahan.");
    })
    .finally(() => {
      unblock(formEl);
    });
}
</script>

<template>
  <div class="row">
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
          <div class="mb-3">
            <label class="form-label">Barang</label>
            <select class="form-select" v-model.number="transaksis.id_barang">
              <option disabled value="">Pilih barang</option>
              <option v-for="item in barangs" :key="item.id" :value="Number(item.id)">
                {{ item.text }}
              </option>
            </select>
            <ErrorMessage name="id_barang" class="text-danger small" />
            <ErrorMessage name="metode_pembayaran" class="text-danger small" />
            <ErrorMessage name="keranjang" class="text-danger small" />
          </div>

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

          <!-- Metode Pembayaran -->
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <Field as="select" name="metode_pembayaran" class="form-control" v-model="transaksis.metode_pembayaran">
              <option disabled value="">Pilih metode</option>
              <option value="cash">Cash</option>
              <option value="debit">Debit</option>
              <option value="qris">QRIS</option>
            </Field>
            <ErrorMessage name="metode_pembayaran" class="text-danger small" />
          </div>
        </div>

        <!-- Submit -->
        <div class="card-footer">
          <button type="submit" class="btn btn-primary w-100">Simpan Transaksi</button>
        </div>
      </VForm>
    </div>
  </div>
</div>
</template>

<style>
.ringkasan-wrapper {
  width: 100%;
  max-width: 100%;
}
</style>
