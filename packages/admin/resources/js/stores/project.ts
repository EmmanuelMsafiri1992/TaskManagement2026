import { axios } from 'spack'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useModalsStore } from 'spack'
import ProjectForm from 'View/projects/Form.vue'
import type { ProjectI } from 'types'

export const useProjectIndex = defineStore('project-index', () => {
  const items = ref<ProjectI[]>([]),
    fetching = ref<boolean>(false)

  fetch()

  function create(): void {
    useModalsStore().add(ProjectForm)
  }

  async function fetch() {
    fetching.value = true

    const { data } = await axios.get<ProjectI[]>('projects')

    fetching.value = false
    items.value = data
  }

  function archive() {
    console.log('archive')
  }

  function favorite() {
    console.log('favorite')
  }

  function edit() {
    console.log('edit')
  }

  return {
    create,
    fetch,
    fetching,
    items,
    archive,
    favorite,
    edit,
  }
})
