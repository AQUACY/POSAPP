<template>
  <q-page class="register-page flex flex-center">
    <div class="register-container">
      <div class="text-center q-mb-lg">
        <h2 class="text-h4 text-white q-mb-sm">Create Your Account</h2>
        <p class="text-subtitle1 text-grey-4">Step 1 of 4</p>
      </div>

      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-input
          v-model="form.name"
          label="Full Name"
          :rules="rules.name"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="person" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.email"
          type="email"
          label="Email"
          :rules="rules.email"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="mail" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.phone"
          label="Phone Number"
          :rules="rules.phone"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="phone" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.password"
          :type="isPwd ? 'password' : 'text'"
          label="Password"
          :rules="rules.password"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="lock" color="grey-4" />
          </template>
          <template v-slot:append>
            <q-icon
              :name="isPwd ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              color="grey-4"
              @click="isPwd = !isPwd"
            />
          </template>
        </q-input>

        <q-input
          v-model="form.confirmPassword"
          :type="isPwd ? 'password' : 'text'"
          label="Confirm Password"
          :rules="rules.confirmPassword"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="lock" color="grey-4" />
          </template>
        </q-input>

        <q-btn
          type="submit"
          color="primary"
          label="Continue"
          class="full-width q-py-sm"
          size="lg"
          :loading="loading"
        />
      </q-form>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useOnboardingStore } from '../../stores/onboarding'  
import onboardingService from '../../services/onboarding'

export default defineComponent({
  name: 'RegisterPage',
  emits: ['update:step'],
  setup(props, { emit }) {
    const $q = useQuasar()
    const router = useRouter()
    const onboardingStore = useOnboardingStore()
    const isPwd = ref(true)
    const loading = ref(false)

    const form = ref({
      name: onboardingStore.registrationData.name || '',
      email: onboardingStore.registrationData.email || '',
      phone: onboardingStore.registrationData.phone || '',
      password: '',
      confirmPassword: ''
    })

    const rules = {
      name: [
        val => !!val || 'Name is required',
        val => val.length <= 255 || 'Name must be less than 255 characters'
      ],
      email: [
        val => !!val || 'Email is required',
        val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email format',
        val => val.length <= 255 || 'Email must be less than 255 characters'
      ],
      phone: [
        val => !!val || 'Phone number is required',
        val => /^\+?[\d\s-]{10,}$/.test(val) || 'Invalid phone number format'
      ],
      password: [
        val => !!val || 'Password is required',
        val => val.length >= 8 || 'Password must be at least 8 characters'
      ],
      confirmPassword: [
        val => !!val || 'Please confirm your password',
        val => val === form.value.password || 'Passwords do not match'
      ]
    }

    onMounted(() => {
      emit('update:step', 1)
      onboardingStore.setCurrentStep(1)
    })

    const onSubmit = async () => {
      loading.value = true
      try {
        const response = await onboardingService.register({
          name: form.value.name,
          email: form.value.email,
          password: form.value.password,
          phone: form.value.phone
        })

        // Store registration data in onboarding store
        onboardingStore.setRegistrationData({
          name: form.value.name,
          email: form.value.email,
          phone: form.value.phone,
          userId: response.user_id
        })

        $q.notify({
          color: 'positive',
          message: response.message || 'Registration successful',
          position: 'top'
        })

        // Redirect to OTP verification
        router.push('/onboarding/verify-otp')
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Registration failed'
        $q.notify({
          color: 'negative',
          message: errorMessage,
          position: 'top'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      rules,
      isPwd,
      loading,
      onSubmit
    }
  }
})
</script>

<style lang="scss" scoped>
.register-page {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
  padding-top: 4rem;
}

.register-container {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
}

.custom-input {
  :deep(.q-field__control) {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
  }

  :deep(.q-field__label) {
    color: rgba(255, 255, 255, 0.7);
  }

  :deep(.q-field__native) {
    color: white;
  }
}
</style> 