<template>
  <SettingsLayout>
    <FormBase
      name="setting-email"
      uri="settings/email"
      :title="__('Email')"
      save-only
    >
      <FieldSelect
        name="mail_driver"
        :label="__('Driver')"
        input-class="max-w-xs"
        inline
      />
      <FieldText name="mail_host" :label="__('Host')" inline />
      <FieldText name="mail_port" :label="__('Port')" inline />
      <FieldText name="mail_username" :label="__('Username')" inline />
      <FieldText name="mail_password" :label="__('Password')" inline />
      <FieldSelect
        name="mail_encryption"
        :label="__('Encryption')"
        input-class="max-w-xs"
        inline
      />
      <FieldCheckbox name="mail_verify_peer" :label="__('Verify Peer')" inline />
      <FieldText name="mail_from_address" :label="__('From Address')" inline />
      <FieldText name="mail_from_name" :label="__('From Name')" inline />
    </FormBase>
  </SettingsLayout>
</template>

<script setup lang="ts">
  import { useFlashStore, useFormStore } from 'spack'
  import SettingsLayout from './SettingsLayout.vue'
  import { FormBase, useFieldCheckbox, useFieldSelect, useFieldText } from 'thetheme'

  const form = useFormStore('setting-email')()
  const FieldText = useFieldText<any>()
  const FieldSelect = useFieldSelect<any>()
  const FieldCheckbox = useFieldCheckbox<any>()

  form.onSuccess(() => {
    useFlashStore().success('Settings Saved Successfully!')
    location.reload()
  })
</script>
