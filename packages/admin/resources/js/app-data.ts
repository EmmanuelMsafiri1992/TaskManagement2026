import { reactive } from 'vue'
import type { AppData } from 'types'

export const appData: AppData = reactive({ ...window.AppData })
