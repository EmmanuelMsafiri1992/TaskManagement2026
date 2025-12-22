import type { App } from 'vue'
import { appData } from '../app-data'

export function install(app: App) {
  console.log('[SpackPlugin] Installing plugin')
  console.log('[SpackPlugin] AppData:', appData)

  // Translation helper
  app.config.globalProperties.__ = (word: string): string => {
    return appData.translations?.[word] || word
  }

  // Permission helpers
  const can = (permission: string | undefined): boolean => {
    if (!permission) return true
    if (appData.is_super_admin) return true
    return appData.permissions?.includes(permission) || false
  }

  const cannot = (permission: string | undefined): boolean => {
    return !can(permission)
  }

  app.config.globalProperties.can = can
  app.config.globalProperties.cannot = cannot

  // Also provide via inject/provide for composition API
  app.provide('can', can)
  app.provide('cannot', cannot)
  app.provide('__', app.config.globalProperties.__)
  app.provide('user', { ...appData.user, is_super_admin: appData.is_super_admin })

  console.log('[SpackPlugin] Functions provided:', {
    can: typeof can,
    cannot: typeof cannot,
    __: typeof app.config.globalProperties.__
  })
}

export default { install }
