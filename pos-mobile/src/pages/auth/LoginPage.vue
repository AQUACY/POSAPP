<template>
  <q-page class="login-page flex flex-center">
    <div class="login-container">
      <div class="text-center q-mb-lg">
        <h2 class="text-h4 text-white q-mb-sm">Welcome Back</h2>
        <p class="text-subtitle1 text-grey-4">Sign in to continue</p>
      </div>

      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-input
          v-model="email"
          type="email"
          label="Email"
          :rules="[val => !!val || 'Email is required', isValidEmail]"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="mail" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="password"
          :type="isPwd ? 'password' : 'text'"
          label="Password"
          :rules="[val => !!val || 'Password is required']"
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

        <div class="row justify-between items-center q-mb-md">
          <q-checkbox
            v-model="rememberMe"
            label="Remember me"
            color="primary"
            dark
          />
          <q-btn
            flat
            color="primary"
            label="Forgot Password?"
            class="text-caption"
            @click="onForgotPassword"
          />
        </div>

        <q-btn
          type="submit"
          color="primary"
          label="Sign In"
          class="full-width q-py-sm"
          size="lg"
          :loading="loading"
        />

        <div class="text-center q-mt-lg">
          <p class="text-grey-4">
            Don't have an account?
            <q-btn
              flat
              color="primary"
              label="Create Account"
              class="text-weight-medium"
              @click="$router.push('/onboarding')"
            />
          </p>
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'LoginPage',
  setup() {
    const $q = useQuasar()
    const email = ref('')
    const password = ref('')
    const isPwd = ref(true)
    const rememberMe = ref(false)
    const loading = ref(false)

    const isValidEmail = (val) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(val) || 'Invalid email'
    }

    const onSubmit = async () => {
      loading.value = true
      try {
        // TODO: Implement login logic
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simulated API call
        $q.notify({
          color: 'positive',
          message: 'Login successful',
          position: 'top'
        })
      } catch {
        $q.notify({
          color: 'negative',
          message: 'Login failed',
          position: 'top'
        })
      } finally {
        loading.value = false
      }
    }

    const onForgotPassword = () => {
      // TODO: Implement forgot password logic
    }

    return {
      email,
      password,
      isPwd,
      rememberMe,
      loading,
      isValidEmail,
      onSubmit,
      onForgotPassword
    }
  }
})
</script>

<style lang="scss" scoped>
.login-page {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
}

.login-container {
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