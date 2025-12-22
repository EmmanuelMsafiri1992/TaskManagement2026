<template>
  <span
    class="mt-0.5 inline-block h-5 w-5 cursor-pointer rounded-full bg-white text-white"
    :class="{
      'opacity-50': processing,
      'bg-blue-400': task.data.completed_at,
      'border border-gray-300': !task.data.completed_at,
    }"
    @click="toggle(task.data.id)"
  >
    <svg viewBox="0 0 16 16" fill="white">
      <path
        d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"
      />
    </svg>
  </span>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios, useIndexStore, useModalsStore } from 'spack'

  const tasksIndex = useIndexStore('tasks')()
  const projectDetail = useProjectDetail()
  const task = useTaskStore(),
    processing = ref(false)

  function toggle(id: number | undefined) {
    if (processing.value) return

    processing.value = true

    axios.patch(`tasks/${id}/complete`).then(({ data }) => {
      processing.value = false
      task.data.completed_at = data.completed_at

      updateProjectDetail()

      const index = useModalsStore().components.findIndex(
        // @ts-ignore
        (x) => x.component.__name == 'TaskModal',
      )
      if (useModalsStore().components[index].payload?.page == 'tasks') {
        console.log('tasks page')
        tasksIndex.fetch({ loading: false })
      }

      // if (useModalStore().$state.page == 'calendar') {
      //   calendar.updateDays()
      // }

      // if (useModalsStore().$state.page == 'tasks') {
      //   tasksIndex.fetch({ loading: false })
      // }
    })
  }

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }
</script>
