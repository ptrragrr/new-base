<script setup lang="ts">
import { ref, computed } from "vue";
import FormTransaksi from "./Form.vue";
import type { Barang } from "@/types";
import { useBarang } from "@/services/useBarang";

const selectedBarang = ref<Barang | null>(null);
const barang = useBarang();

const barangList = computed(() => barang.data.value || []);

const addBarang = (barang: Barang) => {
  selectedBarang.value = barang;
};

const loadBarang = () => {
  barang.refetch();
};
</script>

<template>
  <!-- <div class="row">
    <div class="col-lg-8">
      <div class="card card-custom">
        <div class="card-header">
          <h3 class="card-title">Daftar Barang</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div
              v-for="barang in barangList"
              :key="barang.id"
              class="col-md-4 mb-4"
              @click="addBarang(barang)"
              style="cursor: pointer"
            >
              <div class="card h-100">
                <div class="card-body text-center">
                  <h5 class="fw-bold">{{ barang.nama_barang }}</h5>
                  <p>Rp {{ barang.harga_barang.toLocaleString() }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> -->

    <div class="container-fluid px-0">
      <FormTransaksi :selectedBarang="selectedBarang" @refresh="loadBarang" />
    </div>
  <!-- </div> -->
</template>
