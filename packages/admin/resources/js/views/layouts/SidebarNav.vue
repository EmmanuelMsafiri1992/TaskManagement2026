<template>
  <div class="space-y-1">
    <template v-for="item in useSidebarNav" :key="item.label">
      <!-- Dropdown Menu Item -->
      <div v-if="item.children && shouldShowDropdown(item)">
        <button
          type="button"
          :class="[
            isDropdownActive(item.children)
              ? 'bg-gray-700/50 text-white'
              : 'text-gray-300 hover:bg-gray-700/70 hover:text-white',
            'group relative flex w-full items-center rounded-lg py-2.5 px-3 text-sm font-medium transition-all duration-200 ease-in-out border-l-4 border-transparent',
          ]"
          @click="toggleDropdown(item.label)"
        >
          <Component
            :is="item.icon"
            :class="[
              isDropdownActive(item.children)
                ? 'text-indigo-300'
                : 'text-gray-400 group-hover:text-indigo-300',
              'h-5 w-5 flex-shrink-0 ltr:mr-3 rtl:ml-3 transition-colors duration-200',
            ]"
            aria-hidden="true"
          />
          <span class="flex-1 text-left">{{ __(item.label) }}</span>
          <ChevronDownIcon
            :class="[
              openDropdowns[item.label] ? 'rotate-180' : '',
              'h-4 w-4 text-gray-400 transition-transform duration-200',
            ]"
          />
        </button>

        <!-- Dropdown Children -->
        <div
          v-show="openDropdowns[item.label]"
          class="mt-1 space-y-1 pl-4"
        >
          <template v-for="child in getFilteredChildren(item.children)" :key="child.label">
            <RouterLink
              v-if="!child.permission || can(child.permission)"
              v-slot="{ href, navigate }"
              custom
              :to="child.uri"
            >
              <a
                :href="href"
                :class="[
                  isActive(child.uri)
                    ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 border-l-4 border-indigo-400'
                    : 'text-gray-300 hover:bg-gray-700/70 hover:text-white hover:border-l-4 hover:border-gray-500/50 border-l-4 border-transparent',
                  'group relative flex items-center rounded-lg py-2 px-3 text-sm font-medium transition-all duration-200 ease-in-out',
                ]"
                @click="navigate"
              >
                <div
                  v-if="isActive(child.uri)"
                  class="absolute inset-0 -z-10 rounded-lg bg-indigo-500/20 blur-xl"
                ></div>
                <span class="flex-1">{{ __(child.label) }}</span>
              </a>
            </RouterLink>
          </template>
        </div>
      </div>

      <!-- Regular Menu Item -->
      <RouterLink
        v-else-if="item.uri && shouldShowItem(item)"
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
          @click="navigate"
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
  import { computed, inject, onMounted, reactive } from 'vue'
  import { useSidebarNav, isFeatureDisabled, filterChildren } from 'Use/sidebar-nav'
  import { useRouter } from 'vue-router'
  import { ChevronDownIcon, PlusIcon } from '@heroicons/vue/24/outline'
  import { appData } from '@/app-data'

  const disabledFeatures = computed(() => appData.disabled_sidebar_features || [])

  const can = inject('can') as (permission: string | undefined) => boolean
  const __ = inject('__') as (word: string) => string
  const router = useRouter()
  const path = computed(() => router.currentRoute.value.path)

  const openDropdowns = reactive<Record<string, boolean>>({})

  function isActive(uri: string) {
    if (uri === '/' || uri == '/projects') {
      return path.value === uri
    }

    return path.value.startsWith(uri)
  }

  function isDropdownActive(children: Array<{ uri: string }>) {
    return children.some(child => isActive(child.uri))
  }

  function hasAnyChildPermission(children: Array<{ permission?: string }>) {
    return children.some(child => !child.permission || can(child.permission))
  }

  function toggleDropdown(label: string) {
    openDropdowns[label] = !openDropdowns[label]
  }

  function shouldShowItem(item: { uri?: string; label: string; permission?: string; superAdminOnly?: boolean; attendanceAdminOnly?: boolean }) {
    // Check if the feature is disabled
    if (isFeatureDisabled(item.uri, item.label, disabledFeatures.value)) {
      return false
    }
    // Check super admin only first
    if (item.superAdminOnly && !appData.is_super_admin) {
      return false
    }
    // Check attendance admin (emmanuel@emphxs.com or super admin)
    if (item.attendanceAdminOnly && !appData.is_attendance_admin) {
      return false
    }
    // Then check permission
    if (item.permission && !can(item.permission)) {
      return false
    }
    return true
  }

  // Check if a dropdown menu should be visible (has visible children after filtering)
  function shouldShowDropdown(item: { label: string; children?: { label: string; uri: string; permission?: string }[] }) {
    if (isFeatureDisabled(undefined, item.label, disabledFeatures.value)) {
      return false
    }
    if (!item.children) return false
    const filteredChildren = getFilteredChildren(item.children)
    return filteredChildren.length > 0 && hasAnyChildPermission(filteredChildren)
  }

  // Get filtered children for a dropdown menu
  function getFilteredChildren(children: { label: string; uri: string; permission?: string }[]) {
    return filterChildren(children, disabledFeatures.value)
  }

  function create(uri: string) {
    router.push(uri)
  }

  // Auto-open dropdown if a child is active
  onMounted(() => {
    useSidebarNav.forEach(item => {
      if (item.children && isDropdownActive(item.children)) {
        openDropdowns[item.label] = true
      }
    })
  })
</script>
