<template>
  <div class="relative z-10 flex h-16 flex-shrink-0 bg-white shadow">
    <button
      class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden"
      @click="store.isMobileSidebar = true"
    >
      <span class="sr-only">Open sidebar</span>
      <Bars3BottomLeftIcon class="h-6 w-6" aria-hidden="true" />
    </button>

    <div class="flex flex-1 justify-between px-4">
      <div class="flex flex-1 items-center px-4">
        <RecentDropdown />
        <CreateDropdown
          v-if="can('project:create') || can('user:create')"
          class="ltr:ml-6 rtl:mr-6"
        />
      </div>
      <div class="flex items-center ltr:ml-4 rtl:mr-4 ltr:md:ml-6 rtl:md:mr-6">
        <GeneratorDropdown />
        <TimerCurrentUser class="ltr:ml-5 ltr:mr-5 rtl:ml-5 rtl:mr-5" />
        <TimerDropdown />
        <ContractCountdown class="ltr:ml-5 rtl:mr-5" />
        <NotificationsDropdown class="ltr:ml-5 rtl:mr-5" />
        <ProfileDropdown class="ltr:ml-5 rtl:mr-5" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import TimerCurrentUser from './TimerCurrentUser.vue'
  import TimerDropdown from './TimerDropdown.vue'
  import GeneratorDropdown from './GeneratorDropdown.vue'
  import CreateDropdown from './_CreateDropdown.vue'
  import NotificationsDropdown from './_NotificationsDropdown.vue'
  import RecentDropdown from './_RecentDropdown.vue'
  import ProfileDropdown from './ProfileDropdown.vue'
  import ContractCountdown from './ContractCountdown.vue'
  import { Bars3BottomLeftIcon } from '@heroicons/vue/24/outline'
  import { useStore } from 'Store/main'

  const store = useStore()
</script>
