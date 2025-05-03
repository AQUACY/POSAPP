import { useAuthStore } from 'stores/auth'

export default async ({ router }) => {
  const authStore = useAuthStore()

  // Add navigation guard
  router.beforeEach(async (to, from, next) => {
    // Check if the route requires authentication
    if (to.matched.some(record => record.meta.requiresAuth)) {
      // Check if user is authenticated
      const isAuthenticated = await authStore.checkAuth()
      
      if (!isAuthenticated) {
        // Not authenticated, redirect to login
        next({
          path: '/auth/login',
          query: { redirect: to.fullPath }
        })
      } else {
        // Authenticated, proceed to the route
        next()
      }
    } else if (to.path.startsWith('/auth')) {
      // If trying to access auth pages while authenticated
      const isAuthenticated = await authStore.checkAuth()
      if (isAuthenticated) {
        // Instead of redirecting to '/', redirect to the last visited page or dashboard
        const lastPath = localStorage.getItem('lastPath')
        next(lastPath)
      } else {
        next()
      }
    } else {
      // Public route, proceed
      next()
    }
  })

  // Save the current path before each navigation
  router.beforeEach((to, from, next) => {
    if (!to.path.startsWith('/auth')) {
      localStorage.setItem('lastPath', to.fullPath)
    }
    next()
  })
} 