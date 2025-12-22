<template>
  <SettingsLayout>
    <FormBase
      name="setting-general"
      uri="settings/general"
      title="General"
      save-only
    >
      <FieldText name="app_name" :label="__('App Name')" inline />
      <FieldText name="app_url" :label="__('App URL')" inline />
      <FieldSelect
        name="app_locale"
        :label="__('Language')"
        translatable
        input-class="max-w-lg sm:max-w-xs"
        inline
      />
      <FieldSelect
        name="app_timezone"
        :label="__('Timezone')"
        input-class="max-w-lg sm:max-w-xs"
        inline
      />
      <FieldSelect
        name="app_direction"
        :label="__('Direction')"
        input-class="max-w-lg sm:max-w-xs"
        inline
      />
      <FieldLogo />

      <div
        v-if="config.options.is_standalone_demo"
        class="grid border-b border-gray-200 py-5 last:border-b-0 sm:grid-cols-3"
      >
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2"
          >&nbsp;</label
        >
        <div class="py-2 text-sm text-red-600 sm:col-span-2">
          Note: Only language and direction will take effect in this demo!
        </div>
      </div>
    </FormBase>
  </SettingsLayout>
</template>

<script setup lang="ts">
  import { useFormStore } from 'spack'
  import SettingsLayout from './SettingsLayout.vue'
  import FieldLogo from './FieldLogo.vue'
  import { FormBase, useFieldSelect, useFieldText } from 'thetheme'
  import { appData } from '@/app-data'

  const config = appData
  const form = useFormStore('setting-general')()
  const FieldText = useFieldText<any>()
  const FieldSelect = useFieldSelect<any>()

  form.onSuccess(() => location.reload())
</script>
