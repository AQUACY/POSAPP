<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <div class="row full-height">
        <!-- Left Side - Branding & Progress -->
        <div class="col-12 col-md-5 left-panel">
          <div class="animated-background"></div>
          <div class="left-panel-content">
            <div class="branding">
              <q-img
                src="~assets/quasar-logo-vertical.svg"
                style="height: 40%; max-width: 160px"
                class="q-mb-lg logo-animation"
              />
              <div class="text-h4 text-weight-bold text-white q-mb-sm fade-in">
                Welcome to POS System
              </div>
              <div class="text-subtitle1 text-grey-3 fade-in-delay">
                Let's get your business set up in minutes
              </div>
            </div>

            <div class="progress-section q-mt-xl">
              <div class="text-h6 text-white q-mb-lg fade-in">Setup Progress</div>
              <div class="progress-steps">
                <div
                  v-for="(step, index) in steps"
                  :key="index"
                  class="progress-step"
                  :class="{
                    'step-completed': currentStep > index + 1,
                    'step-active': currentStep === index + 1,
                    'fade-in': true
                  }"
                  :style="{ animationDelay: `${index * 0.1}s` }"
                >
                  <div class="step-number">{{ index + 1 }}</div>
                  <div class="step-info">
                    <div class="step-title">{{ step.title }}</div>
                    <div class="step-description">{{ step.description }}</div>
                  </div>
                  <q-icon
                    v-if="currentStep > index + 1"
                    name="check_circle"
                    color="positive"
                    size="24px"
                    class="check-animation"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Content -->
        <div class="col-12 col-md-7 right-panel">
          <div class="right-panel-content">
            <div class="step-container">
              <!-- Step 1: Registration -->
              <div v-if="currentStep === 1">
                <div class="text-h5 q-mb-lg">Create Your Account</div>
                <registration-form
                  @registration-complete="onRegistrationComplete"
                  @registration-error="onRegistrationError"
                />
                <div class="row justify-end q-mt-lg">
                  <q-btn
                    color="primary"
                    label="Next"
                    @click="currentStep = 2"
                    :disable="!canProceedToStep2"
                  />
                </div>
              </div>

              <!-- Step 2: OTP Verification -->
              <div v-if="currentStep === 2">
                <div class="text-h5 q-mb-lg">Verify Your Account</div>
                <otp-verification
                  :user-id="userId"
                  @verification-complete="onVerificationComplete"
                  @verification-error="onVerificationError"
                />
                <div class="row justify-between q-mt-lg">
                  <q-btn
                    flat
                    color="primary"
                    label="Back"
                    @click="currentStep = 1"
                  />
                  <q-btn
                    color="primary"
                    label="Next"
                    @click="currentStep = 3"
                    :disable="!canProceedToStep3"
                  />
                </div>
              </div>

              <!-- Step 3: Login -->
              <div v-if="currentStep === 3">
                <div class="text-h5 q-mb-lg">Login to Your Account</div>
                <login-form
                  :user-id="userId"
                  @login-complete="onLoginComplete"
                  @login-error="onLoginError"
                />
                <div class="row justify-between q-mt-lg">
                  <q-btn
                    flat
                    color="primary"
                    label="Back"
                    @click="currentStep = 2"
                  />
                  <q-btn
                    color="primary"
                    label="Next"
                    @click="currentStep = 4"
                    :disable="!canProceedToStep4"
                  />
                </div>
              </div>

              <!-- Step 4: Business Setup -->
              <div v-if="currentStep === 4">
                <div class="text-h5 q-mb-lg">Business Details</div>
                <business-setup
                  @business-setup-complete="onBusinessSetupComplete"
                  @business-setup-error="onBusinessSetupError"
                />
                <div class="row justify-between q-mt-lg">
                  <q-btn
                    flat
                    color="primary"
                    label="Back"
                    @click="currentStep = 3"
                  />
                  <div class="row q-gutter-sm">
                    <q-btn
                      color="primary"
                      label="Skip Setup"
                      @click="onSkipSetup"
                      :disable="!canProceedToStep5"
                    />
                    <q-btn
                      color="primary"
                      label="Continue Setup"
                      @click="currentStep = 5"
                      :disable="!canProceedToComplete"
                    />
                  </div>
                </div>
              </div>

              <!-- Step 5: Branch Setup -->
              <!-- <div v-if="currentStep === 5">
                <div class="text-h5 q-mb-lg">Branch Setup</div>
                <branch-setup
                  @branch-setup-complete="onBranchSetupComplete"
                  @branch-setup-error="onBranchSetupError"
                />
                <div class="row justify-between q-mt-lg">
                  <q-btn
                    flat
                    color="primary"
                    label="Back"
                    @click="currentStep = 4"
                  />
                  <q-btn
                    color="primary"
                    label="Next"
                    @click="currentStep = 6"
                    :disable="!canProceedToStep6"
                  />
                </div>
              </div> -->

              <!-- Step 6: Staff Setup -->
              <!-- <div v-if="currentStep === 6">
                <div class="text-h5 q-mb-lg">Staff Setup</div>
                <staff-setup
                  :business-id="businessId"
                  @staff-setup-complete="onStaffSetupComplete"
                  @staff-setup-error="onStaffSetupError"
                />
                <div class="row justify-between q-mt-lg">
                  <q-btn
                    flat
                    color="primary"
                    label="Back"
                    @click="currentStep = 5"
                  />
                  <q-btn
                    color="primary"
                    label="Complete Setup"
                    @click="onStaffSetupComplete"
                    :disable="!canProceedToComplete"
                  />
                </div>
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import {
  RegistrationForm,
  OtpVerification,
  BusinessSetup,
  // BranchSetup,
  // StaffSetup,
  LoginForm
} from 'src/components/onboarding'

