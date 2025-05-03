<template>
  <div class="branch-setup">
    <div class="text-center q-mb-lg">
      <div class="text-h6">Setup Your First Branch</div>
      <div class="text-subtitle2 q-mt-sm">
        You can add more branches later from the dashboard
      </div>
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md">
      <div class="row q-col-gutter-md">
        <div class="col-12">
          <q-input
            v-model="form.name"
            label="Branch Name"
            :rules="[val => !!val || 'Branch name is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="store" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.address"
            label="Branch Address"
            type="textarea"
            :rules="[val => !!val || 'Address is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="location_on" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.phone"
            label="Branch Phone"
            mask="(###) ###-####"
            :rules="[
              val => !!val || 'Phone number is required',
              val => val.length === 14 || 'Invalid phone number'
            ]"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="phone" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.email"
            label="Branch Email"
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
        </div>

        <div class="col-12">
          <div class="text-subtitle2 q-mb-sm">Branch Settings</div>
          <q-toggle
            v-model="form.settings.enable_inventory"
            label="Enable Inventory Management"
          />
          <q-toggle
            v-model="form.settings.enable_sales"
            label="Enable Sales Management"
            class="q-mt-sm"
          />
          <q-toggle
            v-model="form.settings.enable_reports"
            label="Enable Reports"
            class="q-mt-sm"
          />
        </div>
      </div>

      <div class="row justify-end q-mt-md">
        <q-btn
          label="Create Branch"
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
  name: 'BranchSetup',
  emits: ['branch-setup-complete'],
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)

    const form = reactive({
      name: '',
      address: '',
      phone: '',
      email: '',
      settings: {
        enable_inventory: true,
        enable_sales: true,
        enable_reports: true
      }
    })

    const isValidEmail = (email) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(email)
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        const response = await api.post('/onboarding/setup-branch', {
          ...form,
          settings: JSON.stringify(form.settings)
        })

        emit('branch-setup-complete')
        
        $q.notify({
          type: 'positive',
          message: 'Branch created successfully!'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to create branch. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      isValidEmail,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.branch-setup {
  padding: 20px 0;
}
</style> 