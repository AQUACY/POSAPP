<template>
  <div class="otp-verification">
    <div class="text-center q-mb-lg">
      <div class="text-h6">Verify Your Account</div>
      <div class="text-subtitle2 q-mt-sm">
        Please enter the 6-digit code sent to your phone
      </div>
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md">
      <div class="row justify-center">
        <q-input
          v-model="otp"
          label="Enter OTP"
          mask="######"
          :rules="[
            val => !!val || 'OTP is required',
            val => val.length === 6 || 'OTP must be 6 digits'
          ]"
          outlined
          class="otp-input"
        >
          <template v-slot:prepend>
            <q-icon name="lock" />
          </template>
        </q-input>
      </div>

      <div class="row justify-center q-mt-md">
        <q-btn
          label="Verify"
          type="submit"
          color="primary"
          :loading="loading"
          class="full-width"
        />
      </div>

      <div class="row justify-center q-mt-sm">
        <q-btn
          flat
          label="Resend OTP"
          color="primary"
          :loading="resendLoading"
          :disable="countdown > 0"
          @click="resendOtp"
        />
        <span v-if="countdown > 0" class="q-ml-sm text-caption">
          Resend in {{ countdown }}s
        </span>
      </div>
    </q-form>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useQuasar } from 'quasar'
import  api from '../../services/api'

export default {
  name: 'OtpVerification',
  props: {
    userId: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['verification-complete'],
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)
    const resendLoading = ref(false)
    const otp = ref('')
    const countdown = ref(0)
    let timer = null

    const startCountdown = () => {
      countdown.value = 60
      timer = setInterval(() => {
        if (countdown.value > 0) {
          countdown.value--
        } else {
          clearInterval(timer)
        }
      }, 1000)
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        await api.post('/onboarding/verify-otp', {
          user_id: props.userId,
          otp: otp.value
        })

        emit('verification-complete')

        $q.notify({
          type: 'positive',
          message: 'Account verified successfully!'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Verification failed. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    const resendOtp = async () => {
      try {
        resendLoading.value = true
        await api.post('/onboarding/resend-otp', {
          user_id: props.userId
        })

        startCountdown()

        $q.notify({
          type: 'positive',
          message: 'OTP resent successfully!'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to resend OTP. Please try again.'
        })
      } finally {
        resendLoading.value = false
      }
    }

    onMounted(() => {
      startCountdown()
    })

    onUnmounted(() => {
      if (timer) {
        clearInterval(timer)
      }
    })

    return {
      otp,
      loading,
      resendLoading,
      countdown,
      onSubmit,
      resendOtp
    }
  }
}
</script>

<style lang="scss" scoped>
.otp-verification {
  padding: 20px 0;

  .otp-input {
    width: 200px;
  }
}
</style>