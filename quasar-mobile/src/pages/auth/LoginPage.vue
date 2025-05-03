<template>
  <div class="login-page fade-in">
    <div class="text-h5 text-weight-bold q-mb-lg text-gradient">Welcome Back</div>
    
    <q-form @submit="onSubmit" class="q-gutter-md">
      <GlassInput
        v-model="email"
        label="Email"
        type="email"
        :rules="[val => !!val || 'Email is required', isValidEmail]"
        outlined
      >
        <template #prepend>
          <q-icon name="mail" />
        </template>
      </GlassInput>

      <GlassInput
        v-model="password"
        label="Password"
        :type="isPwd ? 'password' : 'text'"
        :rules="[val => !!val || 'Password is required']"
        outlined
      >
        <template #prepend>
          <q-icon name="lock" />
        </template>
        <template #append>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </GlassInput>

      <div class="row justify-between items-center q-mb-lg">
        <q-checkbox v-model="rememberMe" label="Remember me" class="text-grey-7" />
        <GlassButton
          flat
          label="Forgot Password?"
          to="/auth/forgot-password"
          text-gradient
        />
      </div>

      <GlassButton
        label="Login"
        type="submit"
        full-width
        size="lg"
        :loading="authStore.loading"
      />

      <div class="text-center q-mt-md">
        <span class="text-grey-7">Don't have an account? </span>
        <GlassButton
          flat
          label="Register"
          to="/auth/register"
          text-gradient
          class="q-ml-sm"
        />
      </div>
    </q-form>

    <!-- Social Login -->
    <div class="text-center q-mt-xl">
      <div class="text-grey-7 q-mb-md">Or continue with</div>
      <div class="row justify-center q-gutter-md">
        <GlassButton round flat icon="fab fa-google" />
        <GlassButton round flat icon="fab fa-facebook" />
        <GlassButton round flat icon="fab fa-twitter" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth'
import GlassInput from 'components/common/GlassInput.vue'
import GlassButton from 'components/common/GlassButton.vue'

const $q = useQuasar()
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const isPwd = ref(true)
const rememberMe = ref(false)

const isValidEmail = (val) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
  return emailPattern.test(val) || 'Invalid email'
}

const onSubmit = async () => {
  try {
    const userData = await authStore.login(email.value, password.value)
    console.log('Login successful, user data:', userData)
    
    $q.notify({
      color: 'positive',
      message: 'Login successful',
      icon: 'check_circle'
    })

    // Get the redirect path based on user role
    const redirectPath = authStore.getRedirectPath()
    console.log('Redirect path:', redirectPath)
    console.log('User role:', authStore.user?.role)
    
    // If there's a redirect query parameter, use it instead
    const finalPath = route.query.redirect || redirectPath
    console.log('Final path:', finalPath)
    
    // Ensure we have a valid path
    if (!finalPath || finalPath === '/') {
      console.error('Invalid redirect path:', finalPath)
      throw new Error('Invalid redirect path')
    }
    
    router.push(finalPath)
  } catch (error) {
    console.error('Login error:', error)
    $q.notify({
      color: 'negative',
      message: error.message || 'Login failed',
      icon: 'error'
    })
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  .glass-input {
    .q-field__control {
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      border-radius: 8px;
      transition: all 0.3s ease;

      &:hover {
        border-color: var(--accent-color);
      }
    }

    .q-field__label {
      color: var(--text-secondary);
    }
  }

  .glass-button {
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px var(--glass-shadow);
    }

    &.text-gradient {
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  }
}
</style>
