import api from './api'

export const posService = {
  // Get all products
  async getProducts(businessId, branchId) {
    const response = await api.get(`/branch/${businessId}/${branchId}/products`)
    return response.data
  },

  // Get product categories
  async getCategories(businessId, branchId) {
    const response = await api.get(`/branch/${businessId}/${branchId}/categories`)
    return response.data
  },

  // Get customers
  async getCustomers(businessId, branchId) {
    const response = await api.get(`/branch/${businessId}/${branchId}/customers`)
    return response.data
  },

  // Create new customer
  async createCustomer(businessId, branchId, customerData) {
    const response = await api.post(`/branch/${businessId}/${branchId}/customers`, customerData)
    return response.data
  },

  // Create a new sale
  async createSale(businessId, branchId, saleData) {
    const response = await api.post(`/branch/${businessId}/${branchId}/sales`, saleData)
    return response.data?.data || response.data
  },

  // Process payment for a sale
  async processPayment(businessId, branchId, saleId, paymentData) {
    // Ensure saleId is a number
    const numericSaleId = parseInt(saleId, 10)
    if (isNaN(numericSaleId)) {
      throw new Error('Invalid sale ID')
    }
    const response = await api.post(`/branch/${businessId}/${branchId}/sales/${numericSaleId}/payment`, paymentData)
    return response.data?.data || response.data
  },

  // Get sale details
  async getSale(businessId, branchId, id) {
    const response = await api.get(`/branch/${businessId}/${branchId}/sales/${id}`)
    return response.data
  },

  // Get recent sales
  async getRecentSales(businessId, branchId, params = {}) {
    const response = await api.get(`/branch/${businessId}/${branchId}/sales`, { params })
    return response.data
  },

  // Get business taxes
  async getBusinessTaxes(businessId, branchId) {
    const response = await api.get(`/branch/${businessId}/${branchId}/cashier/taxes`)
    return response.data
  },

  // Refund methods
  async processRefund(businessId, branchId, data) {
    return await api.post(`/branch/${businessId}/${branchId}/cashier/refunds`, data)
  },

  async getRefunds(businessId, branchId) {
    return await api.get(`/branch/${businessId}/${branchId}/cashier/refunds`)
  },

  async approveRefund(businessId, branchId, refundId, data) {
    return await api.post(`/branch/${businessId}/${branchId}/cashier/refunds/${refundId}/approve`, data)
  },

  // Notification methods
  async getNotifications() {
    return await api.get('/notifications')
  },

  async markNotificationAsRead(notificationId) {
    return await api.post(`/notifications/${notificationId}/mark-as-read`)
  },

  async markAllNotificationsAsRead() {
    return await api.post('/notifications/mark-all-as-read')
  },

  async getUnreadNotificationCount() {
    return await api.get('/notifications/unread-count')
  },

  async createRefund(saleId, items, reason) {
    try {
      const response = await api.post('/refunds', {
        saleId,
        items,
        reason
      })
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  }
} 