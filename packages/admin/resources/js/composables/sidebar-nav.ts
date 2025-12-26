import { BanknotesIcon, BriefcaseIcon, CalendarDaysIcon, CalendarIcon, ChartBarIcon, ClipboardDocumentCheckIcon, CurrencyDollarIcon, DocumentChartBarIcon, DocumentTextIcon, FilmIcon, HomeIcon, InboxIcon, ReceiptPercentIcon, UserGroupIcon, UserIcon, UsersIcon } from '@heroicons/vue/24/outline'
import type { SidebarNav } from '@/types'

export const useSidebarNav: SidebarNav[] = [
  { label: 'Home', uri: '/', icon: HomeIcon },
  { label: 'My Tasks', uri: '/tasks', icon: InboxIcon },
  {
    label: 'Attendance',
    uri: '/attendance-page',
    icon: CalendarIcon,
  },
  {
    label: 'Attendance Reports',
    uri: '/attendance-reports',
    icon: DocumentChartBarIcon,
    permission: 'attendance:view-all',
  },
  {
    label: 'Employees',
    uri: '/employees',
    icon: BriefcaseIcon,
    permission: 'employee:view',
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
    label: 'Payroll',
    uri: '/payrolls',
    icon: BanknotesIcon,
    permission: 'payroll:view',
  },
  {
    label: 'Clients',
    uri: '/clients',
    icon: UserGroupIcon,
    permission: 'client:view',
  },
  {
    label: 'Quotations',
    uri: '/quotations',
    icon: DocumentTextIcon,
    permission: 'quotation:view',
  },
  {
    label: 'Expenses',
    uri: '/expenses',
    icon: ReceiptPercentIcon,
    permission: 'expense:view',
  },
  {
    label: 'Income',
    uri: '/income',
    icon: CurrencyDollarIcon,
    permission: 'income:view',
  },
  {
    label: 'Team Members',
    uri: '/users',
    icon: UsersIcon,
    permission: 'user:view',
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
]
