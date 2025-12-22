<template>
  <div class="hidden md:flex md:flex-shrink-0">
    <div class="flex w-64 flex-col border-r border-gray-700/50 bg-gradient-to-b from-gray-900 via-gray-900 to-gray-800 shadow-2xl">
      <div class="flex h-0 flex-1 flex-col">
        <!-- Logo Header with gradient border -->
        <RouterLink
          to="/"
          class="group relative flex h-16 flex-shrink-0 items-center border-b border-gray-700/50 bg-gray-900/80 px-4 backdrop-blur-sm transition-all duration-200 hover:bg-gray-800/80"
        >
          <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent opacity-0 transition-opacity duration-200 group-hover:opacity-100"></div>
          <SidebarLogo />
        </RouterLink>

        <!-- Navigation Area with custom scrollbar -->
        <div class="sidebar-nav flex flex-1 flex-col overflow-y-auto scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-gray-700 hover:scrollbar-thumb-gray-600">
          <nav class="flex-1 space-y-1 px-3 py-4">
            <SidebarNav />
            <SidebarFavorites />
            <SidebarProjects />
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import SidebarNav from './SidebarNav.vue'
  import SidebarLogo from './SidebarLogo.vue'
  import SidebarProjects from './projects/Index.vue'
  import SidebarFavorites from './favorites/Index.vue'
</script>
