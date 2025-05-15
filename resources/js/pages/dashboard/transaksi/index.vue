<script setup lang="ts">
import { ref, computed } from "vue";
import FormTransaksi from "./Form.vue";
import type { Barang } from "@/types";
import { useBarang } from "@/services/useBarang";
import { useKategori } from "@/services/useKategori";

const selectedBarang = ref<Barang | null>(null);
const selectedKategoriId = ref<number | null>(null);

const barang = useBarang();
const kategori = useKategori();

const barangList = computed(() => {
  const allBarang = barang.data.value || [];
  if (selectedKategoriId.value) {
    return allBarang.filter(
      (b) => b.kategori?.id === selectedKategoriId.value
    );
  }
  return allBarang;
});

const kategoriList = computed(() => kategori.data.value || []);

const addBarang = (barang: Barang) => {
  selectedBarang.value = barang;
};

const loadBarang = () => {
  barang.refetch();
};
</script>

<template>
  <div class="container-fluid">
    <!-- KATEGORI LIST -->
    <div class="mb-3" style="max-width: 250px;">
  <label for="kategoriSelect" class="form-label fw-bold">Pilih Kategori</label>
  <select
    id="kategoriSelect"
    v-model="selectedKategoriId"
    class="form-select"
  >
    <option :value="null">Semua Kategori ({{ barang.data.value?.length || 0 }} items)</option>
    <option
      v-for="kat in kategoriList"
      :key="kat.id"
      :value="kat.id"
    >
      {{ kat.nama_kategori }} ({{ kat.barang_count || 0 }} items)
    </option>
  </select>
</div>


    <!-- LAYOUT GRID: Barang di Kiri, Form di Kanan -->
    <div class="row">
      <!-- GRID BARANG (8 kolom) -->
      <div class="col-md-8">
        <div class="row">
          <div
            v-for="barang in barangList"
            :key="barang.id"
            class="col-md-4 mb-4"
            @click="addBarang(barang)"
            style="cursor: pointer"
          >
            <div class="card h-100 text-center">
              <img
                v-if="barang.foto_barang"
                :src="`/storage/${barang.foto_barang}`"
                alt="Foto Barang"
                class="card-img-top p-2"
                style="height: 120px; object-fit: contain"
              />
              <div class="card-body">
                <h6>{{ barang.nama_barang }}</h6>
                <p class="mb-0 text-success fw-bold">
                  Rp {{ barang.harga_barang.toLocaleString() }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FORM TRANSAKSI (4 kolom) -->
      <div class="col-md-4">
        <FormTransaksi :selectedBarang="selectedBarang" @refresh="loadBarang" />
      </div>
    </div>
  </div>
</template>
