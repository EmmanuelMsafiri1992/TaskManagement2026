<template>
  <section class="mt-6">
    <Collapsible open>
      <template #trigger="{ open }">
        <div class="group flex cursor-pointer items-center rounded-lg px-3 py-2 transition-all duration-200 hover:bg-gray-700/50">
          <svg
            viewBox="0 0 16 16"
            class="h-4 w-4 text-gray-400 transition-all duration-200 group-hover:text-indigo-400"
            :class="{ 'rotate-[270deg]': !open, 'rotate-0': open }"
          >
            <path
              d="M14 5.758L13.156 5 7.992 9.506l-.55-.48.002.002-4.588-4.003L2 5.77 7.992 11 14 5.758"
              fill="currentColor"
            ></path>
          </svg>

          <h3
            class="px-3 text-xs font-bold uppercase tracking-wider text-gray-400 transition-colors duration-200 group-hover:text-gray-200"
          >
            {{ __('Favorites') }}
          </h3>
        </div>
      </template>

      <template #content>
        <div class="mt-2 space-y-0.5">
          <template v-for="item in favorite.items" :key="item.project.name">
            <RouterLink
              v-slot="{ navigate, href, route }"
              :to="`/projects/${item.project.id}`"
              custom
            >
              <a
                :href="href"
                :class="[
                  isActive(route.path)
                    ? 'bg-gray-700/70 text-white border-l-2 border-indigo-400'
                    : 'text-gray-300 hover:bg-gray-700/50 hover:text-white border-l-2 border-transparent hover:border-gray-500/30',
                  'group flex items-center rounded-lg py-2 px-3 text-sm font-medium transition-all duration-200',
                ]"
                @click="navigate"
              >
                <span
                  class="h-2.5 w-2.5 rounded-full shadow-lg transition-all duration-200 ltr:ml-1 ltr:mr-3 rtl:mr-1 rtl:ml-3 group-hover:scale-125"
                  aria-hidden="true"
                  :style="{ 'background-color': item.project.meta.color, 'box-shadow': `0 0 8px ${item.project.meta.color}40` }"
                ></span>
                <span class="flex-1 truncate">{{ item.project.name }}</span>
              </a>
            </RouterLink>
          </template>
        </div>
      </template>
    </Collapsible>
  </section>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useRouter } from 'vue-router'
  import { useFavoriteStore } from 'Store/favorite'
  import { Collapsible } from 'thetheme'

  const router = useRouter(),
    path = computed(() => router.currentRoute.value.path),
    favorite = useFavoriteStore()

  favorite.fetch()

  function isActive(href: string) {
    return path.value.startsWith(href)
  }
</script>
