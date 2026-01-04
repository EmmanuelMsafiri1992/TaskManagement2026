import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { axios } from 'spack/axios'

interface InactivityReport {
  id: number
  inactive_from: string
  inactive_until: string
  inactive_from_full: string
  inactive_until_full: string
  duration: string
  duration_minutes: number
  reason_type: 'same_page' | 'computer_inactive' | 'power_outage' | 'session_gap'
  reason_label: string
  page_url: string | null
  page_title: string | null
  detected_at: string
}

interface ActivityTrackerOptions {
  heartbeatInterval?: number // in milliseconds
  inactivityThreshold?: number // in milliseconds
  checkPendingInterval?: number // in milliseconds
}

export function useActivityTracker(options: ActivityTrackerOptions = {}) {
  const {
    heartbeatInterval = 60000, // 1 minute
    inactivityThreshold = 300000, // 5 minutes (for local detection)
    checkPendingInterval = 30000, // 30 seconds
  } = options

  const router = useRouter()

  // State
  const sessionId = ref<string | null>(null)
  const isActive = ref(true)
  const lastActivityTime = ref(Date.now())
  const pendingReports = ref<InactivityReport[]>([])
  const showInactivityModal = ref(false)
  const currentReport = ref<InactivityReport | null>(null)
  const isInitialized = ref(false)

  // Timers
  let heartbeatTimer: ReturnType<typeof setInterval> | null = null
  let pendingCheckTimer: ReturnType<typeof setInterval> | null = null
  let inactivityCheckTimer: ReturnType<typeof setInterval> | null = null
  let awayStartTime: number | null = null

  /**
   * Generate a unique session ID.
   */
  function generateSessionId(): string {
    return `sess_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
  }

  /**
   * Get current page info.
   */
  function getPageInfo() {
    return {
      page_url: window.location.pathname + window.location.search,
      page_title: document.title,
    }
  }

  /**
   * Start a new session.
   */
  async function startSession() {
    try {
      sessionId.value = generateSessionId()
      const { page_url } = getPageInfo()

      await axios.post('activity/session/start', {
        session_id: sessionId.value,
        page_url,
      })

      isInitialized.value = true

      // Check for pending reports immediately
      await checkPendingReports()

      // Start timers
      startTimers()
    } catch (error) {
      console.error('Failed to start activity session:', error)
    }
  }

  /**
   * Send heartbeat to server.
   */
  async function sendHeartbeat() {
    if (!sessionId.value || !isActive.value) return

    try {
      const pageInfo = getPageInfo()

      await axios.post('activity/heartbeat', {
        session_id: sessionId.value,
        ...pageInfo,
      })
    } catch (error) {
      console.error('Failed to send heartbeat:', error)
    }
  }

  /**
   * Check for pending inactivity reports.
   */
  async function checkPendingReports() {
    try {
      const response = await axios.get('activity/pending')

      if (response.data.has_pending) {
        pendingReports.value = response.data.reports

        // Show modal for first pending report
        if (pendingReports.value.length > 0 && !showInactivityModal.value) {
          currentReport.value = pendingReports.value[0]
          showInactivityModal.value = true
        }
      } else {
        pendingReports.value = []
      }
    } catch (error) {
      console.error('Failed to check pending reports:', error)
    }
  }

  /**
   * Submit explanation for a report.
   */
  async function submitExplanation(reportId: number, explanation: string): Promise<boolean> {
    try {
      await axios.post(`activity/explain/${reportId}`, { explanation })

      // Remove from pending list
      pendingReports.value = pendingReports.value.filter((r) => r.id !== reportId)

      // Show next pending report or close modal
      if (pendingReports.value.length > 0) {
        currentReport.value = pendingReports.value[0]
      } else {
        showInactivityModal.value = false
        currentReport.value = null
      }

      return true
    } catch (error) {
      console.error('Failed to submit explanation:', error)
      return false
    }
  }

  /**
   * Report return from being away.
   */
  async function reportReturn() {
    if (!awayStartTime) return

    try {
      const pageInfo = getPageInfo()

      await axios.post('activity/return', {
        away_from: new Date(awayStartTime).toISOString(),
        ...pageInfo,
      })

      awayStartTime = null

      // Check for new pending reports
      await checkPendingReports()
    } catch (error) {
      console.error('Failed to report return:', error)
    }
  }

  /**
   * End session gracefully.
   */
  async function endSession() {
    if (!sessionId.value) return

    try {
      await axios.post('activity/session/end', {
        session_id: sessionId.value,
      })
    } catch (error) {
      console.error('Failed to end session:', error)
    }

    stopTimers()
    sessionId.value = null
  }

  /**
   * Handle user activity (mouse move, keypress, etc.).
   */
  function handleActivity() {
    const now = Date.now()

    // Check if user was away and is now back
    if (!isActive.value) {
      isActive.value = true
      reportReturn()
    }

    lastActivityTime.value = now
  }

  /**
   * Check for local inactivity.
   */
  function checkLocalInactivity() {
    const now = Date.now()
    const timeSinceActivity = now - lastActivityTime.value

    if (timeSinceActivity >= inactivityThreshold && isActive.value) {
      isActive.value = false
      awayStartTime = lastActivityTime.value
    }
  }

  /**
   * Start all timers.
   */
  function startTimers() {
    // Heartbeat timer
    heartbeatTimer = setInterval(sendHeartbeat, heartbeatInterval)

    // Pending reports check timer
    pendingCheckTimer = setInterval(checkPendingReports, checkPendingInterval)

    // Local inactivity check timer
    inactivityCheckTimer = setInterval(checkLocalInactivity, 10000) // Check every 10 seconds
  }

  /**
   * Stop all timers.
   */
  function stopTimers() {
    if (heartbeatTimer) {
      clearInterval(heartbeatTimer)
      heartbeatTimer = null
    }

    if (pendingCheckTimer) {
      clearInterval(pendingCheckTimer)
      pendingCheckTimer = null
    }

    if (inactivityCheckTimer) {
      clearInterval(inactivityCheckTimer)
      inactivityCheckTimer = null
    }
  }

  /**
   * Set up event listeners.
   */
  function setupEventListeners() {
    // Activity detection events
    const activityEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click']

    activityEvents.forEach((event) => {
      document.addEventListener(event, handleActivity, { passive: true })
    })

    // Visibility change (tab switch)
    document.addEventListener('visibilitychange', handleVisibilityChange)

    // Before unload (page close/refresh)
    window.addEventListener('beforeunload', handleBeforeUnload)

    // Return cleanup function
    return () => {
      activityEvents.forEach((event) => {
        document.removeEventListener(event, handleActivity)
      })
      document.removeEventListener('visibilitychange', handleVisibilityChange)
      window.removeEventListener('beforeunload', handleBeforeUnload)
    }
  }

  /**
   * Handle visibility change (tab switch).
   */
  function handleVisibilityChange() {
    if (document.hidden) {
      // User switched away from tab
      awayStartTime = Date.now()
      isActive.value = false
    } else {
      // User returned to tab
      if (awayStartTime) {
        const awayDuration = Date.now() - awayStartTime

        // Only report if away for more than threshold
        if (awayDuration >= inactivityThreshold) {
          reportReturn()
        } else {
          awayStartTime = null
        }
      }
      isActive.value = true
      lastActivityTime.value = Date.now()
    }
  }

  /**
   * Handle before unload.
   */
  function handleBeforeUnload() {
    // Use sendBeacon for reliable delivery
    if (sessionId.value) {
      const data = JSON.stringify({ session_id: sessionId.value })
      navigator.sendBeacon('/api/activity/session/end', data)
    }
  }

  // Watch for route changes
  watch(
    () => router.currentRoute.value.path,
    () => {
      // Send heartbeat on page change
      if (isInitialized.value) {
        sendHeartbeat()
      }
    }
  )

  // Lifecycle
  onMounted(() => {
    const cleanup = setupEventListeners()
    startSession()

    onUnmounted(() => {
      cleanup()
      stopTimers()
      endSession()
    })
  })

  return {
    // State
    sessionId,
    isActive,
    lastActivityTime,
    pendingReports,
    showInactivityModal,
    currentReport,
    isInitialized,

    // Methods
    submitExplanation,
    checkPendingReports,
  }
}
