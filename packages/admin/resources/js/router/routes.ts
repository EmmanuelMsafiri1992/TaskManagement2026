import type { RouteRecordRaw } from 'vue-router'
import NotFound from 'View/errors/NotFound.vue'
import Forbidden from 'View/errors/Forbidden.vue'
import Home from 'View/Home.vue'
import Profile from 'View/Profile.vue'
import ProjectsDetail from 'View/projects/Detail.vue'
import SettingsEmail from 'View/settings/Email.vue'
import SettingsGeneral from 'View/settings/General.vue'
import SettingsAdSense from 'View/settings/AdSense.vue'
import SettingsCountries from 'View/settings/Countries.vue'
import SettingsUserAssignments from 'View/settings/UserAssignments.vue'
import RolesIndex from 'View/roles/Index.vue'
import TasksIndex from 'View/tasks/Index.vue'
import UsersIndex from 'View/users/Index.vue'
import LabelsIndex from 'View/labels/Index.vue'
import ProjectsIndex from 'View/projects/Index.vue'
import AdSenseReports from 'View/AdSenseReports.vue'
import AttendanceIndex from 'View/attendance/IndexWorking.vue'
import AttendanceReports from 'View/attendance/Reports.vue'
import EmployeesIndex from 'View/employees/Index.vue'
import EmployeesDetail from 'View/employees/Detail.vue'
import LeavesIndex from 'View/leaves/Index.vue'
import HolidaysIndex from 'View/holidays/Index.vue'
import PayrollsIndex from 'View/payrolls/Index.vue'
import ClientsIndex from 'View/clients/Index.vue'
import QuotationsIndex from 'View/quotations/Index.vue'
import QuotationsDetail from 'View/quotations/Detail.vue'
import ExpensesIndex from 'View/expenses/Index.vue'
import IncomeIndex from 'View/income/Index.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', component: Home },
  { path: '/profile', component: Profile },
  { path: '/projects', name: 'ProjectsIndex', component: ProjectsIndex },
  { path: '/projects/:id', name: 'ProjectsDetail', component: ProjectsDetail },
  {
    path: '/projects/:id/tasks/:taskId',
    name: 'ProjectsDetailTask',
    component: ProjectsDetail,
    props: true,
  },
  { path: '/users', name: 'Users', component: UsersIndex },
  { path: '/labels', name: 'Labels', component: LabelsIndex },
  { path: '/roles', name: 'Roles', component: RolesIndex },
  { path: '/tasks', name: 'Tasks', component: TasksIndex },
  { path: '/attendance', name: 'Attendance', component: AttendanceIndex },
  { path: '/attendance-page', name: 'AttendancePage', component: AttendanceIndex },
  { path: '/attendance-reports', name: 'AttendanceReports', component: AttendanceReports },
  { path: '/employees', name: 'Employees', component: EmployeesIndex },
  { path: '/employees/:id', name: 'EmployeeDetail', component: EmployeesDetail },
  { path: '/leaves', name: 'Leaves', component: LeavesIndex },
  { path: '/holidays', name: 'Holidays', component: HolidaysIndex },
  { path: '/payrolls', name: 'Payrolls', component: PayrollsIndex },
  { path: '/clients', name: 'Clients', component: ClientsIndex },
  { path: '/quotations', name: 'Quotations', component: QuotationsIndex },
  { path: '/quotations/:id', name: 'QuotationDetail', component: QuotationsDetail },
  { path: '/expenses', name: 'Expenses', component: ExpensesIndex },
  { path: '/income', name: 'Income', component: IncomeIndex },
  { path: '/adsense-reports', name: 'AdSenseReports', component: AdSenseReports },
  { path: '/settings/general', component: SettingsGeneral },
  { path: '/settings/email', component: SettingsEmail },
  { path: '/settings/adsense', component: SettingsAdSense },
  { path: '/settings/countries', component: SettingsCountries },
  { path: '/settings/user-assignments', component: SettingsUserAssignments },

  { path: '/403', name: 'Forbidden', component: Forbidden },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
]
