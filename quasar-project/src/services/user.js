import { api } from 'boot/axios'

export const userService = {
  // Get all users with optional filters
  getUsers: async (params = {}) => {
    try {
      const response = await api.get('/super-admin/users', { params })
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch users')
    }
  },

  // Get a single user by ID
  getUser: async (id) => {
    try {
      const response = await api.get(`/super-admin/users/${id}`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch user')
    }
  },

  // Create a new user
  createUser: async (userData) => {
    try {
      const response = await api.post('/auth/register', userData)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to create user')
    }
  },

  // Update an existing user
  updateUser: async (id, userData) => {
    try {
      const response = await api.put(`/super-admin/users/${id}`, userData)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update user')
    }
  },

  // Delete a user
  deleteUser: async (id) => {
    try {
      await api.delete(`/super-admin/users/${id}`)
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to delete user')
    }
  },

  // Toggle user status (activate/deactivate)
  toggleUserStatus: async (id) => {
    try {
      const response = await api.patch(`/super-admin/users/${id}/toggle-status`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to toggle user status')
    }
  },

  // Get user activity history
  getUserActivity: async (id) => {
    try {
      const response = await api.get(`/super-admin/users/${id}/activity`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch user activity')
    }
  }
} 