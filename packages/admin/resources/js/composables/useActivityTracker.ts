import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { axios } from 'spack/axios'

interface ActivitySettings {
  enabled: boolean
  exception_urls: string[]
  same_page_threshold: number
  inactivity_threshold: number
  heartbeat_interval: number
  lunch_start: string
  lunch_end: string
  is_clocked_in: boolean
}

interface ActivityTrackerOptions {
  heartbeatInterval?: number // in milliseconds
  inactivityThreshold?: number // in milliseconds
}

export function useActivityTracker(options: ActivityTrackerOptions = {}) {
  const router = useRouter()

  // State
  const sessionId = ref<string | null>(null)
  const isActive = ref(true)
  const lastActivityTime = ref(Date.now())
  const isInitialized = ref(false)
  const settings = ref<ActivitySettings | null>(null)
  const isMonitoringEnabled = ref(true)
  const isClockedIn = ref(false)

  // Timers
  let heartbeatTimer: ReturnType<typeof setInterval> | null = null
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
      page_url: window.location.href,
      page_title: document.title,
    }
  }

  /**
   * Check if current URL is an exception URL (company pages).
   */
  function isExceptionUrl(): boolean {
    if (!settings.value?.exception_urls) return false

    const currentHost = window.location.hostname.replace(/^www\./, '')

    return settings.value.exception_urls.some((url) => {
      try {
        const exceptionHost = new URL(url).hostname.replace(/^www\./, '')
        return currentHost === exceptionHost
      } catch {
        return false
      }
    })
  }

  /**
   * Fetch activity monitoring settings.
   */
  async function fetchSettings() {
    try {
      const response = await axios.get('activity/settings')
      settings.value = response.data
      isMonitoringEnabled.value = response.data.enabled ?? true
      isClockedIn.value = response.data.is_clocked_in ?? false
      return response.data
    } catch (error) {
      console.error('Failed to fetch activity settings:', error)
      return null
    }
  }

  /**
   * Start a new session.
   */
  async function startSession() {
    try {
      // First fetch settings
      await fetchSettings()

      // If monitoring is disabled, don't start tracking
      if (!isMonitoringEnabled.value) {
        console.log('Activity monitoring is disabled')
        return
      }

      // If user is not clocked in, don't start tracking
      if (!isClockedIn.value) {
        console.log('User is not clocked in, skipping activity tracking')
        return
      }

      // If on exception URL, don't start tracking
      if (isExceptionUrl()) {
        console.log('On exception URL, skipping activity tracking')
        return
      }

      sessionId.value = generateSessionId()
      const { page_url } = getPageInfo()

      await axios.post('activity/session/start', {
        session_id: sessionId.value,
        page_url,
      })

      isInitialized.value = true

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

    // Skip if on exception URL
    if (isExceptionUrl()) return

    // Skip if monitoring is disabled
    if (!isMonitoringEnabled.value) return

    // Skip if user is not clocked in
    if (!isClockedIn.value) return

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
   * Report return from being away.
   */
  async function reportReturn() {
    if (!awayStartTime) return

    // Skip if on exception URL, monitoring disabled, or not clocked in
    if (isExceptionUrl() || !isMonitoringEnabled.value || !isClockedIn.value) {
      awayStartTime = null
      return
    }

    try {
      const pageInfo = getPageInfo()

      await axios.post('activity/return', {
        away_from: new Date(awayStartTime).toISOString(),
        ...pageInfo,
      })

      awayStartTime = null
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
    // Skip if monitoring disabled, on exception URL, or not clocked in
    if (!isMonitoringEnabled.value || isExceptionUrl() || !isClockedIn.value) return

    const inactivityThreshold = (settings.value?.inactivity_threshold ?? 5) * 60 * 1000 // Convert minutes to ms
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
    const heartbeatInterval = (settings.value?.heartbeat_interval ?? 60) * 1000 // Convert seconds to ms

    // Heartbeat timer
    heartbeatTimer = setInterval(sendHeartbeat, heartbeatInterval)

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
        const inactivityThreshold = (settings.value?.inactivity_threshold ?? 5) * 60 * 1000
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
    isInitialized,
    settings,
    isMonitoringEnabled,
    isClockedIn,
  }
}
