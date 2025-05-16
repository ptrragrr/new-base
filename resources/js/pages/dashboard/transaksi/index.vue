<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from "@/libs/axios";
import FormTransaksi from "./Form.vue";
import type { Barang } from "@/types";
import { useBarang } from "@/services/useBarang";

const selectedBarang = ref<Barang | null>(null);
const selectedKategoriId = ref<number | 0>(0);
const kategoriList = ref([]);

const barang = useBarang();

const barangList = computed(() => {
  const allBarang = barang.data.value || [];
  if (selectedKategoriId.value && selectedKategoriId.value !== 0) {
    return allBarang.filter((b) => b.id_kategori === selectedKategoriId.value);
  }
  return allBarang;
});

const fetchKategori = async () => {
  try {
    const res = await axios.get("/tambah/kategori");
    kategoriList.value = res.data.data || res.data;
  } catch (error) {
    console.error("Gagal fetch kategori:", error);
  }
};

onMounted(() => {
  fetchKategori();
});

const addBarang = (barang: Barang) => {
  selectedBarang.value = barang;
};

const loadBarang = () => {
  barang.refetch();
};
</script>

<template>
  <div class="container-fluid">
    <div class="mb-3" style="max-width: 250px">
      <label for="kategoriSelect" class="form-label fw-bold">Pilih Kategori</label>
      <select
        id="kategoriSelect"
        v-model="selectedKategoriId"
        class="form-select"
      >
        <option :value="0">
          Semua Kategori ({{ barang.data.value?.length || 0 }} items)
        </option>
        <option
          v-for="kat in kategoriList"
          :key="kat.id"
          :value="kat.id"
        >
          {{ kat.nama }}
        </option>
      </select>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="row">
          <div
            v-for="barang in barangList"
            :key="barang.id"
            class="col-md-4 mb-4"
          >
            <div
              class="card h-100 text-center"
              :class="{ 'kartu-habis': Number(barang.stok_barang) === 0 }"
              @click="Number(barang.stok_barang) > 0 && addBarang(barang)"
            >
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

              <div
                v-if="Number(barang.stok_barang) === 0"
                class="overlay-habis"
              >
                Stok Habis
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <FormTransaksi
          :selectedBarang="selectedBarang"
          @refresh="loadBarang"
        />
      </div>
    </div>
  </div>
</template>

<style>
.card.kartu-habis {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #f8d7da;
  border: 1px solid #dc3545;
  position: relative;
  overflow: hidden;
}

.card.kartu-habis img {
  filter: grayscale(100%);
}

.card.kartu-habis .card-body h6 {
  text-decoration: line-through;
}

.card.kartu-habis .overlay-habis {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #dc3545;
  color: white;
  padding: 4px 8px;
  border-radius: 5px;
  font-size: 0.85rem;
  z-index: 10;
}
</style>
