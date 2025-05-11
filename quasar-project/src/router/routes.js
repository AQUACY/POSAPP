import { Platform } from 'quasar'

const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') }
    ]
  },

  // Admin Routes
  {
    path: '/business/:businessId',
    component: () => import('layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', redirect: to => `/business/${to.params.businessId}/dashboard` },
      { path: 'dashboard', component: () => import('pages/admin/Dashboard.vue') },
      { path: 'branches', component: () => import('pages/admin/Branches.vue') },
      { path: 'branch/:branchId/inventory', component: () => import('pages/admin/BranchInventory.vue') },
      { path: 'branch/:branchId/staff', component: () => import('pages/admin/BranchStaff.vue') },
      { path: 'warehouse/:warehouseId/stock-requests', component: () => import('pages/admin/StockRequests.vue') },
      { path: 'warehouses', component: () => import('pages/admin/Warehouses.vue') },
      { path: 'inventory', component: () => import('pages/admin/Inventory.vue') },
      { path: 'categories', component: () => import('pages/admin/Categories.vue') },
      { path: 'sales', component: () => import('pages/admin/Sales.vue') },
      { path: 'refunds', component: () => import('pages/admin/Refunds.vue') },
      { path: 'reports', component: () => import('pages/admin/Reports.vue') },
      { path: 'settings', component: () => import('pages/admin/Settings.vue') }
    ]
  },

  // Branch Manager routes
  {
    path: '/branch/:businessId/:branchId',
    component: () => Platform.is.mobile ? import('layouts/MobileLayout.vue') : import('layouts/BranchManagerLayout.vue'),
    meta: { requiresAuth: true, role: 'branch_manager' },
    children: [
      {
        path: '',
        redirect: to => `/branch/${to.params.businessId}/${to.params.branchId}/dashboard`
      },
      {
        path: 'dashboard',
        component: () => import('pages/branch-manager/Dashboard.vue')
      },
      {
        path: 'inventory',
        component: () => import('pages/branch-manager/Inventory.vue')
      },
      {
        path: 'sales',
        component: () => import('pages/branch-manager/Sales.vue')
      },
      {
        path: 'customers',
        component: () => import('pages/branch-manager/Customers.vue')
      },
      {
        path: 'staff',
        component: () => import('pages/branch-manager/Staff.vue')
      },
      {
        path: 'reports',
        component: () => import('pages/branch-manager/Reports.vue')
      },
      {
        path: 'settings',
        component: () => import('pages/branch-manager/Settings.vue')
      },
      {
        path: 'stock-requests',
        name: 'branch-stock-requests',
        component: () => import('pages/branch-manager/StockRequests.vue')
      },
      {
        path: 'more',
        component: () => import('pages/More.vue')
      }
    ]
  },

  // Cashier routes
  {
    path: '/pos/:businessId/:branchId',
    component: () => Platform.is.mobile ? import('layouts/MobileLayout.vue') : import('layouts/CashierLayout.vue'),
    meta: { requiresAuth: true, role: 'cashier' },
    children: [
      {
        path: '',
        name: 'pos',
        component: () => import('pages/pos/POSPage.vue')
      },
      {
        path: 'pending',
        name: 'pending-sales',
        component: () => import('pages/pos/PendingSalesPage.vue')
      },
      {
        path: 'payment/:saleId',
        name: 'payment',
        component: () => import('pages/pos/PaymentPage.vue')
      },
      {
        path: 'history',
        name: 'sales-history',
        component: () => import('pages/pos/SalesHistoryPage.vue')
      },
      {
        path: 'refunds',
        name: 'refunds',
        component: () => import('pages/pos/RefundHistoryPage.vue')
      }
    ]
  },

  // Inventory Manager routes
  {
    path: '/inventory/:businessId/:branchId',
    component: () => Platform.is.mobile ? import('layouts/MobileLayout.vue') : import('layouts/InventoryManagerLayout.vue'),
    meta: { requiresAuth: true, role: 'inventory_clerk' },
    children: [
      {
        path: '',
        redirect: to => `/inventory/${to.params.businessId}/${to.params.branchId}/dashboard`
      },
      {
        path: 'dashboard',
        name: 'inventory-dashboard',
        component: () => import('pages/inventory/Dashboard.vue')
      },
      {
        path: 'inventory',
        name: 'inventory-list',
        component: () => import('pages/inventory/Inventory.vue')
      },
      {
        path: 'reports',
        name: 'inventory-reports',
        component: () => import('pages/inventory/Reports.vue')
      }
    ]
  },

  // Auth routes
  {
    path: '/auth',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('pages/auth/LoginPage.vue')
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('pages/auth/RegisterPage.vue')
      }
    ]
  },

  // Onboarding
  {
    path: '/onboarding',
    component: () => import('pages/onboarding/OnboardingPage.vue')
  },

  // 404
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
