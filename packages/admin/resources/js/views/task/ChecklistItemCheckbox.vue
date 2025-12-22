<template>
  <span
    class="mt-px mr-2 inline-block h-5 w-5 cursor-pointer rounded-full bg-white text-white"
    :class="{
      'opacity-50': processing,
      'bg-blue-400': isCompleted,
      'border border-gray-300': !isCompleted,
    }"
    @click="toggle"
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
  import { axios } from 'spack'

  const props = defineProps<{
    id: number
    index: number
    isCompleted: string | null
  }>()

  const task = useTaskStore(),
    projectDetail = useProjectDetail(),
    processing = ref(false)

  function toggle() {
    if (processing.value) return

    processing.value = true

    axios.patch(`checklist-item/${props.id}/complete`).then(({ data }) => {
      processing.value = false
      task.data.checklists[0].checklist_items[props.index].completed_at =
        data.completed_at
      updateProjectDetail()
    })
  }

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }
</script>
