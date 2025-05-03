import SuperAdminLayout from 'layouts/SuperAdminLayout.vue'

const routes = [
  {
    path: '/super-admin',
    component: SuperAdminLayout,
    children: [
      {
        path: '',
        redirect: '/super-admin/dashboard'
      },
      {
        path: 'dashboard',
        component: () => import('pages/super-admin/DashboardPage.vue')
      },
      {
        path: 'businesses',
        component: () => import('pages/super-admin/businesses/BusinessesPage.vue')
      },
      
      {
        path: 'businesses/create',
        component: () => import('pages/super-admin/businesses/CreateBusinessPage.vue')
      },
      {
        path: 'businesses/:id',
        component: () => import('pages/super-admin/businesses/BusinessDetailsPage.vue')
      },
      {
        path: 'businesses/:id/edit',
        component: () => import('pages/super-admin/businesses/EditBusinessPage.vue')
      },
      {
        path: 'businesses/:id/branches',
        component: () => import('pages/super-admin/branches/BranchesPage.vue')
      },
      {
        path: 'reports',
        component: () => import('pages/super-admin/reports/ReportsPage.vue')
      },
      {
        path: 'reports/sales',
        component: () => import('pages/super-admin/reports/SalesReportPage.vue')
      },
      {
        path: 'reports/revenue',
        component: () => import('pages/super-admin/reports/RevenueReportPage.vue')
      },
      {
        path: 'reports/business',
        component: () => import('pages/super-admin/reports/BusinessReportPage.vue')
      },
      {
        path: 'users',
        component: () => import('pages/super-admin/users/UsersPage.vue')
      },
      {
        path: 'users/create',
        component: () => import('pages/super-admin/users/CreateUserPage.vue')
      },
      {
        path: 'users/:id',
        component: () => import('pages/super-admin/users/UserDetailsPage.vue')
      },
      {
        path: 'users/:id/edit',
        component: () => import('pages/super-admin/users/EditUserPage.vue')
      },
      {
        path: 'settings',
        component: () => import('pages/super-admin/settings/SettingsPage.vue')
      }
    ]
  }
]

export default routes 