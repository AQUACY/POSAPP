import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    // Dashboard data
    dashboardSummary: {
      total_items: 0,
      items_change: 0,
      low_stock_items: 0,
      expired_items: 0,
      stock_value: 0
    },
    recentActivities: [],
    stockAlerts: [],
    expiringItems: [],
    
    // Inventory items
    inventoryItems: [],
    loading: false,
    error: null
  }),

  getters: {
    hasLowStockItems: (state) => state.stockAlerts.length > 0,
    totalInventoryValue: (state) => state.dashboardSummary.stock_value,
    getInventoryItemById: (state) => (id) => {
      return state.inventoryItems.find(item => item.id === id)
    }
  },

  actions: {
    // Dashboard Summary
    async fetchDashboardSummary() {
      try {
        const response = await api.get('/dashboard/summary')
        this.dashboardSummary = response.data.data
      } catch (error) {
        console.error('Error fetching dashboard summary:', error)
        throw error
      }
    },

    // Recent Activities
    async fetchRecentActivities() {
      try {
        const response = await api.get('/dashboard/activities')
        console.log('Recent activities response:', response.data)
        this.recentActivities = response.data.data
      } catch (error) {
        console.error('Error fetching recent activities:', error)
        throw error
      }
    },

    // Stock Alerts
    async fetchStockAlerts() {
      try {
        const response = await api.get('/dashboard/alerts')
        this.stockAlerts = response.data.data
      } catch (error) {
        console.error('Error fetching stock alerts:', error)
        throw error
      }
    },

    // Expiring Items
    async fetchExpiringItems() {
      try {
        const response = await api.get('/dashboard/expiring')
        this.expiringItems = response.data.data
      } catch (error) {
        console.error('Error fetching expiring items:', error)
        throw error
      }
    },

    // Inventory Items
    async fetchInventoryItems(params = {}) {
      this.loading = true
      try {
        const response = await api.get('/inventory', { params })
        this.inventoryItems = response.data.data.data
        console.log(this.inventoryItems.data)
      } catch (error) {
        console.error('Error fetching inventory items:', error)
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update Inventory Quantity
    async updateInventoryQuantity({ id, change_type, quantity, reason }) {
      try {
        const response = await api.post(`/inventory/${id}/add-stock`, {
          change_type,
          quantity,
          reason
        })

        // Update local state
        const updatedItem = response.data.data.inventory
        const index = this.inventoryItems.findIndex(item => item.id === id)
        if (index !== -1) {
          this.inventoryItems[index] = updatedItem
        }

        // Refresh dashboard data since quantities have changed
        await this.fetchDashboardSummary()
        await this.fetchStockAlerts()
        
        return response.data
      } catch (error) {
        console.error('Error updating inventory quantity:', error)
        throw error
      }
    },

    // Add new inventory item
    async addInventoryItem(itemData) {
      try {
        const response = await api.post('/inventory', itemData)
        this.inventoryItems.push(response.data.data)
        return response.data
      } catch (error) {
        console.error('Error adding inventory item:', error)
        throw error
      }
    },

    // Update inventory item
    async updateInventoryItem(id, itemData) {
      try {
        const response = await api.put(`/inventory/${id}`, itemData)
        const index = this.inventoryItems.findIndex(item => item.id === id)
        if (index !== -1) {
          this.inventoryItems[index] = response.data.data
        }
        return response.data
      } catch (error) {
        console.error('Error updating inventory item:', error)
        throw error
      }
    },

    // Delete inventory item
    async deleteInventoryItem(id) {
      try {
        await api.delete(`/inventory/${id}`)
        this.inventoryItems = this.inventoryItems.filter(item => item.id !== id)
      } catch (error) {
        console.error('Error deleting inventory item:', error)
        throw error
      }
    },

    // Get low stock items
    async fetchLowStockItems() {
      try {
        const response = await api.get('/inventory/low-stock')
        return response.data.data
      } catch (error) {
        console.error('Error fetching low stock items:', error)
        throw error
      }
    },

    // Get expired items
    async fetchExpiredItems() {
      try {
        const response = await api.get('/inventory/expired')
        return response.data.data
      } catch (error) {
        console.error('Error fetching expired items:', error)
        throw error
      }
    },

    // Reset store state
    resetState() {
      this.dashboardSummary = {
        total_items: 0,
        items_change: 0,
        low_stock_items: 0,
        expired_items: 0,
        stock_value: 0
      }
      this.recentActivities = []
      this.stockAlerts = []
      this.expiringItems = []
      this.inventoryItems = []
      this.loading = false
      this.error = null
    },

    // Get inventory report
    async fetchInventoryReport(params = {}) {
      try {
        const response = await api.get('/inventory/report', { params })
        return response.data.data
      } catch (error) {
        console.error('Error fetching inventory report:', error)
        throw error
      }
    }
  }
}) 