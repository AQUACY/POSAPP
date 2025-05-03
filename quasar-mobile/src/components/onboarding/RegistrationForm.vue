<template>
  <div class="registration-form">
    <q-form @submit="onSubmit" class="q-gutter-md">
      <q-input
        v-model="form.name"
        label="Full Name"
        :rules="[val => !!val || 'Name is required']"
        outlined
      >
        <template v-slot:prepend>
          <q-icon name="person" />
        </template>
      </q-input>

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
        v-model="form.phone"
        label="Phone Number"
        mask="(###) ###-####"
        :rules="[
          val => !!val || 'Phone number is required',
          val => val.length === 14 || 'Invalid phone number'
        ]"
        outlined
      >
        <template v-slot:prepend>
          <q-select
            v-model="form.countryCode"
            :options="countryCodes"
            dense
            options-dense
            emit-value
            map-options
            style="min-width: 120px"
          >
            <template v-slot:selected>
              <div class="row items-center">
                <q-icon :name="form.countryCode.icon" class="q-mr-sm" />
                {{ form.countryCode.code }}
              </div>
            </template>
            <template v-slot:option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section avatar>
                  <q-icon :name="scope.opt.icon" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ scope.opt.label }}</q-item-label>
                  <q-item-label caption>{{ scope.opt.code }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </template>
        <template v-slot:append>
          <q-icon name="phone" />
        </template>
      </q-input>

      <q-input
        v-model="form.password"
        label="Password"
        :type="isPwd ? 'password' : 'text'"
        :rules="[
          val => !!val || 'Password is required',
          val => val.length >= 8 || 'Password must be at least 8 characters'
        ]"
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

      <q-input
        v-model="form.confirmPassword"
        label="Confirm Password"
        :type="isPwd ? 'password' : 'text'"
        :rules="[
          val => !!val || 'Please confirm your password',
          val => val === form.password || 'Passwords do not match'
        ]"
        outlined
      >
        <template v-slot:prepend>
          <q-icon name="lock" />
        </template>
      </q-input>

      <div class="row justify-end q-mt-md">
        <q-btn
          label="Register"
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
  name: 'RegistrationForm',
  emits: ['registration-complete'],
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)
    const isPwd = ref(true)

    const countryCodes = [
      { label: 'Ghana', code: '+233', icon: 'flag' },
      { label: 'Nigeria', code: '+234', icon: 'flag' },
      { label: 'Kenya', code: '+254', icon: 'flag' },
      { label: 'South Africa', code: '+27', icon: 'flag' },
      { label: 'United States', code: '+1', icon: 'flag' },
      { label: 'United Kingdom', code: '+44', icon: 'flag' },
      { label: 'India', code: '+91', icon: 'flag' },
      { label: 'China', code: '+86', icon: 'flag' },
      { label: 'Japan', code: '+81', icon: 'flag' },
      { label: 'Australia', code: '+61', icon: 'flag' }
    ]

    const form = reactive({
      name: '',
      email: '',
      phone: '',
      countryCode: countryCodes[0], // Default to Ghana
      password: '',
      confirmPassword: ''
    })

    const isValidEmail = (email) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(email)
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        // Remove all non-numeric characters and strip leading '0'
        const cleanPhone = form.phone.replace(/[^0-9]/g, '').replace(/^0+/, '')
        
        const response = await api.post('/onboarding/register', {
          name: form.name,
          email: form.email,
          phone: form.countryCode.code + cleanPhone,
          password: form.password
        })

        emit('registration-complete', response.data.user_id)

        $q.notify({
          type: 'positive',
          message: 'Registration successful! Please verify your account.'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Registration failed. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      isPwd,
      countryCodes,
      isValidEmail,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.registration-form {
  padding: 20px 0;
}
</style>
