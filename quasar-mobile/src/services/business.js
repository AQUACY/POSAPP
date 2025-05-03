import { api } from 'boot/axios'

export const businessService = {
  // Get all businesses with optional filters
  getBusinesses: async (params = {}) => {
    try {
      console.log('Making API request with params:', params)
      const response = await api.get('/super-admin/businesses', { params })
      console.log('Raw API response:', response)
      
      // Check if response has the expected structure
      if (!response.data) {
        console.error('Invalid API response structure:', response)
        throw new Error('Invalid API response structure')
      }
      
      // Return the data in the expected format
      return {
        data: response.data.data || [],
        total: response.data.data.length || 0, // Use array length if total is not provided
        current_page: response.data.current_page || 1,
        per_page: response.data.per_page || 10
      }
    } catch (error) {
      console.error('API Error:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch businesses')
    }
  },

  // Get a single business by ID
  getBusiness: async (id) => {
    try {
      const response = await api.get(`/super-admin/businesses/${id}`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch business')
    }
  },

  // Create a new business
  createBusiness: async (businessData) => {
    try {
      const response = await api.post('/super-admin/businesses', businessData)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to create business')
    }
  },

  // Update an existing business
  updateBusiness: async (id, businessData) => {
    try {
      const response = await api.put(`/super-admin/businesses/${id}`, businessData)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update business')
    }
  },

  // Delete a business
  deleteBusiness: async (id) => {
    try {
      await api.delete(`/super-admin/businesses/${id}`)
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to delete business')
    }
  },

  // Toggle business status (activate/deactivate)
  toggleBusinessStatus: async (id) => {
    try {
      const response = await api.patch(`/super-admin/businesses/${id}/toggle-status`)
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to toggle business status')
    }
  },

  // Upload business logo
  uploadLogo: async (id, file) => {
    try {
      const formData = new FormData()
      formData.append('logo', file)
      const response = await api.post(`/super-admin/businesses/${id}/logo`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      return response.data.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to upload logo')
    }
  },

  // Branch Management
  // Get all branches for a business
  getBranches: async (businessId, params) => {
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