import api from './api'

export const onboardingService = {
  async register(userData) {
    const response = await api.post('/register', {
      name: userData.fullName,
      email: userData.email,
      password: userData.password,
      phone: userData.phone
    })
    return response.data
  },

  async verifyOtp(userId, otp) {
    const response = await api.post('/verify-otp', {
      user_id: userId,
      otp: otp.join('')
    })
    return response.data
  },

  async setupBusiness(businessData) {
    const formData = new FormData()
    formData.append('business_name', businessData.businessName)
    formData.append('business_type', businessData.businessType)
    formData.append('address', businessData.address)
    formData.append('whatsapp_contact', businessData.phone)
    formData.append('email', businessData.email)
    formData.append('tax_id', businessData.taxId || '')
    if (businessData.logo) {
      formData.append('business_logo', businessData.logo)
    }

    const response = await api.post('/setup-business', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  }
}

export default onboardingService 