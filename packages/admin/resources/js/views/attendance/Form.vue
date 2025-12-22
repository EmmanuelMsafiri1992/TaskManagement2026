<template>
  <FormModal :id="id" :name="name" uri="attendance">
    <input type="hidden" name="action" value="manual" />

    <FieldSelect
      name="user_id"
      label="User"
      class="col-span-12"
      :disabled="!!id"
    />

    <FieldText
      name="date"
      label="Date"
      type="date"
      class="col-span-12"
    />

    <FieldText
      name="clock_in"
      label="Clock In"
      type="datetime-local"
      class="col-span-6"
    />

    <FieldText
      name="clock_out"
      label="Clock Out"
      type="datetime-local"
      class="col-span-6"
    />

    <FieldText
      name="notes"
      label="Notes"
      class="col-span-12"
    />
  </FormModal>
</template>

<script setup lang="ts">
  import { FormModal, useFieldSelect, useFieldText } from 'thetheme'
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'

  defineProps<{
    id?: number
  }>()

  const name = 'attendance'
  const form = useFormStore(name)()
  const index = useIndexStore(name)()
  const FieldText = useFieldText<any>()
  const FieldSelect = useFieldSelect<any>()

  form.onSuccess(() => {
    index.fetch()
    useModalsStore().pop()
  })
</script>