export default {
  name: 'OnboardingPage',
  components: {
    RegistrationForm,
    OtpVerification,
    BusinessSetup,
    // BranchSetup,
    // StaffSetup,
    LoginForm
  },
  setup () {
    const router = useRouter()
    const $q = useQuasar()
    const currentStep = ref(1)
    const userId = ref(null)
    const businessId = ref(null)

    const steps = [
      {
        title: 'Create Account',
        description: 'Register your account details'
      },
      {
        title: 'Verify Account',
        description: 'Confirm your phone number'
      },
      {
        title: 'Login',
        description: 'Sign in to your account'
      },
      {
        title: 'Business Setup',
        description: 'Configure your business details'
      }
      // {
      //   title: 'Branch Setup',
      //   description: 'Set up your first branch'
      // },
      // {
      //   title: 'Staff Setup',
      //   description: 'Create staff accounts'
      // }
    ]

    // Error states
    const registrationError = ref(false)
    const verificationError = ref(false)
    const loginError = ref(false)
    const businessSetupError = ref(false)
    // const branchSetupError = ref(false)
    // const staffSetupError = ref(false)

    // Step completion states
    const registrationComplete = ref(false)
    const verificationComplete = ref(false)
    const loginComplete = ref(false)
    const businessSetupComplete = ref(false)
    // const branchSetupComplete = ref(false)
    // const staffSetupComplete = ref(false)

    // Computed properties for step navigation
    const canProceedToStep2 = computed(() => registrationComplete.value)
    const canProceedToStep3 = computed(() => verificationComplete.value)
    const canProceedToStep4 = computed(() => loginComplete.value)
    const canProceedToStep5 = computed(() => businessSetupComplete.value)
    // const canProceedToStep6 = computed(() => branchSetupComplete.value)
    // const canProceedToComplete = computed(() => staffSetupComplete.value)

    // Load onboarding progress from localStorage
    const loadOnboardingProgress = () => {
      const progress = JSON.parse(localStorage.getItem('onboarding_progress') || '{}')
      userId.value = progress.userId || null
      businessId.value = progress.businessId || null

      // Determine the current step based on progress
      if (progress.registrationComplete && !progress.verificationComplete) {
        currentStep.value = 2 // OTP Verification
      } else if (progress.verificationComplete && !progress.loginComplete) {
        currentStep.value = 3 // Login
      } else if (progress.loginComplete && !progress.businessSetupComplete) {
        currentStep.value = 4 // Business Setup
      }
      //  else if (progress.businessSetupComplete && !progress.branchSetupComplete) {
      //   currentStep.value = 5 // Branch Setup
      // } else if (progress.branchSetupComplete && !progress.staffSetupComplete) {
      //   currentStep.value = 6 // Staff Setup
      // }
    }

    // Save onboarding progress to localStorage
    const saveOnboardingProgress = (updates) => {
      const progress = JSON.parse(localStorage.getItem('onboarding_progress') || '{}')
      localStorage.setItem('onboarding_progress', JSON.stringify({
        ...progress,
        ...updates
      }))
    }

    // Clear onboarding progress
    const clearOnboardingProgress = () => {
      localStorage.removeItem('onboarding_progress')
    }

    onMounted(() => {
      loadOnboardingProgress()
    })

    const onRegistrationComplete = (id) => {
      userId.value = id
      registrationComplete.value = true
      registrationError.value = false
      currentStep.value = 2
      saveOnboardingProgress({
        userId: id,
        registrationComplete: true
      })
      $q.notify({
        type: 'positive',
        message: 'Registration successful! Please verify your account.'
      })
    }

    const onRegistrationError = () => {
      registrationError.value = true
      $q.notify({
        type: 'negative',
        message: 'Registration failed. Please try again.'
      })
    }

    const onVerificationComplete = () => {
      verificationComplete.value = true
      verificationError.value = false
      currentStep.value = 3
      saveOnboardingProgress({
        verificationComplete: true
      })
      $q.notify({
        type: 'positive',
        message: 'Account verified successfully! Please login to continue.'
      })
    }

    const onVerificationError = () => {
      verificationError.value = true
      $q.notify({
        type: 'negative',
        message: 'Verification failed. Please try again.'
      })
    }

    const onLoginComplete = () => {
      loginComplete.value = true
      loginError.value = false
      currentStep.value = 4
      saveOnboardingProgress({
        loginComplete: true
      })
      $q.notify({
        type: 'positive',
        message: 'Login successful! Please setup your business.'
      })
    }

    const onLoginError = () => {
      loginError.value = true
      $q.notify({
        type: 'negative',
        message: 'Login failed. Please try again.'
      })
    }

    const onBusinessSetupComplete = (id) => {
      businessId.value = id
      $q.notify({
        type: 'positive',
        message: 'Business setup completed successfully!'
      })
      clearOnboardingProgress()
      $q.notify({
        type: 'positive',
        message: 'Setup completed successfully! Redirecting to dashboard...'
      })
      setTimeout(() => {
        router.push(`/business/${businessId.value}/dashboard`)
      }, 1500)
    }

    const onBusinessSetupError = () => {
      businessSetupError.value = true
      $q.notify({
        type: 'negative',
        message: 'Business setup failed. Please try again.'
      })
    }

    const onBranchSetupComplete = () => {
      branchSetupComplete.value = true
      branchSetupError.value = false
      currentStep.value = 6
      $q.notify({
        type: 'positive',
        message: 'Branch setup completed!'
      })
    }

    const onBranchSetupError = () => {
      branchSetupError.value = true
      $q.notify({
        type: 'negative',
        message: 'Branch setup failed. Please try again.'
      })
    }

    const onStaffSetupComplete = () => {
      staffSetupComplete.value = true
      staffSetupError.value = false
      clearOnboardingProgress()
      $q.notify({
        type: 'positive',
        message: 'Setup completed successfully! Redirecting to dashboard...'
      })
      setTimeout(() => {
        router.push(`/business/${businessId.value}/dashboard`)
      }, 1500)
    }

    const onStaffSetupError = () => {
      staffSetupError.value = true
      $q.notify({
        type: 'negative',
        message: 'Staff setup failed. Please try again.'
      })
    }

    const onSkipSetup = () => {
      $q.dialog({
        title: 'Skip Setup',
        message: 'Are you sure you want to skip branch and staff setup? You can set these up later from the dashboard.',
        cancel: true,
        persistent: true
      }).onOk(() => {
        clearOnboardingProgress()
        router.push(`/business/${businessId.value}/dashboard`)
      })
    }

    return {
      currentStep,
      userId,
      businessId,
      steps,
      registrationError,
      verificationError,
      loginError,
      businessSetupError,
      branchSetupError,
      staffSetupError,
      canProceedToStep2,
      canProceedToStep3,
      canProceedToStep4,
      canProceedToStep5,
      canProceedToStep6,
      canProceedToComplete,
      onRegistrationComplete,
      onRegistrationError,
      onVerificationComplete,
      onVerificationError,
      onLoginComplete,
      onLoginError,
      onBusinessSetupComplete,
      onBusinessSetupError,
      onBranchSetupComplete,
      onBranchSetupError,
      onStaffSetupComplete,
      onStaffSetupError,
      onSkipSetup
    }
  }
}
</script>

