import { defineStore } from 'pinia'
import { branchManagerService } from 'src/services/branchManagerService'
import { useRoute } from 'vue-router'

export const useBranchManagerStore = defineStore('branchManager', {
  state: () => ({
    branchDetails: null,
    inventory: [],
    sales: [],
    salesSummary: null,
    customers: [],
    staff: [],
    stockRequests: [],
    warehouses: [],
    warehouseInventory: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchBranchDetails(businessId, branchId) {
      this.loading = true
      try {
        const response = await branchManagerService.getBranchDetails(businessId, branchId)
        this.branchDetails = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchBranchDetailsforInventory(businessId, branchId) {
      this.loading = true
      try {
        const response = await branchManagerService.getBranchDetailsforInventory(businessId, branchId)
        this.branchDetails = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async updateBranchDetails(data) {
      this.loading = true
      try {
        const route = useRoute()
        await branchManagerService.updateBranchDetails(route.params.businessId, route.params.branchId, data)
        await this.fetchBranchDetails(route.params.businessId, route.params.branchId)
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchInventory(businessId, branchId, params = {}) {
      this.loading = true
      try {
        const response = await branchManagerService.getInventory(businessId, branchId, params)
        if (response.data && response.data.data) {
          this.inventory = response.data
        } else {
          this.inventory = { data: [], total: 0 }
        }
      } catch (error) {
        this.error = error.message
        this.inventory = { data: [], total: 0 }
        throw error
      } finally {
        this.loading = false
      }
    },

    async createInventory(businessId, branchId, data) {
      this.loading = true
      try {
        await branchManagerService.createInventory(businessId, branchId, data)
        await this.fetchInventory(businessId, branchId)
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateInventory(businessId, branchId, id, data) {
      this.loading = true
      try {
        await branchManagerService.updateInventory(businessId, branchId, id, data)
        await this.fetchInventory(businessId, branchId)
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteInventoryItem(businessId, branchId, id) {
      this.loading = true
      try {
        await branchManagerService.deleteInventory(businessId, branchId, id)
        await this.fetchInventory(businessId, branchId)
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSales() {
      this.loading = true
      try {
        const route = useRoute()
        const response = await branchManagerService.getSales(route.params.businessId, route.params.branchId)
        this.sales = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchSalesSummary() {
      this.loading = true
      try {
        const route = useRoute()
        const response = await branchManagerService.getSalesSummary(route.params.businessId, route.params.branchId)
        this.salesSummary = response.data.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchCustomers() {
      this.loading = true
      try {
        const route = useRoute()
        const response = await branchManagerService.getCustomers(route.params.businessId, route.params.branchId)
        this.customers = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchStaff() {
      this.loading = true
      try {
        const route = useRoute()
        const response = await branchManagerService.getStaff(route.params.businessId, route.params.branchId)
        this.staff = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async createCashier(data) {
      this.loading = true
      try {
        const route = useRoute()
        await branchManagerService.createCashier(route.params.businessId, route.params.branchId, data)
        await this.fetchStaff()
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async updateCashier(id, data) {
      this.loading = true
      try {
        const route = useRoute()
        await branchManagerService.updateCashier(route.params.businessId, route.params.branchId, id, data)
        await this.fetchStaff()
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchStockRequests(businessId, branchId, params = {}) {
      this.loading = true
      try {
        const response = await branchManagerService.getStockRequests(businessId, branchId, params)
        this.stockRequests = response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async createStockRequest(businessId, branchId, data) {
      this.loading = true
      try {
        const response = await branchManagerService.createStockRequest(businessId, branchId, data)
        await this.fetchStockRequests(businessId, branchId)
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async getStockRequestDetails(businessId, branchId, id) {
      this.loading = true
      try {
        const response = await branchManagerService.getStockRequestDetails(businessId, branchId, id)
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async cancelStockRequest(businessId, branchId, id) {
      this.loading = true
      try {
        await branchManagerService.cancelStockRequest(businessId, branchId, id)
        await this.fetchStockRequests(businessId, branchId)
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchWarehouses(businessId, branchId) {
      this.loading = true
      try {
        const response = await branchManagerService.getWarehouses(businessId, branchId)
        this.warehouses = Array.isArray(response.data) ? response.data : [response.data.data]
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchWarehouseInventory(businessId, branchId, warehouseId) {
      this.loading = true
      try {
        const response = await branchManagerService.getWarehouseInventory(businessId, branchId, warehouseId)
        this.warehouseInventory = response.data
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
