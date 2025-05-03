<template>
  <div class="staff-setup">
    <div class="text-center q-mb-lg">
      <div class="text-h6">Setup Your Staff</div>
      <div class="text-subtitle2 q-mt-sm">
        Create accounts for your staff members
      </div>
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md">
      <div class="row q-col-gutter-md">
        <div class="col-12">
          <q-select
            v-model="form.role"
            :options="roleOptions"
            label="Staff Role"
            :rules="[val => !!val || 'Role is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="badge" />
            </template>
          </q-select>
        </div>

        <div class="col-12">
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
        </div>

        <div class="col-12">
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
        </div>

        <div class="col-12">
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
              <q-icon name="phone" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.whatsapp_contact"
            label="WhatsApp Contact"
            :rules="[
              val => !!val || 'WhatsApp contact is required',
              val => isValidPhone(val) || 'Invalid phone number'
            ]"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="whatsapp" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-select
            v-model="form.branch_id"
            :options="branchOptions"
            label="Assign to Branch"
            :rules="[val => !!val || 'Branch is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="store" />
            </template>
          </q-select>
        </div>
      </div>

      <div class="row justify-end q-mt-md">
        <q-btn
          label="Create Staff Account"
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
import { ref, reactive, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

export default {
  name: 'StaffSetup',
  emits: ['staff-setup-complete'],
  props: {
    businessId: {
      type: [Number, String],
      required: true
    }
  },
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)
    const branchOptions = ref([])

    const form = reactive({
      role: null,
      name: '',
      email: '',
      phone: '',
      whatsapp_contact: '',
      branch_id: null
    })

    const roleOptions = [
      { label: 'Cashier', value: 'cashier' },
      { label: 'Branch Manager', value: 'branch_manager' },
      { label: 'Inventory Clerk', value: 'inventory_clerk' }
    ]

    const isValidEmail = (email) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(email)
    }

    const isValidPhone = (phone) => {
      return phone.length >= 10
    }

    const fetchBranches = async () => {
      try {
        const response = await api.get(`/business/${props.businessId}/branches`)
        branchOptions.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: `Failed to fetch branches.  ${error}`
        })
      }
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        
        // Create a new object with only the values we need
        const payload = {
          role: form.role.value,
          branch_id: form.branch_id.value,
          name: form.name,
          email: form.email,
          phone: form.phone,
          whatsapp_contact: form.whatsapp_contact
        }

        const response = await api.post('/onboarding/create-staff', payload)

        emit('staff-setup-complete')
        
        $q.notify({
          type: 'positive',
          message: 'Staff account created successfully!'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to create staff account. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchBranches()
    })

    return {
      form,
      loading,
      roleOptions,
      branchOptions,
      isValidEmail,
      isValidPhone,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.staff-setup {
  padding: 20px 0;
}
</style> 