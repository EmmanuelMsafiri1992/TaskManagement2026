<template>
  <canvas :id="id"></canvas>
</template>

<script setup lang="ts">
  import { onMounted, onUpdated } from 'vue'
  import {
    CategoryScale,
    Chart,
    Filler,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
  } from 'chart.js'

  const props = defineProps<{
    id: string
    data: any
    backgroundColor: string
    borderColor: string
    tooltipLabel: string
  }>()

  Chart.register(
    LineElement,
    PointElement,
    LineController,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip,
  )

  let myChart = {}

  onMounted(function () {
    const ctx = document.getElementById(props.id) as HTMLCanvasElement
    myChart = new Chart(ctx, config)
  })

  onUpdated(function () {
    // @ts-ignore
    myChart.data.datasets[0].data = props.data

    // @ts-ignore
    myChart.update()
  })

  const data = {
    datasets: [
      {
        label: props.tooltipLabel,
        backgroundColor: props.backgroundColor,
        borderColor: props.borderColor,
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: 'white',
        fill: true,
        data: props.data,
      },
    ],
  }

  const config: any = {
    type: 'line',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        elements: {
          point: {
            pointStyle: 'cross',
            backgroundColor: 'blue',
            borderColor: 'black',
          },
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
