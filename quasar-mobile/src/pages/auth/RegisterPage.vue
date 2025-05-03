<template>
  <div class="register-page fade-in">
    <div class="text-h5 text-weight-bold q-mb-lg text-gradient">Create Account</div>
    
    <q-form @submit="onSubmit" class="q-gutter-md">
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6">
          <GlassInput
            v-model="firstName"
            label="First Name"
            :rules="[val => !!val || 'First name is required']"
            outlined
          >
            <template #prepend>
              <q-icon name="person" />
            </template>
          </GlassInput>
        </div>
        <div class="col-12 col-sm-6">
          <GlassInput
            v-model="lastName"
            label="Last Name"
            :rules="[val => !!val || 'Last name is required']"
            outlined
          >
            <template #prepend>
              <q-icon name="person" />
            </template>
          </GlassInput>
        </div>
      </div>

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
        :rules="[val => !!val || 'Password is required', val => val.length >= 8 || 'Password must be at least 8 characters']"
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

      <GlassInput
        v-model="confirmPassword"
        label="Confirm Password"
        :type="isPwd ? 'password' : 'text'"
        :rules="[val => !!val || 'Please confirm your password', val => val === password || 'Passwords do not match']"
        outlined
      >
        <template #prepend>
          <q-icon name="lock" />
        </template>
      </GlassInput>

      <q-checkbox
        v-model="terms"
        label="I agree to the Terms and Conditions"
        :rules="[val => val || 'You must agree to the terms']"
        class="text-grey-7"
      />

      <GlassButton
        label="Register"
        type="submit"
        full-width
        size="lg"
        :loading="authStore.loading"
      />

      <div class="text-center q-mt-md">
        <span class="text-grey-7">Already have an account? </span>
        <GlassButton
          flat
          label="Login"
          to="/auth/login"
          text-gradient
          class="q-ml-sm"
        />
      </div>
    </q-form>

    <!-- Social Register -->
    <div class="text-center q-mt-xl">
      <div class="text-grey-7 q-mb-md">Or register with</div>
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
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth'
import GlassInput from 'components/common/GlassInput.vue'
import GlassButton from 'components/common/GlassButton.vue'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

const firstName = ref('')
const lastName = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const isPwd = ref(true)
const terms = ref(false)

const isValidEmail = (val) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
  return emailPattern.test(val) || 'Invalid email'
}

const onSubmit = async () => {
  try {
    await authStore.register({
      firstName: firstName.value,
      lastName: lastName.value,
      email: email.value,
      password: password.value
    })
    
    $q.notify({
      color: 'positive',
      message: 'Registration successful',
      icon: 'check_circle'
    })

    // Redirect to login page
    router.push('/auth/login')
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.message || 'Registration failed',
      icon: 'error'
    })
  }
}
</script>

<style lang="scss" scoped>
.register-page {
  animation: fadeIn 0.5s ease;
}
</style>
