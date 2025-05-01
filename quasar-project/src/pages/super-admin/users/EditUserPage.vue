<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Edit User</div>
      <q-btn
        color="primary"
        icon="arrow_back"
        label="Back to Users"
        to="/super-admin/users"
      />
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md" v-if="user">
      <div class="row q-col-gutter-md">
        <!-- Basic Information -->
        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Basic Information</div>
              
              <q-input
                v-model="form.name"
                label="Full Name"
                :rules="[val => !!val || 'Name is required']"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[
                  val => !!val || 'Email is required',
                  val => isValidEmail(val) || 'Invalid email format'
                ]"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.password"
                label="New Password (leave blank to keep current)"
                type="password"
                :rules="[
                  val => !val || val.length >= 6 || 'Password must be at least 6 characters'
                ]"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.c_password"
                label="Confirm New Password"
                type="password"
                :rules="[
                  val => !form.password || val === form.password || 'Passwords do not match'
                ]"
                outlined
                class="q-mb-md"
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Role and Business Information -->
        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Role and Business Information</div>
              
              <q-select
                v-model="form.role"
                :options="roleOptions"
                label="Role"
                :rules="[val => !!val || 'Role is required']"
                outlined
                class="q-mb-md"
              />

              <q-select
                v-if="form.role && form.role !== 'super_admin' && form.role !== 'default'"
                v-model="form.business_id"
                :options="businessOptions"
                label="Business"
                :rules="[val => !!val || 'Business is required']"
                outlined
                class="q-mb-md"
              />

              <q-select
                v-if="form.role && form.role !== 'super_admin' && form.role !== 'default'"
                v-model="form.branch_id"
                :options="branchOptions"
                label="Branch"
                :rules="[val => !!val || 'Branch is required']"
                outlined
                class="q-mb-md"
              />

              <q-toggle
                v-model="form.is_active"
                label="Active"
                color="positive"
              />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="row justify-end q-mt-lg">
        <q-btn
          label="Cancel"
          color="grey"
          class="q-mr-sm"
          to="/super-admin/users"
        />
        <q-btn
          label="Update User"
          type="submit"
          color="primary"
          :loading="loading"
        />
      </div>
    </q-form>

    <div v-else class="text-center q-pa-lg">
      <q-spinner color="primary" size="3em" />
      <div class="text-h6 q-mt-md">Loading user details...</div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { userService } from '../../../services/user'
import { businessService } from '../../../services/business'

const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// State
const user = ref(null)
const loading = ref(false)
const businessOptions = ref([])
const branchOptions = ref([])

// Form state
const form = ref({
  name: '',
  email: '',
  password: '',
  c_password: '',
  role: '',
  business_id: null,
  branch_id: null,
  is_active: true
})

// Role options (excluding super_admin)
const roleOptions = [
  { label: 'Admin', value: 'admin' },
  { label: 'Branch Manager', value: 'branch_manager' },
  { label: 'Cashier', value: 'cashier' },
  { label: 'Inventory Clerk', value: 'inventory_clerk' },
  { label: 'Default', value: 'default' }
]

// Methods
const isValidEmail = (val) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
  return emailPattern.test(val) || 'Invalid email'
}

const fetchUser = async () => {
  try {
    loading.value = true
    user.value = await userService.getUser(route.params.id)
    
    // Set form values
    form.value = {
      name: user.value.name,
      email: user.value.email,
      password: '',
      c_password: '',
      role: user.value.role,
      business_id: user.value.business_id,
      branch_id: user.value.branch_id,
      is_active: user.value.is_active
    }
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
    router.push('/super-admin/users')
  } finally {
    loading.value = false
  }
}

const fetchBusinesses = async () => {
  try {
    const businesses = await businessService.getBusinesses()
    businessOptions.value = businesses.map(business => ({
      label: business.name,
      value: business.id
    }))
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const fetchBranches = async (businessId) => {
  try {
    const business = await businessService.getBusiness(businessId)
    branchOptions.value = business.branches.map(branch => ({
      label: branch.name,
      value: branch.id
    }))
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

// Watch for business selection to update branches
watch(() => form.value.business_id, (newVal) => {
  if (newVal) {
    fetchBranches(newVal)
  } else {
    branchOptions.value = []
    form.value.branch_id = null
  }
})

const onSubmit = async () => {
  try {
    loading.value = true
    
    // Prepare update data
    const updateData = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      business_id: form.value.business_id,
      branch_id: form.value.branch_id,
      is_active: form.value.is_active
    }

    // Only include password if it's being changed
    if (form.value.password) {
      updateData.password = form.value.password
      updateData.c_password = form.value.c_password
    }
    
    // Update user
    await userService.updateUser(route.params.id, updateData)

    $q.notify({
      color: 'positive',
      message: 'User updated successfully',
      icon: 'check_circle'
    })

    router.push('/super-admin/users')
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUser()
  fetchBusinesses()
})
</script>

<style lang="scss" scoped>
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px var(--glass-shadow);
  }
}
</style> 