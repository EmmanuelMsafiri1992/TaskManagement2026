<template>
  <Card>
    <FormModalSkeleton v-if="form.fetching" />

    <section v-else>
      <!-- Title Header -->
      <div v-if="$slots.title" class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">
          <slot name="title" />
        </h2>
      </div>

      <div class="px-6 py-4">
        <slot />
      </div>

      <div class="bottom-0 flex justify-end rounded-b-lg bg-gray-50 py-5 px-6">
        <TheButton
          v-if="!saveOnly"
          class="mr-3"
          white
          :disabled="form.submitting"
          @click="handleCancel"
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
    name?: string
    uri?: string
    title?: string
    saveOnly?: boolean
    buttonText?: string
    externalForm?: any // Allow passing an external form object
  }>()

  const emit = defineEmits(['submit', 'cancel'])

  const title = computed(() => {
    if (!props.name) return ''
    const arr = props.name.split(' ')

    for (let i = 0; i < arr.length; i++) {
      arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1)
    }

    return arr.join(' ')
  })

  if (props.name) {
    provide('form_name', props.name)
  }

  // Use external form if provided, otherwise use the store
  let form: any
  if (props.externalForm) {
    // External form object passed via props
    form = props.externalForm
  } else if (props.name && props.uri) {
    // Use the form store with name and uri
    form = useFormStore(props.name)()
    form.init(props.uri)
  } else {
    // Create a dummy form object to prevent errors
    form = {
      fetching: false,
      submitting: false,
      submit: () => emit('submit')
    }
  }

  const handleCancel = () => {
    if (props.externalForm) {
      // When using external form, emit cancel event
      emit('cancel')
    } else {
      // When using modals store, pop the modal
      useModalsStore().pop()
    }
  }
</script>
