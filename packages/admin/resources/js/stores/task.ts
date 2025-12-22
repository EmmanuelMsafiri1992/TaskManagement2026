import { defineStore } from 'pinia'
import { axios } from 'spack'
import { ref } from 'vue'
import type { TaskI, TaskOptions } from 'types'

export const useTaskStore = defineStore('task', () => {
  const loading = ref<boolean | number>(true),
    data = ref<Partial<TaskI>>({}),
    options = ref<Partial<TaskOptions>>({}),
    isContentForm = ref(false),
    subtaskForm = ref(null),
    checklistItemForm = ref<number | null>(null),
    commentForm = ref(null),
    newSubtaskForm = ref(false),
    newChecklistItemForm = ref(false)

  function fetch(payload: any) {
    if (typeof payload == 'number') {
      loading.value = payload

      axios.get('tasks/' + payload).then(({ data: responseData }) => {
        loading.value = false
        options.value = responseData.options
        data.value = responseData.task
      })
    } else {
      loading.value = payload.loading

      axios.get('tasks/' + payload.id).then(({ data }) => {
        loading.value = false
        options.value = data.options
        data.value = data.task
      })
    }
  }

  return {
    checklistItemForm,
    commentForm,
    data,
    fetch,
    isContentForm,
    loading,
    newChecklistItemForm,
    newSubtaskForm,
    options,
    subtaskForm,
  }
})
