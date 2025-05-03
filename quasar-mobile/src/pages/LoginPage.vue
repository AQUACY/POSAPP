<template>
  <q-page class="flex flex-center">
    <q-card class="login-card">
      <q-card-section>
        <div class="text-h6">Login</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="onSubmit" class="q-gutter-md">
          <q-input
            v-model="email"
            label="Email"
            type="email"
            :rules="[val => !!val || 'Email is required']"
          />

          <q-input
            v-model="password"
            label="Password"
            type="password"
            :rules="[val => !!val || 'Password is required']"
          />

          <div>
            <q-btn
              label="Login"
              type="submit"
              color="primary"
              class="full-width"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { authService } from 'src/services/auth'

export default {
  name: 'LoginPage',
  setup() {
    const $q = useQuasar()
    const router = useRouter()
    const email = ref('')
    const password = ref('')
    const loading = ref(false)

    const onSubmit = async () => {
      try {
        loading.value = true
        await authService.login(email.value, password.value)
        $q.notify({
          color: 'positive',
          message: 'Login successful'
        })
        router.push('/')
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: error.message || 'Login failed'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      email,
      password,
      loading,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.login-card {
  width: 100%;
  max-width: 400px;
  margin: 20px;
}
</style> 