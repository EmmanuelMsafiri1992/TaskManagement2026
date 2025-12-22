import { axios } from 'spack'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { ProjectI } from 'types'

export const useProjectDetail = defineStore('project-detail', () => {
  const fetching = ref<boolean>(false),
    data = ref<Partial<ProjectI>>({})

  async function fetch(id: number | string | undefined) {
    console.log('[ProjectDetail] Fetching project:', id)
    fetching.value = true

    const { data: responseData } = await axios.get<ProjectI>(`projects/${id}`)

    console.log('[ProjectDetail] Response received:', responseData)
    console.log('[ProjectDetail] Lists count:', responseData.lists?.length || 0)

    fetching.value = false
    data.value = responseData

    console.log('[ProjectDetail] Data assigned, lists:', data.value.lists)
  }

  return {
    fetch,
    fetching,
    data,
  }
})
