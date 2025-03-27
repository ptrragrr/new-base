<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";

const kurir = ref({
    name: "",
    email: "",
    phone: "",
    photo: "",
    status: "",
    rating: 0,
});

const getProfile = async () => {
    try {
        const { data } = await axios.get("/kurir");

        if (data.data.length > 0) {
            kurir.value = {
                name: data.data[0].name ?? "Tidak tersedia",
                email: data.data[0].email ?? "Tidak tersedia",
                phone: data.data[0].phone ?? "-",
                photo: data.data[0].photo ? "/storage/" + data.data[0].photo : "/default-avatar.png",
                status: data.data[0].status ?? "tidak aktif",
                rating: data.data[0].rating ?? 0, // Tambahkan default jika kosong
            };
        } else {
            toast.error("Data kurir tidak ditemukan");
        }

    } catch (error) {
        toast.error("Gagal mengambil data kurir");
        console.error(error);
    }
};


onMounted(() => {
    getProfile();
});
</script>

<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profil Kurir</h3>
        </div>
        <div class="card-body text-center">
            <img :src="kurir.photo" class="rounded-circle mb-3" width="120" height="120" alt="Foto Kurir">
            <h4>{{ kurir.name }}</h4>
            <p class="text-muted">{{ kurir.email }}</p>
            <p>Nomor Telepon: {{ kurir.phone }}</p>
            <p>Status: <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">{{ kurir.status }}</span></p>
            <p>Rating: <span class="fw-bold">{{ kurir.rating }} / 5</span></p>
        </div>
    </div>
</template>

<style scoped>
.text-muted {
    color: #376186;
    /* color: #6c757d; */
}
</style>
