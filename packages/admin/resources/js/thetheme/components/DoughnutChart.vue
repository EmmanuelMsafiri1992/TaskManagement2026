<template>
  <canvas :id="id"></canvas>
</template>

<script setup lang="ts">
  import { onMounted, onUpdated } from 'vue'
  import {
    ArcElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    Tooltip,
  } from 'chart.js'

  const props = defineProps<{
    id: string
    data: number[]
    labels: string[]
    backgroundColors?: string[]
  }>()

  Chart.register(
    ArcElement,
    DoughnutController,
    CategoryScale,
    Tooltip,
    Legend,
  )

  let myChart: any = {}

  const defaultColors = [
    'rgba(99, 102, 241, 0.8)',
    'rgba(236, 72, 153, 0.8)',
    'rgba(34, 197, 94, 0.8)',
    'rgba(251, 191, 36, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(168, 85, 247, 0.8)',
    'rgba(59, 130, 246, 0.8)',
    'rgba(249, 115, 22, 0.8)',
    'rgba(14, 165, 233, 0.8)',
    'rgba(217, 70, 239, 0.8)',
  ]

  onMounted(function () {
    const ctx = document.getElementById(props.id) as HTMLCanvasElement
    myChart = new Chart(ctx, config)
  })

  onUpdated(function () {
    if (myChart.data) {
      myChart.data.labels = props.labels
      myChart.data.datasets[0].data = props.data
      myChart.update()
    }
  })

  const data = {
    labels: props.labels,
    datasets: [
      {
        data: props.data,
        backgroundColor: props.backgroundColors || defaultColors,
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  }

  const config: any = {
    type: 'doughnut',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'right',
        },
        tooltip: {
          usePointStyle: true,
        },
      },
    },
  }
</script>
