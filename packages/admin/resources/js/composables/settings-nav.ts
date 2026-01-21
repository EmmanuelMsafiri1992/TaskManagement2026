interface SettingsNav {
  label: string
  uri: string
  permission?: string
}

export const useSettingsNav: SettingsNav[] = [
  { label: 'General', uri: '/settings/general', permission: 'setting:general' },
  { label: 'Email', uri: '/settings/email', permission: 'setting:email' },
  { label: 'AdSense', uri: '/settings/adsense', permission: 'setting:adsense' },
  { label: 'Countries', uri: '/settings/countries', permission: 'setting:general' },
  { label: 'User Assignments', uri: '/settings/user-assignments', permission: 'setting:general' },
  { label: 'Sidebar Features', uri: '/settings/sidebar-features', permission: 'setting:general' },
  { label: 'Labels', uri: '/labels', permission: 'label:view' },
  { label: 'Roles & Permissions', uri: '/roles', permission: 'role:view' },
]
