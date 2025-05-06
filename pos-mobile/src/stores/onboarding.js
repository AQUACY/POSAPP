import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useOnboardingStore = defineStore('onboarding', () => {
  // Registration data
  const registrationData = ref({
    fullName: '',
    email: '',
    password: '',
    confirmPassword: ''
  })

  // OTP data
  const otpData = ref({
    email: '',
    otp: ['', '', '', '', '', ''],
    verified: false
  })

  // Business data
  const businessData = ref({
    name: '',
    email: '',
    phoneNumber: '',
    address: '',
    type: null,
    logo: null
  })

  // Branch data
  const branchData = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    type: null,
    openingHours: ''
  })

  // Current step tracking
  const currentStep = ref(1)
  const totalSteps = ref(4)

  // Computed properties
  const isRegistrationComplete = computed(() => {
    const { fullName, email, password, confirmPassword } = registrationData.value
    return fullName && email && password && confirmPassword
  })

  const isOtpVerified = computed(() => otpData.value.verified)

  const isBusinessSetupComplete = computed(() => {
    const { name, email, phoneNumber, address, type } = businessData.value
    return name && email && phoneNumber && address && type
  })

  const isBranchSetupComplete = computed(() => {
    const { name, email, phone, address, type, openingHours } = branchData.value
    return name && email && phone && address && type && openingHours
  })

  // Methods
  function setRegistrationData(data) {
    registrationData.value = { ...registrationData.value, ...data }
  }

  function setOtpData(data) {
    otpData.value = { ...otpData.value, ...data }
  }

  function setBusinessData(data) {
    businessData.value = { ...businessData.value, ...data }
  }

  function setBranchData(data) {
    branchData.value = { ...branchData.value, ...data }
  }

  function setCurrentStep(step) {
    currentStep.value = step
  }

  function clearOnboardingData() {
    registrationData.value = {
      fullName: '',
      email: '',
      password: '',
      confirmPassword: ''
    }
    otpData.value = {
      email: '',
      otp: ['', '', '', '', '', ''],
      verified: false
    }
    businessData.value = {
      name: '',
      email: '',
      phoneNumber: '',
      address: '',
      type: null,
      logo: null
    }
    branchData.value = {
      name: '',
      email: '',
      phone: '',
      address: '',
      type: null,
      openingHours: ''
    }
    currentStep.value = 1
  }

  return {
    // State
    registrationData,
    otpData,
    businessData,
    branchData,
    currentStep,
    totalSteps,

    // Computed
    isRegistrationComplete,
    isOtpVerified,
    isBusinessSetupComplete,
    isBranchSetupComplete,

    // Methods
    setRegistrationData,
    setOtpData,
    setBusinessData,
    setBranchData,
    setCurrentStep,
    clearOnboardingData
  }
}) 