<template>
  <div class="space-y-1">
    <template v-for="item in useSidebarNav" :key="item.label">
      <RouterLink
        v-if="!item.permission || can(item.permission)"
        v-slot="{ href, navigate }"
        custom
        :to="item.uri"
      >
        <a
          :href="href"
          :class="[
            isActive(item.uri)
              ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 border-l-4 border-indigo-400'
              : 'text-gray-300 hover:bg-gray-700/70 hover:text-white hover:border-l-4 hover:border-gray-500/50 border-l-4 border-transparent',
            'group relative flex items-center rounded-lg py-2.5 px-3 text-sm font-medium transition-all duration-200 ease-in-out',
          ]"
          @click="(e) => { console.log('Nav click:', item.label, item.uri); navigate(e); }"
        >
          <!-- Glow effect on active -->
          <div
            v-if="isActive(item.uri)"
            class="absolute inset-0 -z-10 rounded-lg bg-indigo-500/20 blur-xl"
          ></div>

          <Component
            :is="item.icon"
            :class="[
              isActive(item.uri)
                ? 'text-white'
                : 'text-gray-400 group-hover:text-indigo-300',
              'h-5 w-5 flex-shrink-0 ltr:mr-3 rtl:ml-3 transition-colors duration-200',
            ]"
            aria-hidden="true"
          />
          <span class="flex-1">{{ __(item.label) }}</span>

          <span
            v-if="item.create && can(item.createPermission || 'false')"
            class="ml-auto flex h-6 w-6 cursor-pointer items-center justify-center rounded-md bg-gray-700/50 text-gray-400 opacity-0 transition-all duration-200 hover:bg-indigo-500 hover:text-white group-hover:opacity-100"
            @click.prevent="create(item.create || '/')"
          >
            <Component :is="PlusIcon" class="h-3.5 w-3.5" />
          </span>
        </a>
      </RouterLink>
    </template>
  </div>
</template>

<script setup lang="ts">
  import { computed, inject } from 'vue'
  import { useSidebarNav } from 'Use/sidebar-nav'
  import { useRouter } from 'vue-router'
  import { PlusIcon } from '@heroicons/vue/24/outline'

  const can = inject('can')
  const __ = inject('__')
  const router = useRouter()
  const path = computed(() => router.currentRoute.value.path)

  function isActive(uri: string) {
    if (uri === '/' || uri == '/projects') {
      return path.value === uri
    }

    return path.value.startsWith(uri)
  }

  function create(uri: string) {
    router.push(uri)
  }
</script>
