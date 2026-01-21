<template>
  <FormModal :name="name" uri="projects">
    <FieldText name="name" label="Name" class="col-span-12" />
    <FieldColor name="color" />
    <FieldChooseUsers name="users" />
  </FormModal>
</template>

<script setup lang="ts">
  import { FormModal, useFieldText } from 'thetheme'
  import FieldColor from 'Component/FieldColor.vue'
  import FieldChooseUsers from './FieldChooseUsers.vue'
  import { useFormStore } from 'spack'
  import { useModalsStore } from 'spack'
  import { useIndexStore } from 'spack'
  import { useProjectIndex } from 'Store/project'
  import type { ProjectForm } from 'types'

  const name = 'project'
  const form = useFormStore(name)()
  const project = useProjectIndex()
  const projectIndexStore = useIndexStore('projects')()
  const FieldText = useFieldText<ProjectForm>()

  form.onSuccess((response) => {
    const params = new URLSearchParams(window.location.search)

    if (!params.has('archived') && Array.isArray(projectIndexStore.data)) {
      projectIndexStore.data.push(response.model)
    }

    // Refetch the project list to ensure all fields are properly loaded
    project.fetch()
    useModalsStore().pop()
  })
</script>
