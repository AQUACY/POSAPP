import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const role = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => role.value === 'admin')
  const isSuperAdmin = computed(() => role.value === 'super_admin')
  const isBranchManager = computed(() => role.value === 'branch_manager')
  const isInventoryClerk = computed(() => role.value === 'inventory_clerk')
  const isCashier = computed(() => role.value === 'cashier')

  function setUser(userData) {
    user.value = userData
  }

  function setToken(tokenData) {
    token.value = tokenData
  }

  function setRole(roleData) {
    role.value = roleData
  }

  function clearAuth() {
    user.value = null
    token.value = null
    role.value = null
  }

  return {
    user,
    token,
    role,
    isAuthenticated,
    isAdmin,
    isSuperAdmin,
    isBranchManager,
    isInventoryClerk,
    isCashier,
    setUser,
    setToken,
    setRole,
    clearAuth
  }
}) 