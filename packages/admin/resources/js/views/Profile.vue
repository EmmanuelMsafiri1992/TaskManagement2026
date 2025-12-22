<template>
  <Topbar :title="__('Profile')" />

  <FormBase name="profile" uri="profile" save-only>
    <FieldAvatar />
    <FieldText name="name" :label="__('Name')" inline />
    <FieldText name="email" :label="__('Email')" inline disabled />
    <FieldText
      name="password"
      :label="__('New Password')"
      type="password"
      inline
    />
  </FormBase>
</template>

<script setup lang="ts">
  import { useFormStore } from 'spack'
  import { FormBase, Topbar, useFieldText } from 'thetheme'
  import FieldAvatar from 'Component/FieldAvatar.vue'
  import type { ProfileForm } from 'types'

  const form = useFormStore<ProfileForm>('profile')()
  const FieldText = useFieldText<any>()

  form.onSuccess(() => {
    form.submitting = false
    form.data.password = ''
  })
</script>
