<template>
  <Dropdown close-outside @toggle="onOpen">
    <template #trigger>
      <span class="cursor-pointer">
        <ArrowTopRightOnSquareIcon
          class="h-4 w-4 text-gray-600 hover:text-gray-800"
        />
      </span>
    </template>

    <template #content>
      <div
        class="absolute bottom-0 z-40 mb-6 w-96 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 ltr:origin-bottom-right rtl:left-0 rtl:origin-bottom-left"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <div class="px-6 py-4">
          <h3 class="mb-4 text-sm font-medium text-gray-700">Share URL</h3>

          <section class="mb-2">
            <div
              class="flex w-full items-center rounded-md border border-gray-300 px-3 py-2"
              @click="onClick"
            >
              <input
                ref="input"
                v-model="url"
                type="text"
                readonly
                class="flex-1 border-0 py-0 px-0 text-sm text-gray-600 focus:ring-0"
              />

              <span
                v-if="isNavigatorAvailable"
                class="ml-2 block w-4 cursor-pointer text-gray-500 hover:text-gray-800"
                :aria-label="copyLabel"
                data-cooltipz-dir="bottom"
                @click="copy"
                @mouseover="resetTooltip"
              >
                <ClipboardIcon class="h-4 w-4" />
              </span>
            </div>
          </section>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { Dropdown } from 'thetheme'
  import {
    ArrowTopRightOnSquareIcon,
    ClipboardIcon,
  } from '@heroicons/vue/24/outline'

  const isNavigatorAvailable = ref(false),
    url = ref(window.location.href),
    input = ref<HTMLInputElement | null>(null),
    copyLabel = ref('Copy to Clipboard')

  function onOpen(open: boolean) {
    checkNavigator()

    if (open) {
      setTimeout(function () {
        onClick()
      })
    }
  }

  function checkNavigator() {
    navigator.clipboard
      ? (isNavigatorAvailable.value = true)
      : (isNavigatorAvailable.value = false)
  }

  function onClick() {
    input.value?.focus()
    input.value?.select()
  }

  function copy() {
    navigator.clipboard.writeText(input.value?.value || '')
    copyLabel.value = 'Copied: ' + input.value?.value
  }

  function resetTooltip() {
    copyLabel.value = 'Copy to Clipboard'
  }
</script>
