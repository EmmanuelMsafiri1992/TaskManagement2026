import { defineComponent } from 'vue'
import { RouterView } from 'vue-router'
import SidebarMobile from './SidebarMobile.vue'
import TheHeader from './Header.vue'
import TheSidebar from './Sidebar.vue'
import ImpersonationBanner from '@/thetheme/components/ImpersonationBanner.vue'
import InactivityModal from '@/components/InactivityModal.vue'
import { FlashMessage, Modals } from 'thetheme'
import { useActivityTracker } from '@/composables/useActivityTracker'

export default defineComponent({
  setup() {
    // Initialize activity tracking
    const {
      showInactivityModal,
      currentReport,
      pendingReports,
      submitExplanation,
    } = useActivityTracker()

    const handleExplanationSubmit = async (reportId: number, explanation: string) => {
      await submitExplanation(reportId, explanation)
    }

    return () => (
      <>
        <ImpersonationBanner />
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

        {/* Inactivity Modal - cannot be closed until explanation is provided */}
        <InactivityModal
          show={showInactivityModal.value}
          report={currentReport.value}
          pendingCount={pendingReports.value.length}
          onSubmit={handleExplanationSubmit}
        />
      </>
    )
  },
})
