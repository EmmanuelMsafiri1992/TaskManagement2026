<template>
  <Card>
    <FormModalSkeleton v-if="form.fetching" />

    <section v-else>
      <div class="px-6">
        <slot />
      </div>

      <div class="bottom-0 flex justify-end rounded-b-lg bg-gray-50 py-5 px-6">
        <TheButton
          v-if="!saveOnly"
          class="mr-3"
          white
          :disabled="form.submitting"
          @click="useModalsStore().pop()"
        >
          {{ __('Cancel') }}
        </TheButton>

        <TheButton
          data-cy="submit-button"
          :processing="form.submitting"
          @click="form.submit"
        >
          <span v-if="saveOnly">{{ __('Save') }}</span>
          <span v-else>
            {{ buttonText || (id ? __('Update') : __('Create')) + ' ' + title }}
          </span>
        </TheButton>
      </div>
    </section>
  </Card>
</template>

<script setup lang="ts">
  import { Card, TheButton } from 'thetheme'
  import FormModalSkeleton from './FormModalSkeleton.vue'
  import { useFormStore, useModalsStore } from 'spack'
  import { computed, provide } from 'vue'

  const props = defineProps<{
    id?: string | number
    name: string
    uri: string
    title?: string
    saveOnly?: boolean
    buttonText?: string
  }>()

  const title = computed(() => {
    const arr = props.name.split(' ')

    for (let i = 0; i < arr.length; i++) {
      arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1)
    }

    return arr.join(' ')
  })

  provide('form_name', props.name)

  const form = useFormStore(props.name)()

  form.init(props.uri)
</script>
