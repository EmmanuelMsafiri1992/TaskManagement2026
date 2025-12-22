import { appData } from '@/app-data'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

console.log('[Router] Creating router with prefix:', appData.prefix)
console.log('[Router] Routes:', routes)

export const router = createRouter({
  history: createWebHistory(appData.prefix),
  routes,
})

router.beforeEach((to, from, next) => {
  console.log('[Router] Navigating from:', from.path, 'to:', to.path)
  console.log('[Router] Route matched:', to.matched.length > 0 ? 'YES' : 'NO')
  console.log('[Router] Route name:', to.name)
  next()
})

router.afterEach((to, from) => {
  console.log('[Router] Navigation complete:', to.path)
})

router.onError((error) => {
  console.error('[Router] Navigation ERROR:', error)
  console.error('[Router] Error stack:', error.stack)
})
