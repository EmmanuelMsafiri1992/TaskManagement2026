<template>
  <section :class="{ 'opacity-50': processing }">
    <div class="overflow-hidden rounded-md border">
      <textarea
        v-model="title"
        class="autosize task-title block h-8 w-full resize-none border-0 px-2 pt-2 text-base font-semibold text-gray-800 placeholder:text-sm placeholder:font-normal placeholder:text-gray-500 focus:ring-0"
        placeholder="Task"
        @keydown.enter.prevent="save"
        @keydown.esc="cancel"
      />

      <textarea
        v-model="description"
        class="autosize task-description block h-14 w-full resize-none border-0 px-2 py-2 text-sm placeholder:font-normal placeholder:text-gray-500 focus:ring-0"
        placeholder="Description"
        @keydown.ctrl.enter="save"
        @keydown.esc="cancel"
      />
    </div>

    <div class="mt-2">
      <button
        type="button"
        class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="save"
      >
        {{ __('Save') }}
      </button>

      <button
        type="button"
        class="inline-flex items-center rounded border border-transparent bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-0 ltr:ml-1 rtl:mr-1"
        @click="cancel"
      >
        {{ __('Cancel') }}
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { onMounted, ref } from 'vue'
  import autosize from 'autosize'
  import { useTaskStore } from 'Store/task'
  import { axios } from 'spack'

  onMounted(() => {
    const el = document.querySelector('.task-title') as HTMLElement
    el.focus()
    autosize(document.querySelectorAll('.autosize'))
  })

  const task = useTaskStore(),
    processing = ref(false),
    title = ref(task.data.title),
    description = ref(task.data.description)

  function save() {
    if (processing.value) return

    if (
      task.data.title == title.value &&
      task.data.description == description.value
    ) {
      cancel()
      return
    }

    processing.value = true

    axios
      .patch(`tasks/${task.data.id}`, {
        title: title.value,
        description: description.value,
      })
      .then(() => {
        processing.value = false
        task.data.title = title.value
        task.data.description = description.value
        cancel()
      })
  }

  function cancel() {
    title.value = ''
    description.value = ''
    task.isContentForm = false
  }
</script>
