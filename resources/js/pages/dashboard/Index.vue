<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'
import axios from 'axios'

const chartRef = ref<HTMLCanvasElement | null>(null)

onMounted(async () => {
  try {
    // Panggil endpoint Laravel
    const response = await axios.get('/sales-data')
    const data = response.data

    // Ambil bulan dan total
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
    const labels = data.map((item: any) => monthNames[item.month - 1])
    const totals = data.map((item: any) => item.total)

    // Tampilkan Chart
    if (chartRef.value) {
      new Chart(chartRef.value, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Total Penjualan per Bulan',
              data: totals,
              backgroundColor: '#504E76',
              barThickness: 50             // Lebar batang (pixel) 
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    }
  } catch (err) {
    console.error('Gagal mengambil data chart:', err)
  }
})
</script>

<template>
  <div>
    <canvas ref="chartRef"></canvas>
  </div>
</template>
