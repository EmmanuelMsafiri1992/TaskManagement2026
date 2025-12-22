import { inject } from 'vue'

export const can = (permission: string): boolean => {
  const canFn = inject<(permission: string) => boolean>('can')
  return canFn ? canFn(permission) : false
}

export const cannot = (permission: string): boolean => {
  const cannotFn = inject<(permission: string) => boolean>('cannot')
  return cannotFn ? cannotFn(permission) : true
}
