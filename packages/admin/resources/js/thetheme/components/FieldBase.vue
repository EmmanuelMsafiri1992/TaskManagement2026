<template>
  <div
    :class="{
      'grid grid-cols-3 border-b border-gray-200 py-5 last:border-b-0': inline,
    }"
  >
    <label
      v-if="label"
      :for="formName + '-' + name"
      class="block text-sm font-medium text-gray-700"
      :class="{ 'mt-px pt-2': inline }"
    >
      {{ label }}
    </label>

    <div class="sm:col-span-2" :class="{ 'mt-1': !inline }">
      <slot />
      <div
        v-if="form.errors[name]"
        class="mt-2 text-xs italic leading-normal text-red-600"
      >
        {{ form.errors[name][0] }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { useFormStore } from 'spack'

  defineProps<{
    full: boolean
    inline: boolean
    label?: string
    lg: boolean
    name: any
  }>()

  const formName = inject('form_name', ''),
    form = useFormStore(formName)()
</script>
