<template>
  <section class="mt-6">
    <Collapsible>
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
            {{ __('Projects') }}
          </h3>

          <div class="ml-auto flex items-center gap-1">
            <RouterLink
              v-slot="{ href, navigate }"
              to="/projects"
              custom
            >
              <a
                :href="href"
                data-cy="projects-index-button-sidebar"
                class="flex h-7 w-7 items-center justify-center rounded-md text-gray-400 opacity-0 transition-all duration-200 hover:bg-indigo-500/20 hover:text-indigo-300 group-hover:opacity-100"
                @click.stop="navigate"
              >
                <FolderOpenIcon class="h-4 w-4" />
              </a>
            </RouterLink>

            <span
              v-if="can('project:create')"
              data-cy="create-project-button-sidebar"
              class="flex h-7 w-7 items-center justify-center rounded-md text-gray-400 opacity-0 transition-all duration-200 hover:bg-indigo-500/20 hover:text-indigo-300 group-hover:opacity-100"
              @click.stop="project.create"
            >
              <PlusIcon class="h-4 w-4" />
            </span>
          </div>
        </div>
      </template>

      <template #content>
        <div class="mt-2 space-y-0.5">
          <template v-for="(item, index) in project.items.filter(i => i && i.id)" :key="item.id || index">
            <RouterLink
              v-slot="{ navigate, href, route }"
              :to="`/projects/${item.id}`"
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
                  :style="{ 'background-color': item.meta.color, 'box-shadow': `0 0 8px ${item.meta.color}40` }"
                ></span>
                <span class="flex-1 truncate">{{ item.name }}</span>

                <SidebarProjectMenu
                  :id="item.id"
                  :index="index"
                  :is-favorite="item.is_favorite"
                  class="group-hover:block"
                  :class="{ hidden: currentIndex != index }"
                  @toggle-menu="onToggle"
                />
              </a>
            </RouterLink>
          </template>
        </div>
      </template>
    </Collapsible>
  </section>
</template>

<script setup lang="ts">
  import SidebarProjectMenu from './ProjectMenu.vue'
  import { useProjectIndex } from 'Store/project'
  import { Collapsible } from 'thetheme'
  import { FolderOpenIcon, PlusIcon } from '@heroicons/vue/24/outline'
  import { useRouter } from 'vue-router'
  import { computed, inject, ref } from 'vue'

  const can = inject('can')
  const project = useProjectIndex(),
    router = useRouter(),
    path = computed(() => router.currentRoute.value.path),
    currentIndex = ref(null)

  function isActive(href: string): boolean {
    return path.value.startsWith(href)
  }

  function onToggle(data: any) {
    if (data.state) {
      currentIndex.value = data.index
    } else if (currentIndex.value == data.index) {
      currentIndex.value = null
    }
  }
</script>
