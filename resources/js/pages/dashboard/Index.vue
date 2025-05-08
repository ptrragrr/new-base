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
    const labels = data.map((item: any) => `Bulan ${item.month}`)
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
              backgroundColor: ['#FFC5A6', ' #DC8E90', '#A97882'] ,
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
