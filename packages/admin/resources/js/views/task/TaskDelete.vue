<template>
  <section class="py-3">
    <h2 class="px-2 text-sm font-medium text-gray-600">{{ __('Delete') }}</h2>

    <div v-if="isDeleteConfirm" class="mt-2 space-y-2 px-2">
      <button
        class="flex w-full items-center rounded border border-transparent bg-red-500 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-0 disabled:opacity-50"
        :disabled="processing"
        @click="archive"
      >
        <TrashIcon class="mr-2 h-3.5 w-3.5" />
        {{ __('Are you sure') }}
      </button>

      <button
        class="flex w-full items-center rounded border border-transparent bg-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-0 disabled:opacity-50"
        :disabled="processing"
        @click="isDeleteConfirm = false"
      >
        <ArrowUturnLeftIcon class="mr-2 h-3.5 w-3.5" />
        {{ __('Cancel') }}
      </button>
    </div>

    <div v-else class="mt-2 space-y-2 px-2">
      <button
        class="flex w-full items-center rounded border border-transparent bg-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-0"
        @click="isDeleteConfirm = true"
      >
        <TrashIcon class="mr-2 h-3.5 w-3.5" />
        Delete
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { TrashIcon } from '@heroicons/vue/24/outline'
  import { ArrowUturnLeftIcon } from '@heroicons/vue/24/solid'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios, useModalsStore } from 'spack'

  const task = useTaskStore()
  const projectDetail = useProjectDetail()
  const processing = ref(false)
  const isDeleteConfirm = ref(false)

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  function archive() {
    if (processing.value) return

    processing.value = true

    axios.patch('tasks/' + task.data.id + '/archive').then(() => {
      processing.value = false
      updateProjectDetail()
      task.data.deleted_at = 'archived'
      close()
    })
  }

  // function restore() {
  //   if (processing.value) return

  //   processing.value = true

  //   axios.patch('tasks/' + task.data.id + '/restore').then(({ data }) => {
  //     processing.value = false
  //     // updateProjectDetail()
  //     task.data.deleted_at = null
  //   })
  // }

  // function deleteTask() {
  //   if (processing.value) return

  //   processing.value = true

  //   axios
  //     .delete(`tasks/${task.data.id}`)
  //     .then(({ data }) => {
  //       close()
  //       processing.value = false
  //       useToastStore().flash(data.message)
  //       updateProjectDetail()
  //     })
  //     .catch((error) => {
  //       useToastStore().danger(error.response.data.message)
  //     })
  // }

  function close() {
    const url = `/projects/${task.data.project_id}`
    history.replaceState(history.state, '', url)

    useModalsStore().pop()
  }
</script>
