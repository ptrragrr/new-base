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
  console.log("Data barang mentah:", barang.data.value);
  console.log("Selected Kategori ID:", selectedKategoriId.value);
  console.log("Semua barang:", allBarang);

  if (selectedKategoriId.value && selectedKategoriId.value !== 0) {
    console.log("ID",selectedKategoriId.value)
    console.log("Barang",allBarang)
    const filtered = allBarang.filter(b => b.id_kategori === selectedKategoriId.value,
    );
    console.log("Barang yang difilter:", filtered);
    return filtered;
  }
  return allBarang;
});


const fetchKategori = async () => {
  try {
    const res = await axios.get("/tambah/kategori");
    console.log("Response API kategori:", res.data);
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
            <label for="kategoriSelect" class="form-label fw-bold"
                >Pilih Kategori</label
            >
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
                                    Rp
                                    {{ barang.harga_barang.toLocaleString() }}
                                </p>
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
