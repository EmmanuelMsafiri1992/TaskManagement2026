<template>
  <FormModal :name="name" uri="invitations" title="Invite Team Member">
    <FieldText name="email" label="Email" class="col-span-12" />
    <FieldSelect name="role" label="Role" class="col-span-12" />
  </FormModal>
</template>

<script setup lang="ts">
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'
  import { FormModal, useFieldSelect, useFieldText } from 'thetheme'

  const name = 'invitation'
  const form = useFormStore(name)()
  const index = useIndexStore('invitation')()
  const FieldText = useFieldText<any>()
  const FieldSelect = useFieldSelect<any>()

  form.onSuccess(function () {
    index.fetch()
    useModalsStore().pop()
  })
</script>
