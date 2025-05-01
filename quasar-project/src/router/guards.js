import { useAuthStore } from 'stores/auth'

export async function setupAuthGuard(router) {
  const authStore = useAuthStore()

  // Initialize auth state
  await authStore.initializeAuth()

  router.beforeEach(async (to, from, next) => {
    const isAuthenticated = authStore.isAuthenticated
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    const isAuthPage = to.path.startsWith('/auth/')
    const requiredRole = to.meta.role
    const user = authStore.user

    console.log('Navigation Guard:', {
      to: {
        path: to.path,
        name: to.name,
        params: to.params,
        meta: to.meta
      },
      from: {
        path: from.path,
        name: from.name
      },
      isAuthenticated,
      requiresAuth,
      isAuthPage,
      requiredRole,
      user: user ? { role: user.role, business_id: user.business_id, branch_id: user.branch_id } : null
    })

    // If the page requires auth and user is not authenticated
    if (requiresAuth && !isAuthenticated) {
      console.log('Redirecting to login - not authenticated')
      next({ path: '/auth/login', query: { redirect: to.fullPath } })
      return
    }

    // If user is authenticated and trying to access auth pages
    if (isAuthenticated && isAuthPage) {
      // If coming from login, use the redirect query parameter
      if (from.path === '/auth/login' && from.query.redirect) {
        console.log('Redirecting to saved path:', from.query.redirect)
        next(from.query.redirect)
        return
      }
      // Otherwise, redirect to the appropriate dashboard
      const redirectPath = authStore.getRedirectPath()
      if (redirectPath !== to.path) {
        console.log('Redirecting to dashboard:', redirectPath)
        next(redirectPath)
        return
      }
    }

    // If user is authenticated but token is invalid
    if (isAuthenticated && !user) {
      try {
        await authStore.checkAuth()
        next()
      } catch (error) {
        console.log('Redirecting to login - invalid token')
        next({ path: '/auth/login', query: { redirect: to.fullPath } })
      }
      return
    }

    // Check role-based access
    if (requiresAuth && requiredRole) {
      const hasRole = (() => {
        switch (requiredRole) {
          case 'super_admin':
            return authStore.isSuperAdmin
          case 'admin':
            return authStore.isBusinessAdmin
          case 'cashier':
            return authStore.isCashier
          case 'inventory_clerk':
            return authStore.isInventoryManager
          case 'branch_manager':
            return authStore.isBranchManager
          default:
            return false
        }
      })()

      if (!hasRole) {
        const redirectPath = authStore.getRedirectPath()
        if (redirectPath !== to.path) {
          console.log('Redirecting - role mismatch:', { requiredRole, userRole: user?.role })
          next(redirectPath)
          return
        }
      }

      // Check business and branch ID access
      if (user) {
        const businessId = parseInt(to.params.businessId)
        const branchId = parseInt(to.params.branchId)

        // Super admin can access any business
        if (authStore.isSuperAdmin) {
          next()
          return
        }

        // Business admin can only access their business
        if (authStore.isBusinessAdmin && businessId !== user.business_id) {
          const redirectPath = authStore.getRedirectPath()
          if (redirectPath !== to.path) {
            console.log('Redirecting - business ID mismatch:', { 
              required: businessId, 
              user: user.business_id 
            })
            next(redirectPath)
            return
          }
        }

        // Branch-specific roles (cashier, inventory clerk, branch manager)
        if (['cashier', 'inventory_clerk', 'branch_manager'].includes(user.role)) {
          // If the route doesn't have the required IDs, redirect to the correct path
          if (!businessId || !branchId) {
            const redirectPath = authStore.getRedirectPath()
            if (redirectPath !== to.path) {
              console.log('Redirecting - missing IDs:', { businessId, branchId })
              next(redirectPath)
              return
            }
          }
          
          // If the IDs don't match the user's IDs, redirect to the correct path
          if (businessId !== user.business_id || branchId !== user.branch_id) {
            const redirectPath = authStore.getRedirectPath()
            if (redirectPath !== to.path) {
              console.log('Redirecting - ID mismatch:', { 
                required: { businessId, branchId },
                user: { business_id: user.business_id, branch_id: user.branch_id }
              })
              next(redirectPath)
              return
            }
          }
        }
      }
    }

    // Allow navigation to proceed
    console.log('Navigation proceeding to:', to.path)
    next()
  })
} 