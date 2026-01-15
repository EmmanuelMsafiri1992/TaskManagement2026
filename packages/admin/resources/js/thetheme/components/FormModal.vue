<template>
  <ModalBase :width="sizeToWidth" @close="emit('close')">
    <!-- When using internal form (name and uri provided) -->
    <template v-if="name && uri">
      <FormModalSkeleton v-if="form.fetching" />

      <section v-else>
        <div class="px-6 pt-6">
          <h1 class="text-xl font-semibold text-gray-900" data-cy="page-title">
            {{
              title || (id ? __(`Edit ${getTitle}`) : __(`Create ${getTitle}`))
            }}
          </h1>
        </div>

        <div class="px-6">
          <div class="grid grid-cols-12 gap-6 pt-3 pb-6">
            <slot />
          </div>
        </div>

        <div class="bottom-0 flex justify-end rounded-b-lg bg-gray-50 py-5 px-6">
          <TheButton
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
            {{ id ? __(`Update ${getTitle}`) : __(`Create ${getTitle}`) }}
          </TheButton>
        </div>
      </section>
    </template>

    <!-- When using external form pattern (just act as modal container) -->
    <template v-else>
      <slot />
    </template>
  </ModalBase>
</template>

<script setup lang="ts">
  import ModalBase from './ModalBase.vue'
  import FormModalSkeleton from './FormModalSkeleton.vue'
  import TheButton from './TheButton.vue'
  import { useFormStore, useModalsStore } from 'spack'
  import { computed, provide } from 'vue'

  const props = defineProps<{
    id?: number
    name?: string
    uri?: string
    title?: string
    size?: string
  }>()

  const emit = defineEmits(['close'])

  const sizeToWidth = computed(() => {
    const sizeMap: Record<string, string> = {
      'sm': 'max-w-sm',
      'md': 'max-w-md',
      'lg': 'max-w-lg',
      'xl': 'max-w-xl',
      '2xl': 'max-w-2xl',
      '3xl': 'max-w-3xl',
      '4xl': 'max-w-4xl',
      '5xl': 'max-w-5xl',
      'full': 'max-w-full'
    }
    return sizeMap[props.size || 'lg'] || 'max-w-2xl'
  })

  const getTitle = computed(() => {
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

  // Only use form store if name and uri are provided
  let form: any
  if (props.name && props.uri) {
    form = useFormStore(props.name)()
    form.init(props.uri, props.id)
  } else {
    // Dummy form object when using external form pattern
    form = {
      fetching: false,
      submitting: false,
      submit: () => {}
    }
  }
</script>
