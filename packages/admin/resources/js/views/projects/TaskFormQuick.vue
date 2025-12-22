<template>
  <div class="px-2">
    <div
      v-show="!show"
      class="group mb-1.5 flex cursor-pointer items-center rounded py-2 hover:bg-gray-300 ltr:pl-2 rtl:pr-2"
      @click="showForm"
    >
      <svg
        class="h-4 w-4 text-gray-500 group-hover:text-gray-700"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 4v16m8-8H4"
        />
      </svg>

      <span
        class="block text-sm text-gray-500 group-hover:text-gray-700 ltr:ml-2 rtl:mr-2"
      >
        {{ __('Add task') }}
      </span>
    </div>

    <div v-show="show" class="mb-2">
      <input
        ref="taskinput"
        v-model="task"
        type="text"
        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="Enter a task"
        @keyup.enter="submit"
        @keyup.escape="hideForm"
      />

      <div class="mt-2">
        <TheButton size="xs" :processing="processing" @click="submit">
          {{ __('Add task') }}
        </TheButton>
        <TheButton
          class="ltr:ml-1 rtl:mr-1"
          white
          size="xs"
          :disabled="processing"
          @click="hideForm"
        >
          {{ __('Cancel') }}
        </TheButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios } from 'spack'
  import { TheButton } from 'thetheme'

  const props = defineProps<{
    projectId: number
    listId: number
    listIndex: number
  }>()

  const show = ref(false),
    processing = ref(false),
    task = ref(''),
    taskinput = ref<HTMLInputElement | null>(null)

  const detail = useProjectDetail()

  function showForm() {
    console.log(detail.data.lists)
    console.log('showForm')
    console.log(props.projectId)
    show.value = true
    setTimeout(function () {
      taskinput.value?.focus()
    })
  }

  function hideForm() {
    show.value = false
    task.value = ''
  }

  function submit() {
    console.log(detail.data.lists)
    console.log(props.listIndex)
    console.log(detail.data.lists[props.listIndex])
    if (!task.value) return

    processing.value = true

    axios
      .post('tasks', {
        task: task.value,
        project_id: props.projectId,
        list_id: props.listId,
      })
      .then((response) => {
        console.log(response.data)
        detail.data.lists[props.listIndex].tasks.push(response.data)
        task.value = ''
        show.value = false
        processing.value = false
      })
  }
</script>
