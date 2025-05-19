import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from 'boot/axios'

function isCordova() {
  return typeof window !== 'undefined' && window.cordova;
}

// Secure Storage helpers
async function setTokenSecure(token) {
  if (isCordova() && window.SecureStorage) {
    return new Promise((resolve) => {
      const ss = new window.SecureStorage(
        () => {
          ss.set(() => resolve(), () => resolve(), 'token', token)
        },
        () => resolve(),
        'POSAPP'
      )
    })
  } else {
    localStorage.setItem('token', token)
  }
}

async function getTokenSecure() {
  if (isCordova() && window.SecureStorage) {
    return new Promise((resolve) => {
      const ss = new window.SecureStorage(
        () => {
          ss.get((value) => resolve(value), () => resolve(null), 'token')
        },
        () => resolve(null),
        'POSAPP'
      )
    })
  } else {
    return localStorage.getItem('token')
  }
}

async function removeTokenSecure() {
  if (isCordova() && window.SecureStorage) {
    return new Promise((resolve) => {
      const ss = new window.SecureStorage(
        () => {
          ss.remove(() => resolve(), () => resolve(), 'token')
        },
        () => resolve(),
        'POSAPP'
      )
    })
  } else {
    localStorage.removeItem('token')
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')
  const isBusinessAdmin = computed(() => user.value?.role === 'admin')
  const isCashier = computed(() => user.value?.role === 'cashier')
  const isInventoryManager = computed(() => user.value?.role === 'inventory_clerk')
  const isBranchManager = computed(() => user.value?.role === 'branch_manager')

  const setToken = async (newToken) => {
    token.value = newToken
    if (newToken) {
      await setTokenSecure(newToken)
      api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      await removeTokenSecure()
      delete api.defaults.headers.common['Authorization']
    }
  }

  const setUser = (userData) => {
    user.value = userData
  }

  const login = async (email, password) => {
    loading.value = true
    try {
      const response = await api.post('/auth/login', { email, password })
      const { token: newToken } = response.data.data
      const userData = response.data.data.user
      await setToken(newToken)
      setUser(userData)
      if (!userData || !userData.role) {
        throw new Error('Invalid user data received')
      }
      return userData
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Login failed')
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    try {
      const response = await api.post('/auth/register', userData)
      const token = response.data.token
      const user = response.data.user
      await setToken(token)
      setUser(user)
      return user
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Registration failed')
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      await setToken(null)
      setUser(null)
    }
  }

  const getRedirectPath = () => {
    if (!isAuthenticated.value) return '/auth/login'
    const businessId = user.value?.business_id
    const branchId = user.value?.branch_id
    if (user.value?.role === 'super_admin') {
      return '/super-admin/dashboard'
    }
    if (!businessId) {
      console.error('No business ID found for user')
      return '/auth/login'
    }
    const basePath = (() => {
      switch (user.value?.role) {
        case 'admin':
          return `/business/${businessId}`
        case 'cashier':
          return `/pos/${businessId}/${branchId}`
        case 'inventory_clerk':
          return `/inventory/${businessId}/${branchId}`
        case 'branch_manager':
          return `/branch/${businessId}/${branchId}`
        default:
          return '/auth/login'
      }
    })()
    switch (user.value?.role) {
      case 'admin':
        return `${basePath}/dashboard`
      case 'cashier':
        return basePath
      case 'inventory_clerk':
        return basePath
      case 'branch_manager':
        return `${basePath}/dashboard`
      default:
        return '/auth/login'
    }
  }

  // Initialize auth state from storage
  const initializeAuth = async () => {
    const storedToken = await getTokenSecure()
    if (storedToken) {
      await setToken(storedToken)
      await fetchUserData()
    }
  }

  const fetchUserData = async () => {
    try {
      const response = await api.get('/auth/profile')
      setUser(response.data.data)
    } catch (error) {
      console.error('Failed to fetch user data:', error)
      await setToken(null)
      setUser(null)
    }
  }

  const checkAuth = async () => {
    if (!token.value) return false
    try {
      loading.value = true
      const response = await api.get('/auth/profile')
      setUser(response.data.data)
      return true
    } catch (error) {
      console.error('Auth check failed:', error)
      await setToken(null)
      setUser(null)
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isSuperAdmin,
    isBusinessAdmin,
    isCashier,
    isInventoryManager,
    isBranchManager,
    login,
    register,
    logout,
    getRedirectPath,
    initializeAuth,
    checkAuth
  }
})
