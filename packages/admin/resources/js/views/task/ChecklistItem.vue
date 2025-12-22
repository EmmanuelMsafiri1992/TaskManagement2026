<template>
  <div class="group relative flex">
    <!-- <span class="inline-block rounded-full bg-white h-5 w-5 text-white cursor-pointer mt-px border border-gray-300 mr-2">
      <svg viewBox="0 0 16 16" fill="white"><path d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/></svg>
    </span> -->
    <ChecklistItemCheckbox
      :id="id"
      :index="index"
      :is-completed="isCompleted || null"
    />

    <p
      class="flex-1 text-sm text-gray-700"
      @click="can('task:update') ? task.checklistItemForm = index : false"
    >
      {{ item }}
    </p>

    <div
      v-if="deleteConfirmation"
      class="absolute right-0 flex items-center bg-white pl-4"
      :class="{ 'opacity-50': processing }"
    >
      <span class="mr-2 text-sm text-gray-600">Are you sure to delete?</span>
      <CheckCircleIcon
        class="mr-1 h-5 w-5 cursor-pointer text-green-600 hover:text-green-800"
        @click="deleteTask"
      />
      <XCircleIcon
        class="h-5 w-5 cursor-pointer text-red-600 hover:text-red-800"
        @click="deleteConfirmation = false"
      />
    </div>

    <div v-else class="absolute right-0 hidden bg-white pl-4 group-hover:flex">
      <span v-if="can('task:update')" class="mr-1" @click="task.checklistItemForm = index">
        <PencilSquareIcon
          class="h-4 w-4 cursor-pointer text-gray-600 hover:text-gray-900"
        />
      </span>
      <TrashIcon
        v-if="can('task:delete')"
        class="h-4 w-4 cursor-pointer text-gray-600 hover:text-gray-900"
        @click="deleteConfirmation = true"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { axios } from 'spack'
  import { ref } from 'vue'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import ChecklistItemCheckbox from './ChecklistItemCheckbox.vue'
  import {
    CheckCircleIcon,
    PencilSquareIcon,
    TrashIcon,
    XCircleIcon,
  } from '@heroicons/vue/24/outline'

  const props = defineProps<{
    id: number
    index: number
    item: string
    isCompleted: string | null
  }>()

  const task = useTaskStore(),
    deleteConfirmation = ref(false),
    processing = ref(false),
    projectDetail = useProjectDetail()

  function deleteTask() {
    if (processing.value) return

    processing.value = true

    axios.delete(`checklist-item/${props.id}`).then(({ data }) => {
      processing.value = false
      if (!data.success) return
      task.data.checklists[0].checklist_items.splice(props.index, 1)
      updateProjectDetail()
    })
  }

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }
</script>
