<template>
  <FormModal :id="id" :name="name" uri="users">
    <FieldText name="name" label="Name" class="col-span-12" />
    <FieldText name="email" label="Email" class="col-span-12" />
    <FieldText name="password" label="Password" class="col-span-12" />
    <FieldSelect name="role" label="Role" class="col-span-12" />
  </FormModal>
</template>

<script setup lang="ts">
  import { FormModal, useFieldSelect, useFieldText } from 'thetheme'
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'

  defineProps<{
    id?: number
  }>()

  const name = 'user'
  const form = useFormStore(name)()
  const index = useIndexStore(name)()
  const FieldText = useFieldText<any>()
  const FieldSelect = useFieldSelect<any>()

  form.onSuccess(() => {
    index.fetch()
    useModalsStore().pop()
  })
</script>
