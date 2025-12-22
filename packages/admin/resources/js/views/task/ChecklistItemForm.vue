<template>
  <div class="flex" :class="{ 'opacity-50': processing }">
    <input
      v-model="item"
      type="text"
      class="item-input block w-full flex-1 rounded-none border-gray-300 text-sm focus:border-gray-300 focus:ring-0 disabled:opacity-50 ltr:rounded-l-md rtl:rounded-r-md"
      placeholder="Enter checklist item"
      @keyup.enter="submit"
      @keydown.esc="hideForm"
    />

    <button
      type="button"
      class="relative inline-flex items-center space-x-2 border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-gray-300 focus:outline-none focus:ring-0 disabled:opacity-50 ltr:-ml-px rtl:-mr-px rtl:space-x-reverse"
      @click="hideForm"
    >
      <svg
        class="h-5 w-5 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </button>

    <button
      type="button"
      class="relative inline-flex items-center space-x-2 border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-gray-300 focus:outline-none focus:ring-0 disabled:opacity-50 ltr:-ml-px ltr:rounded-r-md rtl:-mr-px rtl:space-x-reverse rtl:rounded-l-md"
      @click="submit"
    >
      <svg
        class="h-5 w-5 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 13l4 4L19 7"
        />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, ref } from 'vue'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios } from 'spack'

  const props = defineProps<{
    id?: number
    index?: number
    item?: string
  }>()

  onMounted(() => {
    const el = document.querySelector('.item-input') as HTMLElement
    el.focus()
  })

  const task = useTaskStore(),
    processing = ref(false),
    item = ref(props.item),
    projectDetail = useProjectDetail()

  function hideForm() {
    task.checklistItemForm = null
    task.newChecklistItemForm = false
  }

  function submit() {
    if (processing.value) return
    if (!item.value) return

    if (props.item == item.value && props.id) {
      hideForm()
      return
    }

    processing.value = true

    const method = props.id ? 'patch' : 'post'
    const uri = props.id ? `checklist-item/${props.id}` : 'checklist-item'

    axios[method](uri, {
      title: item.value,
      task_id: task.data.id,
    }).then(({ data }) => {
      processing.value = false
      props.id ? updateTask(data) : appendTask(data)
      props.id ? hideForm() : (item.value = '')
      updateProjectDetail()
    })
  }

  function appendTask(item: any) {
    if (task.data.checklists.length == 0) {
      task.data.checklists = [{ checklist_items: [] }]
    }

    task.data.checklists[0].checklist_items.push(item)
  }

  function updateTask(item: any) {
    // console.log('hit updateTask')
    // console.log(item)
    // console.log(task.data.checklists[0].checklist_items[props.index])
    task.data.checklists[0].checklist_items[props.index || 0] = item
  }

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }
</script>
