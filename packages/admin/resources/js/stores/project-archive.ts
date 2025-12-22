import { axios } from 'spack'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { ArchivedProject } from '@/types'

export const useProjectArchiveStore = defineStore('project-archive', () => {
  const items = ref<ArchivedProject[]>([]),
    fetching = ref<boolean>(false),
    show = ref<boolean>(false)

  async function fetch() {
    fetching.value = true

    const { data } = await axios.get<ArchivedProject[]>('projects?archived')

    fetching.value = false
    show.value = true
    items.value = data
  }

  return {
    fetching,
    items,
    show,
    fetch,
  }
})