<style lang="scss" scoped>
.full-height {
  min-height: 100vh;
}

.left-panel {
  position: relative;
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  @media (max-width: 1023px) {
    display: none;
  }

  .animated-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, #FF6B6B, #4ECDC4, #45B7D1, #96C93D);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
    z-index: 0;
  }

  .left-panel-content {
    position: relative;
    z-index: 1;
    max-width: 400px;
    width: 100%;
  }

  .branding {
    text-align: center;
  }

  .progress-section {
    .progress-steps {
      .progress-step {
        display: flex;
        align-items: center;
        padding: 16px;
        margin-bottom: 16px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);

        &.step-completed {
          background: rgba(255, 255, 255, 0.2);
          transform: translateX(10px);
        }

        &.step-active {
          background: rgba(255, 255, 255, 0.3);
          transform: scale(1.02);
          box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .step-number {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.2);
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          margin-right: 16px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          transition: all 0.3s ease;
        }

        .step-info {
          flex: 1;

          .step-title {
            color: white;
            font-weight: 500;
            margin-bottom: 4px;
          }

          .step-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9em;
          }
        }
      }
    }
  }
}

.right-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #f8f9fa;

  .right-panel-content {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
  }

  .step-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    padding: 32px;
    min-height: 400px;
    display: flex;
    flex-direction: column;

    .text-h5 {
      color: #1976D2;
      font-weight: 600;
    }
  }
}

// Animations
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.2s forwards;
  opacity: 0;
}

.logo-animation {
  animation: slideDown 0.5s ease-out forwards;
}

.check-animation {
  animation: scaleIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

// Mobile Responsiveness
@media (max-width: 599px) {
  .right-panel {
    padding: 20px;

    .step-container {
      padding: 20px;
      min-height: 300px;

      .text-h5 {
        font-size: 1.25rem;
      }
    }
  }
}
</style>
