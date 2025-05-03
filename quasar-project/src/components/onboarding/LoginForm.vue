<template>
  <div class="login-form">
    <q-form @submit="onSubmit" class="q-gutter-md">
      <q-input
        v-model="form.email"
        label="Email"
        type="email"
        :rules="[
          val => !!val || 'Email is required',
          val => isValidEmail(val) || 'Invalid email format'
        ]"
        outlined
      >
        <template v-slot:prepend>
          <q-icon name="email" />
        </template>
      </q-input>

      <q-input
        v-model="form.password"
        label="Password"
        :type="isPwd ? 'password' : 'text'"
        :rules="[val => !!val || 'Password is required']"
        outlined
      >
        <template v-slot:prepend>
          <q-icon name="lock" />
        </template>
        <template v-slot:append>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </q-input>

      <div class="row justify-end q-mt-md">
        <q-btn
          label="Login"
          type="submit"
          color="primary"
          :loading="loading"
          class="full-width"
        />
      </div>
    </q-form>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

export default {
  name: 'LoginForm',
  props: {
    userId: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['login-complete', 'login-error'],
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)
    const isPwd = ref(true)

    const form = reactive({
      email: '',
      password: ''
    })

    const isValidEmail = (email) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(email)
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        const response = await api.post('/auth/login', {
          email: form.email,
          password: form.password
        })

        // Store the token in localStorage
        localStorage.setItem('token', response.data.data.token)
        
        // Set the token in axios defaults for future requests
        api.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`

        emit('login-complete')

        $q.notify({
          type: 'positive',
          message: 'Login successful!'
        })
      } catch (error) {
        emit('login-error')
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Login failed. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      isPwd,
      isValidEmail,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.login-form {
  padding: 20px 0;
}
</style> 