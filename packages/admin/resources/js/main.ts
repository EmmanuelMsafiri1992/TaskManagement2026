console.log('[Main] JavaScript file EXECUTING - Step 1')

import { app } from './app'
console.log('[Main] app imported - Step 2')

import { createPinia } from 'pinia'
console.log('[Main] pinia imported - Step 3')

import { router } from './router'
console.log('[Main] router imported - Step 4')

import SpackPlugin from 'spack/plugin'
console.log('[Main] SpackPlugin imported - Step 5')

import 'thetheme/thetheme'
console.log('[Main] thetheme imported - Step 6')

import 'cooltipz-css'
console.log('[Main] cooltipz imported - Step 7')

import 'flatpickr/dist/flatpickr.css'
console.log('[Main] flatpickr imported - Step 8')

import 'spack/style.css'
console.log('[Main] spack/style imported - Step 9')

import './style.css'
console.log('[Main] style.css imported - Step 10')

console.log('[Main] All imports complete - Step 11')
console.log('[Main] App:', app)
console.log('[Main] Router:', router)
console.log('[Main] SpackPlugin:', SpackPlugin)

try {
  console.log('[Main] Mounting app - Step 12')
  app.use(createPinia()).use(SpackPlugin).use(router).mount('#app')
  console.log('[Main] App mounted successfully - Step 13')
} catch (error) {
  console.error('[Main] MOUNT ERROR:', error)
  alert('Mount Error: ' + error.message)
}
