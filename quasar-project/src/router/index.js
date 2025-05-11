import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'
import routes from './routes'
import { setupAuthGuard } from './guards'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Setup auth guard
setupAuthGuard(router)

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated
  const userRole = authStore.user?.role
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/auth/login')
    return
  }

  // If route has role requirement and user doesn't have the role
  if (to.meta.role && to.meta.role !== userRole) {
    next('/auth/login')
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

export default router
