import { authService } from 'src/services/auth'

const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('pages/IndexPage.vue')
      }
    ]
  },
  {
    path: '/auth/login',
    name: 'login',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '', component: () => import('pages/auth/LoginPage.vue') }
    ],
    meta: { guest: true }
  },
  {
    path: '/onboarding',
    component: () => import('pages/onboarding/OnboardingPage.vue')
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes

export function setupRouter(router) {
  router.beforeEach((to, from, next) => {
    const isAuthenticated = authService.isAuthenticated()
    
    if (to.meta.requiresAuth && !isAuthenticated) {
      next('/login')
    } else if (to.meta.guest && isAuthenticated) {
      next('/')
    } else {
      next()
    }
  })
}
