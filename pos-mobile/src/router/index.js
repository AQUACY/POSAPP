import { defineRouter } from '#q-app/wrappers'
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '', component: () => import('pages/auth/SplashScreen.vue') }
    ]
  },
  {
    path: '/auth/login',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '', component: () => import('pages/auth/LoginPage.vue') }
    ]
  },
 
  {
    path: '/onboarding',
    component: () => import('layouts/OnboardingLayout.vue'),
    children: [
     
      {
        path: 'register',
        component: () => import('pages/onboarding/RegisterPage.vue')
      },
      {
        path: 'verify-otp',
        component: () => import('pages/onboarding/VerifyOtpPage.vue')
      },
      {
        path: 'setup-business',
        component: () => import('pages/onboarding/BusinessSetupPage.vue')
      },
      {
        path: 'setup-branch',
        component: () => import('pages/onboarding/BranchSetupPage.vue')
      }
    ]
  },
  {
    path: '/dashboard',
    component: () => import('layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        component: () => import('pages/dashboard/DashboardPage.vue')
      }
      // {
      //   path: 'profile',
      //   component: () => import('pages/dashboard/ProfilePage.vue')
      // }
    ]
  }
]

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(function (/* { store } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  Router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('token')
    
    if (to.meta.requiresAuth && !isAuthenticated) {
      next('/login')
    } else {
      next()
    }
  })

  return Router
})
