import { defineStore } from 'pinia'
import { superAdminService } from '../services/super-admin'

export const useSuperAdminStore = defineStore('super_admin', {
  state: () => ({
    businesses: [],
    users: [],
    settings: {
      general: {},
      security: {},
      notifications: {},
      backup: {}
    },
    dashboardStats: {},
    recentActivities: [],
    loading: {
      businesses: false,
      users: false,
      settings: false,
      dashboard: false
    },
    error: null
  }),

  getters: {
    getBusinessById: (state) => (id) => {
      return state.businesses.find(business => business.id === id)
    },

    getUserById: (state) => (id) => {
      return state.users.find(user => user.id === id)
    },

    getActiveBusinesses: (state) => {
      return state.businesses.filter(business => business.is_active)
    },

    getInactiveBusinesses: (state) => {
      return state.businesses.filter(business => !business.is_active)
    },

    getActiveUsers: (state) => {
      return state.users.filter(user => user.is_active)
    },

    getInactiveUsers: (state) => {
      return state.users.filter(user => !user.is_active)
    }
  },

  actions: {
    // Business Management
    async fetchBusinesses(params) {
      this.loading.businesses = true
      try {
        const response = await superAdminService.getBusinesses(params)
        this.businesses = response.data
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.loading.businesses = false
      }
    },

    async createBusiness(data) {
      try {
        const response = await superAdminService.createBusiness(data)
        this.businesses.push(response.data)
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async updateBusiness(id, data) {
      try {
        const response = await superAdminService.updateBusiness(id, data)
        const index = this.businesses.findIndex(b => b.id === id)
        if (index !== -1) {
          this.businesses[index] = response.data
        }
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async deleteBusiness(id) {
      try {
        await superAdminService.deleteBusiness(id)
        this.businesses = this.businesses.filter(b => b.id !== id)
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async toggleBusinessStatus(id) {
      try {
        const response = await superAdminService.toggleBusinessStatus(id)
        const index = this.businesses.findIndex(b => b.id === id)
        if (index !== -1) {
          this.businesses[index] = response.data
        }
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    // User Management
    async fetchUsers(params) {
      this.loading.users = true
      try {
        const response = await superAdminService.getUsers(params)
        this.users = response.data
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.loading.users = false
      }
    },

    async createUser(data) {
      try {
        const response = await superAdminService.createUser(data)
        this.users.push(response.data)
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async updateUser(id, data) {
      try {
        const response = await superAdminService.updateUser(id, data)
        const index = this.users.findIndex(u => u.id === id)
        if (index !== -1) {
          this.users[index] = response.data
        }
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async deleteUser(id) {
      try {
        await superAdminService.deleteUser(id)
        this.users = this.users.filter(u => u.id !== id)
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async toggleUserStatus(id) {
      try {
        const response = await superAdminService.toggleUserStatus(id)
        const index = this.users.findIndex(u => u.id === id)
        if (index !== -1) {
          this.users[index] = response.data
        }
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    // Settings
    async fetchSettings() {
      this.loading.settings = true
      try {
        const response = await superAdminService.getSettings()
        this.settings = response.data
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.loading.settings = false
      }
    },

    async updateGeneralSettings(data) {
      try {
        const response = await superAdminService.updateGeneralSettings(data)
        this.settings.general = response.data
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async updateSecuritySettings(data) {
      try {
        const response = await superAdminService.updateSecuritySettings(data)
        this.settings.security = response.data
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async updateNotificationSettings(data) {
      try {
        const response = await superAdminService.updateNotificationSettings(data)
        this.settings.notifications = response.data
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    async updateBackupSettings(data) {
      try {
        const response = await superAdminService.updateBackupSettings(data)
        this.settings.backup = response.data
        this.error = null
        return response.data
      } catch (err) {
        this.error = err.message
        throw err
      }
    },

    // Dashboard
    async fetchDashboardStats() {
      this.loading.dashboard = true
      try {
        const response = await superAdminService.getDashboardStats()
        this.dashboardStats = response.data
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      } finally {
        this.loading.dashboard = false
      }
    },

    async fetchRecentActivities(params) {
      try {
        const response = await superAdminService.getRecentActivities(params)
        this.recentActivities = response.data
        this.error = null
      } catch (err) {
        this.error = err.message
        throw err
      }
    }
  }
}) 