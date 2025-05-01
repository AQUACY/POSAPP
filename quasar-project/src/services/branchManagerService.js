import api from './api'

export const branchManagerService = {
  // Dashboard
  getDashboard(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/dashboard`)
  },

  // Reports
  getReports(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/reports`)
  },

  // Branch Management
  getBranchDetails(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/branch`)
  },

  updateBranchDetails(businessId, branchId, data) {
    return api.put(`/branch/${businessId}/${branchId}/branch_manager/branch`, data)
  },

  // Inventory Management
  getInventory(businessId, branchId, params = {}) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/inventory`, { params })
  },

  createInventory(businessId, branchId, data) {
    return api.post(`/branch/${businessId}/${branchId}/branch_manager/inventory`, data)
  },

  updateInventory(businessId, branchId, id, data) {
    return api.put(`/branch/${businessId}/${branchId}/branch_manager/inventory/${id}`, data)
  },

  deleteInventory(businessId, branchId, id) {
    return api.delete(`/branch/${businessId}/${branchId}/branch_manager/inventory/${id}`)
  },

  // Stock Request Management
  getStockRequests(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/stock-requests`)
  },

  createStockRequest(businessId, branchId, data) {
    return api.post(`/branch/${businessId}/${branchId}/branch_manager/stock-requests`, data)
  },

  getStockRequestDetails(businessId, branchId, id) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/stock-requests/${id}`)
  },

  cancelStockRequest(businessId, branchId, id) {
    return api.post(`/branch/${businessId}/${branchId}/branch_manager/stock-requests/${id}/cancel`)
  },

  // Sales Management
  getSales(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/sales`)
  },

  getSalesSummary(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/sales/summary`)
  },

  // Customer Management
  getCustomers(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/customers`)
  },

  // Staff Management
  getStaff(businessId, branchId) {
    return api.get(`/branch/${businessId}/${branchId}/branch_manager/staff`)
  },

  createCashier(businessId, branchId, data) {
    return api.post(`/branch/${businessId}/${branchId}/branch_manager/staff`, data)
  },

  updateCashier(businessId, branchId, id, data) {
    return api.put(`/branch/${businessId}/${branchId}/branch_manager/staff/${id}`, data)
  }
}
