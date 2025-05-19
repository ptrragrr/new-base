<script setup lang="ts">
import { ref, watch, computed } from "vue";
import type { Barang } from "@/types";
import axios from "axios";
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
// import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    selectedBarang: Barang | null;
}>();

const emit = defineEmits(["refresh"]);
const router = useRouter();


const jumlah = ref(1);
const keranjang = ref<{ barang: Barang; jumlah: number }[]>([]);
const uangBayar = ref<number | null>(null);
const uangBayarDisplay = ref("");
const metodePembayaran = ref("Tunai");
// const nama_kasir = ref("Loading...");
const nama_kasir = ref(useAuthStore().user.name);
// const user = usePage().props.value.auth.user;

const kembalian = computed(() => {
    if (uangBayar.value === null) return null;
    return uangBayar.value - totalHarga.value;
});

watch(
    () => props.selectedBarang,
    () => {
        jumlah.value = 1;
    }
);

// Format uangBayarDisplay menjadi angka dan sinkron ke uangBayar
watch(uangBayarDisplay, (val) => {
    const numeric = val.replace(/\D/g, "");
    uangBayar.value = numeric ? parseInt(numeric) : null;
    uangBayarDisplay.value = formatRupiah(numeric);
});

// Sinkronisasi uangBayar → uangBayarDisplay jika uangBayar diubah dari luar
watch(uangBayar, (val) => {
    if (val === null) {
        uangBayarDisplay.value = "";
    } else {
        uangBayarDisplay.value = formatRupiah(val.toString());
    }
});

function formatRupiah(angka: string): string {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(Number(angka || 0));
}

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
    uangBayarDisplay.value = "";
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
    if (
        metodePembayaran.value === "Tunai" &&
        (uangBayar.value === null || uangBayar.value < totalHarga.value)
    ) {
        alert("Uang bayar kurang atau belum diisi");
        return;
    }

    const token = localStorage.getItem("token");

    const payload = {
        nama_kasir: nama_kasir.value,
        metode_pembayaran: metodePembayaran.value,
        keranjang: JSON.stringify(
            keranjang.value.map((item) => ({
                id_barang: item.barang.id,
                jumlah: item.jumlah,
                harga_barang: item.barang.harga_barang,
                total_harga: item.barang.harga_barang * item.jumlah,
            }))
        ),
        total: totalHarga.value,
        bayar:
            metodePembayaran.value === "Tunai"
                ? uangBayar.value
                : totalHarga.value,
        kembalian: metodePembayaran.value === "Tunai" ? kembalian.value : 0,
    };

    try {
        const response = await axios.post("/transaksi/store", payload, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (response.data.success) {
            const detail_produk = keranjang.value.map((item) => ({
                nama_barang: item.barang.nama_barang,
                harga_barang: item.barang.harga_barang,
                jumlah: item.jumlah,
                total_harga: item.barang.harga_barang * item.jumlah,
            }));

            router.push({
                path: "/transaksi/struk",
                query: {
                    nama_kasir: nama_kasir.value,
                    metode_pembayaran: metodePembayaran.value,
                    total_harga: totalHarga.value.toString(),
                    bayar: (metodePembayaran.value === "Tunai"
                        ? uangBayar.value
                        : totalHarga.value
                    )?.toString(),
                    kembalian: (metodePembayaran.value === "Tunai"
                        ? kembalian.value
                        : 0
                    )?.toString(),
                    detail_produk: JSON.stringify(detail_produk),
                },
            });
        } else {
            alert("Gagal menyimpan transaksi: " + response.data.message);
        }
    } catch (error: any) {
        alert("Gagal menyimpan transaksi: " + error.message);
        console.error(error);
    }
    onMounted(async () => {
        const token = localStorage.getItem("token");

        try {
            const res = await axios.get("/api/user", {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            nama_kasir.value = res.data.name || res.data.nama || "Kasir";
        } catch (error: any) {
            console.error("Gagal mengambil data user:", error);
            nama_kasir.value = "Kasir";
        }
    });
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
                        class="d-flex justify-content-between align-items-center"
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
                    <label for="metodePembayaran" class="form-label"
                        >Metode Pembayaran</label
                    >
                    <select
                        id="metodePembayaran"
                        class="form-select"
                        v-model="metodePembayaran"
                    >
                        <option value="Tunai">Tunai</option>
                        <option value="Debit">Debit</option>
                    </select>
                </div>

                <div class="mt-3" v-if="metodePembayaran === 'Tunai'">
                    <label for="uangBayar" class="form-label">Uang Bayar</label>
                    <input
                        id="uangBayar"
                        type="text"
                        class="form-control"
                        v-model="uangBayarDisplay"
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

<style scoped>
/* style global atau scoped */
/* Hapus ini kalau class .keranjang tidak dipakai */
.list-group-item.bg-dark {
  background-color: #212529 !important;
  color: #fff !important;
  border-color: #444 !important;
}
</style>
