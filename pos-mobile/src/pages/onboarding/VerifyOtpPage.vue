<template>
  <q-page class="verify-otp-page flex flex-center">
    <div class="verify-otp-container">
      <div class="text-center q-mb-lg">
        <h2 class="text-h4 text-white q-mb-sm">Verify Your Email</h2>
        <p class="text-subtitle1 text-grey-4">
          We've sent a verification code to your email
        </p>
      </div>

      <q-form @submit="onSubmit" class="q-gutter-md">
        <div class="otp-inputs">
          <q-input
            v-for="(digit, index) in otp"
            :key="index"
            v-model="otp[index]"
            type="text"
            maxlength="1"
            :ref="el => { if (el) otpInputs[index] = el }"
            @keyup="onOtpInput($event, index)"
            @keydown.delete="onOtpDelete($event, index)"
            dark
            outlined
            class="otp-input"
          />
        </div>

        <div class="text-center q-mt-md">
          <p class="text-grey-4">
            Didn't receive the code?
            <q-btn
              flat
              color="primary"
              label="Resend"
              class="text-weight-medium"
              :loading="resending"
              @click="onResendOtp"
            />
          </p>
        </div>

        <q-btn
          type="submit"
          color="primary"
          label="Verify"
          class="full-width q-py-sm"
          size="lg"
          :loading="loading"
          :disable="!isOtpComplete"
        />
      </q-form>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useOnboardingStore } from 'stores/onboarding'
import onboardingService from 'services/onboarding'

export default defineComponent({
  name: 'VerifyOtpPage',
  emits: ['update:step'],
  setup(props, { emit }) {
    const $q = useQuasar()
    const router = useRouter()
    const onboardingStore = useOnboardingStore()
    const otp = ref(onboardingStore.otpData.otp)
    const otpInputs = ref([])
    const loading = ref(false)
    const resending = ref(false)

    const isOtpComplete = computed(() => {
      return otp.value.every(digit => digit !== '')
    })

    // Watch for OTP changes and update store
    watch(otp, (newOtp) => {
      onboardingStore.setOtpData({ otp: newOtp })
    }, { deep: true })

    onMounted(() => {
      emit('update:step', 2)
      onboardingStore.setCurrentStep(2)
      // Focus first input
      if (otpInputs.value[0]) {
        otpInputs.value[0].focus()
      }
    })

    const onOtpInput = (event, index) => {
      const value = event.target.value
      if (value && index < 5) {
        otpInputs.value[index + 1].focus()
      }
    }

    const onOtpDelete = (event, index) => {
      if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
        otpInputs.value[index - 1].focus()
      }
    }

    const onSubmit = async () => {
      loading.value = true
      try {
        const userId = onboardingStore.registrationData.userId
        const response = await onboardingService.verifyOtp(userId, otp.value)
        onboardingStore.setOtpData({ verified: true })
        $q.notify({
          color: 'positive',
          message: response.message || 'Email verified successfully',
          position: 'top'
        })
        router.push('/onboarding/setup-business')
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: error.response?.data?.message || 'Invalid verification code',
          position: 'top'
        })
      } finally {
        loading.value = false
      }
    }

    const onResendOtp = async () => {
      resending.value = true
      try {
        // TODO: Implement resend OTP endpoint
        await new Promise(resolve => setTimeout(resolve, 1000))
        $q.notify({
          color: 'positive',
          message: 'New code sent successfully',
          position: 'top'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: error.response?.data?.message || 'Failed to resend code',
          position: 'top'
        })
      } finally {
        resending.value = false
      }
    }

    return {
      otp,
      otpInputs,
      loading,
      resending,
      isOtpComplete,
      onOtpInput,
      onOtpDelete,
      onSubmit,
      onResendOtp
    }
  }
})
</script>

<style lang="scss" scoped>
.verify-otp-page {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
  padding-top: 4rem;
}

.verify-otp-container {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
}

.otp-inputs {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin: 2rem 0;

  .otp-input {
    width: 3rem;
    height: 3rem;

    :deep(.q-field__control) {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      height: 3rem;
    }

    :deep(.q-field__native) {
      text-align: center;
      font-size: 1.5rem;
      color: white;
    }
  }
}
</style> 