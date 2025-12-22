/**
 * Notification Sound Utility
 * Handles playing notification sounds when new notifications arrive
 */

let audioContext: AudioContext | null = null
let isEnabled = true

// Initialize Web Audio API context
async function initAudioContext() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || (window as any).webkitAudioContext)()
  }

  // Resume AudioContext if it's suspended (browser autoplay policy)
  if (audioContext.state === 'suspended') {
    try {
      await audioContext.resume()
    } catch (error) {
      console.warn('Could not resume AudioContext:', error)
    }
  }

  return audioContext
}

// Generate a pleasant notification sound using Web Audio API
async function generateNotificationSound() {
  const context = await initAudioContext()
  const oscillator = context.createOscillator()
  const gainNode = context.createGain()

  oscillator.connect(gainNode)
  gainNode.connect(context.destination)

  // Pleasant notification tone (two-note chime)
  oscillator.frequency.value = 800 // Hz
  oscillator.type = 'sine'

  // Envelope for smooth sound
  const now = context.currentTime
  gainNode.gain.setValueAtTime(0, now)
  gainNode.gain.linearRampToValueAtTime(0.3 * getNotificationVolume(), now + 0.01)
  gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3)

  oscillator.start(now)
  oscillator.stop(now + 0.3)

  // Second tone (higher pitch)
  setTimeout(() => {
    const osc2 = context.createOscillator()
    const gain2 = context.createGain()

    osc2.connect(gain2)
    gain2.connect(context.destination)

    osc2.frequency.value = 1000 // Hz
    osc2.type = 'sine'

    const now2 = context.currentTime
    gain2.gain.setValueAtTime(0, now2)
    gain2.gain.linearRampToValueAtTime(0.3 * getNotificationVolume(), now2 + 0.01)
    gain2.gain.exponentialRampToValueAtTime(0.01, now2 + 0.3)

    osc2.start(now2)
    osc2.stop(now2 + 0.3)
  }, 150)
}

/**
 * Play notification sound
 */
export async function playNotificationSound() {
  if (!isEnabled) return

  try {
    await generateNotificationSound()
  } catch (error) {
    console.warn('Error playing notification sound:', error)
  }
}

/**
 * Enable notification sounds
 */
export function enableNotificationSound() {
  isEnabled = true
  localStorage.setItem('notification_sound_enabled', 'true')
}

/**
 * Disable notification sounds
 */
export function disableNotificationSound() {
  isEnabled = false
  localStorage.setItem('notification_sound_enabled', 'false')
}

/**
 * Toggle notification sound on/off
 */
export function toggleNotificationSound() {
  isEnabled = !isEnabled
  localStorage.setItem('notification_sound_enabled', isEnabled ? 'true' : 'false')
  return isEnabled
}

/**
 * Check if notification sound is enabled
 */
export function isNotificationSoundEnabled() {
  const stored = localStorage.getItem('notification_sound_enabled')
  if (stored !== null) {
    isEnabled = stored === 'true'
  }
  return isEnabled
}

/**
 * Set notification volume (0.0 to 1.0)
 */
export function setNotificationVolume(volume: number) {
  const clampedVolume = Math.max(0, Math.min(1, volume))
  localStorage.setItem('notification_volume', clampedVolume.toString())
}

/**
 * Get current notification volume
 */
export function getNotificationVolume() {
  const stored = localStorage.getItem('notification_volume')
  return stored ? parseFloat(stored) : 0.5
}

// Initialize on load
if (typeof window !== 'undefined') {
  isNotificationSoundEnabled()
}
