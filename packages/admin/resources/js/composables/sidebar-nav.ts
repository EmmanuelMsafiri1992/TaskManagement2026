import { AcademicCapIcon, BriefcaseIcon, CalendarDaysIcon, CalendarIcon, ChartBarIcon, ClipboardDocumentCheckIcon, ClipboardDocumentListIcon, ClockIcon, FilmIcon, HomeIcon, InboxIcon, UsersIcon, WalletIcon } from '@heroicons/vue/24/outline'
import type { SidebarNav } from '@/types'

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
    label: 'Leave Requests',
    uri: '/leaves',
    icon: ClipboardDocumentCheckIcon,
    permission: 'leave:view',
  },
  {
    label: 'Holidays',
    uri: '/holidays',
    icon: CalendarDaysIcon,
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
