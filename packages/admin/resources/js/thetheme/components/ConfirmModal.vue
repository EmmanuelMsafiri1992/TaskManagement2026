<template>
  <ModalBase width="max-w-xl">
    <div class="rounded-t-lg bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
      <div class="sm:flex sm:items-start">
        <div
          class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
        >
          <!-- Heroicon name: exclamation -->
          <svg
            class="h-6 w-6 text-red-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <div class="mt-0 ltr:ml-4 ltr:text-left rtl:mr-4 rtl:text-right">
          <h3
            id="modal-headline"
            class="text-lg font-medium leading-6 text-gray-900"
          >
            {{ __(confirm.title) }}
          </h3>
          <div class="mt-2">
            <p class="text-sm leading-5 text-gray-500">
              {{ __(confirm.description) }}
            </p>
          </div>
        </div>
      </div>
    </div>
    <div
      class="rounded-b-lg bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
    >
      <TheButton
        class="ltr:ml-3 rtl:mr-3"
        :danger="confirm.danger"
        :processing="confirm.processing"
        @click="confirm.proceed"
      >
        {{ __(confirm.button) }}
      </TheButton>

      <TheButton white @click="confirm.hide">
        {{ __('Cancel') }}
      </TheButton>
    </div>
  </ModalBase>
</template>

<script setup lang="ts">
  import ModalBase from './ModalBase.vue'
  import { useConfirmStore } from 'spack'
  import TheButton from './TheButton.vue'

  const confirm = useConfirmStore()
</script>
