<template>
  <FormModal :id="id" :name="name" uri="labels">
    <FieldText name="name" label="Name" class="col-span-12" />
    <FieldColor name="color" />
  </FormModal>
</template>

<script setup lang="ts">
  import { FormModal, useFieldText } from 'thetheme'
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'
  import FieldColor from 'Component/FieldColor.vue'

  defineProps<{
    id?: number
  }>()

  const name = 'label'
  const form = useFormStore(name)()
  const FieldText = useFieldText<any>()
  const index = useIndexStore('label')()

  form.onSuccess((response) => {
    console.log(response.model)
    // index.data.data.unshift(response.model)
    index.updateOrCreate(response.model)
    useModalsStore().pop()
  })
</script>
