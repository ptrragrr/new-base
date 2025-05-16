<script setup lang="ts">
import { ref, watch, computed } from "vue";
import type { Barang } from "@/types";
import axios from 'axios';

const props = defineProps<{
    selectedBarang: Barang | null;
}>();

const emit = defineEmits(["refresh"]);

const jumlah = ref(1);
const keranjang = ref<{ barang: Barang; jumlah: number }[]>([]);
const uangBayar = ref<number | null>(null);

const kembalian = computed(() => {
    if (uangBayar.value === null) return null;
    return uangBayar.value - totalHarga.value;
});

watch(() => props.selectedBarang, () => {
    jumlah.value = 1;
});

const tambahKeKeranjang = () => {
    if (!props.selectedBarang || jumlah.value < 1) return;

    const idx = keranjang.value.findIndex(
        (item) => item.barang.id === props.selectedBarang!.id
    );

    if (idx !== -1) {
        keranjang.value[idx].jumlah += jumlah.value;
    } else {
        keranjang.value.push({
            barang: props.selectedBarang,
            jumlah: jumlah.value,
        });
    }

    jumlah.value = 1;
    emit("refresh");
};

const tambahJumlah = (id: number) => {
    const idx = keranjang.value.findIndex((item) => item.barang.id === id);
    if (idx !== -1) {
        keranjang.value[idx].jumlah++;
    }
};

const kurangJumlah = (id: number) => {
    const idx = keranjang.value.findIndex((item) => item.barang.id === id);
    if (idx !== -1 && keranjang.value[idx].jumlah > 1) {
        keranjang.value[idx].jumlah--;
    }
};

const batalTransaksi = () => {
    keranjang.value = [];
    uangBayar.value = null;
    jumlah.value = 1;
    emit("refresh");
};

const totalHarga = computed(() => {
    return keranjang.value.reduce(
        (total, item) => total + item.barang.harga_barang * item.jumlah,
        0
    );
});

const simpanSemuaTransaksi = async () => {
  if (uangBayar.value === null || uangBayar.value < totalHarga.value) {
    alert("Uang bayar kurang atau belum diisi");
    return;
  }

  const token = localStorage.getItem("token"); // pastikan token ada

  const payload = {
    nama_kasir: "Kasir Contoh",
    metode_pembayaran: "Tunai",
    keranjang: JSON.stringify(
      keranjang.value.map((item) => ({
        id_barang: item.barang.id,
        jumlah: item.jumlah,
        harga_barang: item.barang.harga_barang,
        total_harga: item.barang.harga_barang * item.jumlah,
      }))
    ),
    total: totalHarga.value,
    bayar: uangBayar.value,
    kembalian: kembalian.value,
  };

  try {
    const response = await axios.post("/transaksi/store", payload, {
  headers: {
    Authorization: `Bearer ${token}`,
  },
});

    if (response.data.success) {
      alert("Transaksi berhasil disimpan");
      keranjang.value = [];
      uangBayar.value = null;
      emit("refresh");
    } else {
      alert("Gagal menyimpan transaksi: " + response.data.message);
    }
  } catch (error: any) {
    alert("Gagal menyimpan transaksi: " + error.message);
    console.error(error);
  }
};
</script>

<template>
    <div class="card card-custom">
        <div class="card-header">
            <h3 class="card-title">Form Transaksi</h3>
        </div>

        <div class="card-body">
            <div v-if="props.selectedBarang">
                <h5>{{ props.selectedBarang.nama_barang }}</h5>
                <p>
                    Harga: Rp
                    {{ props.selectedBarang.harga_barang.toLocaleString() }}
                </p>

                <button
                    class="btn btn-primary mt-3"
                    @click="tambahKeKeranjang"
                    :disabled="props.selectedBarang.stok_barang === 0"
                >
                    {{
                        props.selectedBarang.stok_barang === 0
                            ? "Stok Habis"
                            : "Tambah ke Keranjang"
                    }}
                </button>
            </div>
            <div v-else class="text-muted mb-3">
                Pilih barang terlebih dahulu.
            </div>

            <div v-if="keranjang.length > 0" class="mt-4">
                <h5>Keranjang</h5>
                <ul class="list-group">
                    <li
                        v-for="item in keranjang"
                        :key="item.barang.id"
                        class="list-group-item d-flex justify-content-between align-items-center"
                    >
                        <div>
                            {{ item.barang.nama_barang }} x {{ item.jumlah }}
                            <div
                                class="btn-group btn-group-sm ms-3"
                                role="group"
                            >
                                <button
                                    class="btn btn-outline-secondary"
                                    @click="kurangJumlah(item.barang.id)"
                                >
                                    -
                                </button>
                                <button
                                    class="btn btn-outline-secondary"
                                    @click="tambahJumlah(item.barang.id)"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        <span>
                            Rp
                            {{
                                (
                                    item.barang.harga_barang * item.jumlah
                                ).toLocaleString()
                            }}
                        </span>
                    </li>
                </ul>

                <div class="mt-3 fw-bold">
                    Total: Rp {{ totalHarga.toLocaleString() }}
                </div>

                <div class="mt-3">
                    <label for="uangBayar" class="form-label">Uang Bayar</label>
                    <input
                        id="uangBayar"
                        type="number"
                        class="form-control"
                        v-model.number="uangBayar"
                        min="0"
                        placeholder="Masukkan uang pembayaran"
                    />
                </div>

                <div class="mt-2 fw-bold" v-if="uangBayar !== null">
                    Kembalian: Rp
                    {{ kembalian !== null ? kembalian.toLocaleString() : "0" }}
                </div>

                <div class="d-flex mt-3">
                    <button class="btn btn-danger me-2" @click="batalTransaksi">
                        Batal
                    </button>
                    <button
                        class="btn btn-success"
                        @click="simpanSemuaTransaksi"
                    >
                        Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
