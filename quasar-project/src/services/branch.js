import { api } from 'boot/axios'

export const branchService = {
  // Get all branches with optional filters
  getBranches: async (businessId, params = {}) => {
    try {
      console.log('Making API request with params:', params)
      const response = await api.get(`/super-admin/branches`, { 
        params: {
          ...params,
          business_id: businessId
        }
      })
      console.log('Raw API response:', response)
      
      // Check if response has the expected structure
      if (!response.data) {
        console.error('Invalid API response structure:', response)
        throw new Error('Invalid API response structure')
      }
      
      // Return the data in the expected format
      return {
        data: response.data.data || [],
        total: response.data.data.length || 0,
        current_page: response.data.current_page || 1,
        per_page: response.data.per_page || 10
      }
    } catch (error) {
      console.error('API Error:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch branches')
    }
  },

  // Create a new branch
  createBranch: async (businessId, branchData) => {
    try {
      const response = await api.post('/super-admin/branches', {
        ...branchData,
        business_id: businessId
      })
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to create branch')
    }
  },

  // Update a branch
  updateBranch: async (businessId, branchId, branchData) => {
    try {
      const response = await api.put(`/super-admin/branches/${businessId}/${branchId}`, branchData)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update branch')
    }
  },

  // Delete a branch
  deleteBranch: async (businessId, branchId) => {
    try {
      await api.delete(`/super-admin/branches/${businessId}/${branchId}`)
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to delete branch')
    }
  },

  // Toggle branch status
  toggleBranchStatus: async (businessId, branchId) => {
    try {
      const response = await api.patch(`/super-admin/branches/${businessId}/${branchId}/toggle-status`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to toggle branch status')
    }
  }
} 