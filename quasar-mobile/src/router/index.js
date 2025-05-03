import { route } from 'quasar/wrappers'
import {
  createRouter,
  createMemoryHistory,
  createWebHistory,
  createWebHashHistory
} from 'vue-router'
import routes from './routes'
import superAdminRoutes from './super-admin'
import { useAuthStore } from 'stores/auth'
import { setupAuthGuard } from './guards'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes: [...routes, ...superAdminRoutes],

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  // Setup auth guard
  setupAuthGuard(Router)

  Router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuthenticated
    const userRole = authStore.user?.role

    // If route requires auth and user is not authenticated
    if (to.meta.requiresAuth && !isAuthenticated) {
      next({ name: 'login' })
      return
    }

    // If route has role requirement and user doesn't have the role
    if (to.meta.role && to.meta.role !== userRole) {
      next({ name: 'login' })
      return
    }

    // If user is authenticated and trying to access auth pages
    if (isAuthenticated && to.path.startsWith('/auth')) {
      // Redirect to appropriate dashboard based on role
      if (userRole === 'cashier') {
        const businessId = authStore.user?.business_id
        const branchId = authStore.user?.branch_id
        next({ path: `/pos/${businessId}/${branchId}` })
        return
      }
    }

    next()
  })

  return Router
})

// Helper function to check if user has required role
function hasRequiredRole(authStore, requiredRole) {
  if (Array.isArray(requiredRole)) {
    return requiredRole.some(role => {
      switch (role) {
        case 'super-admin':
          return authStore.isSuperAdmin
        case 'business-admin':
          return authStore.isBusinessAdmin
        case 'cashier':
          return authStore.isCashier
        default:
          return false
      }
    })
  }

  switch (requiredRole) {
    case 'super-admin':
      return authStore.isSuperAdmin
    case 'business-admin':
      return authStore.isBusinessAdmin
    case 'cashier':
      return authStore.isCashier
    default:
      return false
  }
}
