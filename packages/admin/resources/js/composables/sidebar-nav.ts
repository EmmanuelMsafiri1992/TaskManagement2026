import { AcademicCapIcon, BanknotesIcon, BriefcaseIcon, CalendarDaysIcon, CalendarIcon, ChartBarIcon, ChartPieIcon, ClipboardDocumentCheckIcon, ClipboardDocumentListIcon, ClockIcon, FilmIcon, HomeIcon, InboxIcon, UserPlusIcon, UsersIcon, WalletIcon } from '@heroicons/vue/24/outline'
import type { SidebarNav } from '@/types'

// Feature key mapping for sidebar items
const featureKeyMap: Record<string, string> = {
  '/': 'home',
  '/tasks': 'tasks',
  '/attendance': 'attendance',
  '/employees': 'employees',
  '/working-hours': 'working-hours',
  '/activity-reports': 'activity-reports',
  '/leaves': 'leaves',
  '/advance-requests': 'advance-requests',
  '/holidays': 'holidays',
  '/leads': 'leads',
  '/payrolls': 'payrolls',
  '/clients': 'clients',
  '/quotations': 'quotations',
  '/expenses': 'expenses',
  '/income': 'income',
  '/profit-loss': 'profit-loss',
  '/users': 'users',
  '/service-providers': 'service-providers',
  '/recording-sessions': 'service-providers',
  '/lesson-plans': 'service-providers',
  '/subjects': 'service-providers',
  '/adsense-reports': 'adsense-reports',
  '/video-enhancer': 'video-enhancer',
  '/audit-trail': 'audit-trail',
}

// Parent feature keys for dropdown menus
const parentFeatureKeyMap: Record<string, string> = {
  'Financial': 'financial',
  'Service Providers': 'service-providers',
}

export const useSidebarNav: SidebarNav[] = [
  { label: 'Home', uri: '/', icon: HomeIcon },
  { label: 'My Tasks', uri: '/tasks', icon: InboxIcon },
  {
    label: 'Attendance',
    uri: '/attendance',
    icon: CalendarIcon,
  },
  {
    label: 'Employees',
    uri: '/employees',
    icon: BriefcaseIcon,
    permission: 'employee:view',
  },
  {
    label: 'Working Hours',
    uri: '/working-hours',
    icon: ClockIcon,
    attendanceAdminOnly: true,
  },
  {
    label: 'Activity Reports',
    uri: '/activity-reports',
    icon: ChartPieIcon,
    attendanceAdminOnly: true,
  },
  {
    label: 'Leave Requests',
    uri: '/leaves',
    icon: ClipboardDocumentCheckIcon,
    permission: 'leave:view',
  },
  {
    label: 'Advance Requests',
    uri: '/advance-requests',
    icon: BanknotesIcon,
    permission: 'advance_request:view',
  },
  {
    label: 'Holidays',
    uri: '/holidays',
    icon: CalendarDaysIcon,
  },
  {
    label: 'Leads',
    uri: '/leads',
    icon: UserPlusIcon,
  },
  {
    label: 'Financial',
    icon: WalletIcon,
    children: [
      { label: 'Payroll', uri: '/payrolls', permission: 'payroll:view' },
      { label: 'Clients', uri: '/clients', permission: 'client:view' },
      { label: 'Quotations', uri: '/quotations', permission: 'quotation:view' },
      { label: 'Expenses', uri: '/expenses', permission: 'expense:view' },
      { label: 'Income', uri: '/income', permission: 'income:view' },
      { label: 'Profit & Loss', uri: '/profit-loss' },
    ],
  },
  {
    label: 'Team Members',
    uri: '/users',
    icon: UsersIcon,
    permission: 'user:view',
  },
  {
    label: 'Service Providers',
    icon: AcademicCapIcon,
    children: [
      { label: 'All Providers', uri: '/service-providers', permission: 'service_provider:view' },
      { label: 'Recording Sessions', uri: '/recording-sessions', permission: 'service_provider:view' },
      { label: 'Lesson Plans', uri: '/lesson-plans', permission: 'service_provider:view' },
      { label: 'Subjects', uri: '/subjects', permission: 'service_provider:view' },
    ],
  },
  {
    label: 'AdSense Reports',
    uri: '/adsense-reports',
    icon: ChartBarIcon,
  },
  {
    label: 'Video Enhancer',
    uri: '/video-enhancer',
    icon: FilmIcon,
  },
  {
    label: 'Audit Trail',
    uri: '/audit-trail',
    icon: ClipboardDocumentListIcon,
    permission: 'audit:view',
  },
]

// Helper function to check if a feature is disabled
export function isFeatureDisabled(uri: string | undefined, label: string, disabledFeatures: string[]): boolean {
  if (!disabledFeatures || disabledFeatures.length === 0) {
    return false
  }

  // Check if it's a parent dropdown
  const parentKey = parentFeatureKeyMap[label]
  if (parentKey && disabledFeatures.includes(parentKey)) {
    return true
  }

  // Check by uri
  if (uri && featureKeyMap[uri]) {
    return disabledFeatures.includes(featureKeyMap[uri])
  }

  return false
}

// Helper function to filter children based on disabled features
export function filterChildren(children: { label: string; uri: string; permission?: string }[], disabledFeatures: string[]) {
  if (!disabledFeatures || disabledFeatures.length === 0) {
    return children
  }

  return children.filter(child => {
    const featureKey = featureKeyMap[child.uri]
    return !featureKey || !disabledFeatures.includes(featureKey)
  })
}
