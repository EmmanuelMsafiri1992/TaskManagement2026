<template>
  <div class="sm:col-span-12">
    <label class="block text-sm font-medium text-gray-700">
      {{ __('Color') }}
    </label>

    <label
      v-for="option in form.options['color']"
      :key="option.value"
      class="mt-1 inline-block h-6 w-6 cursor-pointer rounded ltr:mr-1 rtl:ml-1"
      :style="{
        'background-color': option.value,
        color: form.data['color'] == option.value ? '#ffffff' : option.value,
      }"
    >
      <svg viewBox="0 0 16 16" fill="currentColor">
        <path
          d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"
        />
      </svg>
      <input
        v-model="form.data['color']"
        type="radio"
        :value="option.value"
        class="hidden"
      />
    </label>

    <div
      v-if="form.errors['color']"
      class="mt-2 text-xs italic leading-normal text-red-600"
    >
      {{ form.errors['color'][0] }}
    </div>
  </div>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { useFormStore } from 'spack'
  import type { ProjectForm } from 'types'

  defineProps<{
    name: string
  }>()

  // withDefaults(
  //   defineProps<{
  //     name: string
  //   }>(),
  //   {
  //   },
  // )

  const form = useFormStore<ProjectForm>(inject('form_name', ''))()
</script>
