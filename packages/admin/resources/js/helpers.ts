import { appData } from './app-data'

export const can = (permission: string): boolean => {
  if (!permission) return true
  if (appData.is_super_admin) return true
  return appData.permissions?.includes(permission) || false
}

export const cannot = (permission: string): boolean => {
  return !can(permission)
}
