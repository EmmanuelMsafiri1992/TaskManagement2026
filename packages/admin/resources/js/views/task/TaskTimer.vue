<template>
  <section class="pb-3">
    <h2 class="px-2 text-sm font-medium text-gray-600">{{ __('Time') }}</h2>

    <div class="mt-2 flex items-center px-2">
      <div v-if="isCurrentUserTask" class="pr-4">
        <span v-if="task.data.id == timer.taskId" @click.stop="stopTimer">
          <StopIcon
            class="h-4 w-4 cursor-pointer text-red-500 hover:text-red-700"
          />
        </span>
        <span v-else @click.stop="startTimer">
          <PlayIcon
            class="h-4 w-4 cursor-pointer text-gray-500 hover:text-gray-700"
          />
        </span>
      </div>

      <p v-if="task.data.id == timer.taskId" class="text-red-500">
        {{ timer.currentTaskTimer.h.toString().padStart(2, '0') }}:{{
          timer.currentTaskTimer.m.toString().padStart(2, '0')
        }}:{{ timer.currentTaskTimer.s.toString().padStart(2, '0') }}
      </p>

      <p v-else class="text-gray-600">
        {{ getTime() }}
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { useTaskStore } from 'Store/task'
  import { useTimerStore } from 'Store/timer'
  import { PlayIcon, StopIcon } from '@heroicons/vue/24/outline'
  import { appData } from '@/app-data'
  import type { TaskI } from 'types'

  const task = useTaskStore()
  const timer = useTimerStore()
  const isCurrentUserTask = ref(false)
  const isSetTime = ref(false)

  function getTime() {
    if (isSetTime.value) {
      return `${timer.currentTaskTimer.h
        .toString()
        .padStart(2, '0')}:${timer.currentTaskTimer.m
        .toString()
        .padStart(2, '0')}:${timer.currentTaskTimer.s
        .toString()
        .padStart(2, '0')}`
    }

    let seconds = task.data.total_seconds

    let s = Math.floor((seconds || 0) % 60)
    let m = Math.floor(((seconds || 0) % 3600) / 60)
    let h = Math.floor((seconds || 0) / 3600)

    return `${h.toString().padStart(2, '0')}:${m
      .toString()
      .padStart(2, '0')}:${s.toString().padStart(2, '0')}`
  }

  function startTimer() {
    if (!isSetTime.value) {
      timer.setTimer(task.data.total_seconds as number)
      isSetTime.value = true
    }

    setTimeout(function () {
      timer.start(task.data as TaskI)
    })
  }

  function stopTimer() {
    timer.stop()
  }

  // function updateProjectDetail() {
  //   projectDetail.fetch({
  //     loading: false,
  //     id: task.data.project_id,
  //   })
  // }

  function checkIsCurrentUserTask() {
    if (task.data.users.length) {
      if (task.data.users.find((x: any) => x.id == appData.user.id)) {
        return (isCurrentUserTask.value = true)
      }
    }

    isCurrentUserTask.value = false
  }

  checkIsCurrentUserTask()
</script>
