import type { FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue'

declare global {
  interface Window {
    AppData: AppData
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    __: (word: string) => string
    can: (permission: string | undefined) => boolean
    cannot: (permission: string | undefined) => boolean
  }
}

interface AppData {
  prefix: string
  app_name: string
  app_logo: string
  is_super_admin: boolean
  permissions: string[]
  translations: any
  locale: string
  csrf_token: string
  options: any
  PUSHER_APP_KEY: string
  PUSHER_APP_CLUSTER: string
  user: {
    id: number
    name: string
    email: string
    avatar: string
  }
}

interface ChartTasksWeekly {
  Mon: number
  Tue: number
  Wed: number
  Thu: number
  Fri: number
  Sat: number
  Sun: number
}

interface ChartTasksYearly {
  Jan: number
  Feb: number
  Mar: number
  Apr: number
  May: number
  Jun: number
  Jul: number
  Aug: number
  Sep: number
  Oct: number
  Nov: number
  Dec: number
}

interface SidebarNav {
  label: string
  uri: string
  icon: FunctionalComponent<HTMLAttributes & VNodeProps>
  permission?: string
  create?: string
  createPermission?: string
}

interface ProjectForm {
  name: string
  color: string
  users: number[]
}

interface ArchivedProject {
  id: number
  name: string
  meta: any
}

interface TaskI {
  id: number
  project_id: number
  project: any
  title: string
  description: string
  completed_at: string
  deleted_at: string | null
  recurring_at: string
  total_seconds: number
  checklists: any
  project_list: any
  users: any
  labels: any
  meta: any
  due_at: string
  human_due_date: string
  comments: any
}

interface TaskOptions {
  project_lists: any
  labels: any
}

interface ProjectI {
  id: number
  name: string
  lists: any
  is_favorite: boolean
  users: any
  meta: any
}

interface ProfileForm {
  password: string
  avatar: string
}

interface SettingsGeneralForm {
  app_logo: string | null
}

interface RoleForm {
  name: string
  permissions: any
}
