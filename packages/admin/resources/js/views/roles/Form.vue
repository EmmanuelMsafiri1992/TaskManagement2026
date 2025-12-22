<template>
  <FormModal :id="id" :name="name" uri="roles">
    <FieldText name="name" label="Name" class="col-span-12" />

    <div class="col-span-12">
      <div class="mb-4 flex items-center text-sm">
        <p class="block text-sm font-medium text-gray-700">
          {{ __('Permissions') }}
        </p>
        <span
          class="block cursor-pointer pl-4 text-xs text-gray-700 hover:text-gray-900"
          @click="select"
        >
          {{ __('Select All') }}
        </span>
        <span class="block px-1 text-xs text-gray-700">/</span>
        <span
          class="block cursor-pointer text-xs text-gray-700 hover:text-gray-900"
          @click="deselect"
        >
          {{ __('Deselect All') }}
        </span>
      </div>
      <FieldCheckbox name="permissions" class="col-span-12" />
    </div>
  </FormModal>
</template>

<script setup lang="ts">
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'
  import { FormModal, useFieldCheckbox, useFieldText } from 'thetheme'
  import type { RoleForm } from 'types'

  defineProps<{
    id?: number
  }>()

  const name = 'role'
  const form = useFormStore<RoleForm>(name)()
  const index = useIndexStore(name)()
  const FieldText = useFieldText<any>()
  const FieldCheckbox = useFieldCheckbox<any>()

  form.onSuccess((response) => {
    index.updateOrCreate(response.model)
    useModalsStore().pop()
  })

  function select() {
    form.data.permissions = form.options.permissions.map((x: any) => x.value)
  }

  function deselect() {
    form.data.permissions = []
  }
</script>
