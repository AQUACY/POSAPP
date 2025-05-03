import { api } from 'boot/axios'

export const superAdminService = {
  // Business Management
  getBusinesses: (params) => {
    return api.get('/super-admin/businesses', { params })
  },

  getBusiness: (id) => {
    return api.get(`/super-admin/businesses/${id}`)
  },

  createBusiness: (data) => {
    return api.post('/super-admin/businesses', data)
  },

  updateBusiness: (id, data) => {
    return api.put(`/super-admin/businesses/${id}`, data)
  },

  deleteBusiness: (id) => {
    return api.delete(`/super-admin/businesses/${id}`)
  },

  toggleBusinessStatus: (id) => {
    return api.patch(`/super-admin/businesses/${id}/toggle-status`)
  },

  uploadBusinessLogo: (id, formData) => {
    return api.post(`/super-admin/businesses/${id}/logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // User Management
  getUsers: (params) => {
    return api.get('/super-admin/users', { params })
  },

  getUser: (id) => {
    return api.get(`/super-admin/users/${id}`)
  },

  createUser: (data) => {
    return api.post('/super-admin/users', data)
  },

  updateUser: (id, data) => {
    return api.put(`/super-admin/users/${id}`, data)
  },

  deleteUser: (id) => {
    return api.delete(`/super-admin/users/${id}`)
  },

  toggleUserStatus: (id) => {
    return api.patch(`/super-admin/users/${id}/toggle-status`)
  },

  getUserActivities: (id, params) => {
    return api.get(`/super-admin/users/${id}/activities`, { params })
  },

  // Reports
  getSalesReport: (params) => {
    return api.get('/super-admin/reports/sales', { params })
  },

  getRevenueReport: (params) => {
    return api.get('/super-admin/reports/revenue', { params })
  },

  getBusinessReport: (params) => {
    return api.get('/super-admin/reports/business', { params })
  },

  exportSalesReport: (params) => {
    return api.get('/super-admin/reports/sales/export', {
      params,
      responseType: 'blob'
    })
  },

  exportRevenueReport: (params) => {
    return api.get('/super-admin/reports/revenue/export', {
      params,
      responseType: 'blob'
    })
  },

  exportBusinessReport: (params) => {
    return api.get('/super-admin/reports/business/export', {
      params,
      responseType: 'blob'
    })
  },

  // Settings
  getSettings: () => {
    return api.get('/super-admin/settings')
  },

  updateGeneralSettings: (data) => {
    return api.put('/super-admin/settings/general', data)
  },

  updateSecuritySettings: (data) => {
    return api.put('/super-admin/settings/security', data)
  },

  updateNotificationSettings: (data) => {
    return api.put('/super-admin/settings/notifications', data)
  },

  updateBackupSettings: (data) => {
    return api.put('/super-admin/settings/backup', data)
  },

  // Dashboard
  getDashboardStats: () => {
    return api.get('/super-admin/dashboard')
  },

  getRecentActivities: (params) => {
    return api.get('/super-admin/dashboard/activities', { params })
  }
} 