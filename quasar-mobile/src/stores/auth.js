import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import  api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')
  const isBusinessAdmin = computed(() => user.value?.role === 'admin')
  const isCashier = computed(() => user.value?.role === 'cashier')
  const isInventoryManager = computed(() => user.value?.role === 'inventory_clerk')
  const isBranchManager = computed(() => user.value?.role === 'branch_manager')

  const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('token', newToken)
      api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      localStorage.removeItem('token')
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
      
      setToken(newToken)
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
      setToken(token)
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
      setToken(null)
      setUser(null)
    }
  }

  const getRedirectPath = () => {
    if (!isAuthenticated.value) return '/auth/login'
    
    const businessId = user.value?.business_id
    const branchId = user.value?.branch_id
    
    // Super admin doesn't need business ID check
    if (user.value?.role === 'super_admin') {
      return '/super-admin/dashboard'
    }
    
    // For other roles, business ID is required
    if (!businessId) {
      console.error('No business ID found for user')
      return '/auth/login'
    }
    
    // Get the base path for each role
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

    // Add the default page for each role
    switch (user.value?.role) {
      case 'admin':
        return `${basePath}/dashboard`
      case 'cashier':
        return basePath // Cashier only has one page
      case 'inventory_clerk':
        return basePath // Default to inventory page
      case 'branch_manager':
        return `${basePath}/dashboard`
      default:
        return '/auth/login'
    }
  }

  // Initialize auth state from localStorage
  const initializeAuth = async () => {
    const storedToken = localStorage.getItem('token')
    if (storedToken) {
      setToken(storedToken)
      // Fetch user data if token exists
      await fetchUserData()
    }
  }

  const fetchUserData = async () => {
    try {
      const response = await api.get('/auth/profile')
      setUser(response.data.data)
    } catch (error) {
      console.error('Failed to fetch user data:', error)
      setToken(null)
      setUser(null)
    }
  }

  // Check authentication status
  const checkAuth = async () => {
    if (!token.value) return false
    
    try {
      loading.value = true
      const response = await api.get('/auth/profile')
      setUser(response.data.data)
      return true
    } catch (error) {
      console.error('Auth check failed:', error)
      setToken(null)
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
