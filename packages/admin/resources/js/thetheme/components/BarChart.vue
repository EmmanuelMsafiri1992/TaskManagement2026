<template>
  <canvas :id="id"></canvas>
</template>

<script setup lang="ts">
  import { onMounted, onUpdated } from 'vue'
  import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    LinearScale,
    Tooltip,
    Legend,
  } from 'chart.js'

  const props = defineProps<{
    id: string
    data: any
    labels?: string[]
    tooltipLabel?: string
  }>()

  Chart.register(
    BarElement,
    BarController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
  )

  let myChart: any = {}

  onMounted(function () {
    const ctx = document.getElementById(props.id) as HTMLCanvasElement
    myChart = new Chart(ctx, config)
  })

  onUpdated(function () {
    if (myChart.data) {
      myChart.data.datasets = data.datasets
      myChart.data.labels = props.labels || []
      myChart.update()
    }
  })

  const data = {
    labels: props.labels || [],
    datasets: props.data || [],
  }

  const config: any = {
    type: 'bar',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
        },
        tooltip: {
          usePointStyle: true,
        },
      },
      scales: {
        x: {
          beginAtZero: true,
        },
        y: {
          beginAtZero: true,
        },
      },
    },
  }
</script>
