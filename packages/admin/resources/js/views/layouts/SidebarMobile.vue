<template>
  <TransitionRoot as="template" :show="store.isMobileSidebar">
    <Dialog
      as="div"
      static
      class="fixed inset-0 z-40 flex md:hidden"
      :open="store.isMobileSidebar"
      @close="store.isMobileSidebar = false"
    >
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <DialogOverlay class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" />
      </TransitionChild>
      <TransitionChild
        as="template"
        enter="transition ease-in-out duration-300 transform"
        enter-from="-translate-x-full"
        enter-to="translate-x-0"
        leave="transition ease-in-out duration-300 transform"
        leave-from="translate-x-0"
        leave-to="-translate-x-full"
      >
        <div
          class="relative flex w-full max-w-xs flex-1 flex-col border-r border-gray-700/50 bg-gradient-to-b from-gray-900 via-gray-900 to-gray-800 pt-5 pb-4 shadow-2xl"
        >
          <TransitionChild
            as="template"
            enter="ease-in-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in-out duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="absolute top-0 right-0 -mr-12 pt-2">
              <button
                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full bg-gray-800/50 backdrop-blur-sm transition-all duration-200 hover:bg-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-400"
                @click="store.isMobileSidebar = false"
              >
                <span class="sr-only">Close sidebar</span>
                <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </button>
            </div>
          </TransitionChild>

          <RouterLink v-slot="{ href, navigate }" to="/" custom>
            <a
              :href="href"
              class="group flex flex-shrink-0 items-center border-b border-gray-700/50 px-4 pb-4 transition-all duration-200"
              @click="navigate"
            >
              <SidebarLogo />
            </a>
          </RouterLink>

          <div class="mt-5 h-0 flex-1 space-y-1 overflow-y-auto px-3 scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-gray-700">
            <SidebarNav />
          </div>
        </div>
      </TransitionChild>
      <div class="w-14 flex-shrink-0" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
  import {
    Dialog,
    DialogOverlay,
    TransitionChild,
    TransitionRoot,
  } from '@headlessui/vue'
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  import { useStore } from 'Store/main'
  import SidebarNav from './SidebarNav.vue'
  import SidebarLogo from './SidebarLogo.vue'

  const store = useStore()
</script>
