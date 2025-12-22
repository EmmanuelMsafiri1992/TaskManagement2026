import { defineComponent } from 'vue'
import { RouterView } from 'vue-router'
import SidebarMobile from './SidebarMobile.vue'
import TheHeader from './Header.vue'
import TheSidebar from './Sidebar.vue'
import { FlashMessage, Modals } from 'thetheme'

export default defineComponent({
  setup() {
    return () => (
      <div class="flex h-screen overflow-hidden bg-gray-100">
        <SidebarMobile />

        <TheSidebar />

        <div class="flex w-0 flex-1 flex-col overflow-hidden">
          <TheHeader />

          <main class="relative flex-1 overflow-y-auto px-8 py-6">
            <RouterView />
          </main>
        </div>

        <Modals />
        <FlashMessage />
      </div>
    )
  },
})
