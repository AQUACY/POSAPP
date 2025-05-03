<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">User Details</div>
      <div>
        <q-btn
          color="primary"
          icon="edit"
          label="Edit User"
          :to="`/super-admin/users/${$route.params.id}/edit`"
          class="q-mr-sm"
        />
        <q-btn
          color="primary"
          icon="arrow_back"
          label="Back to Users"
          to="/super-admin/users"
        />
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- User Information -->
      <div class="col-12 col-md-8">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">User Information</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Full Name</div>
                <div class="text-body1">{{ user.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Email</div>
                <div class="text-body1">{{ user.email }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Role</div>
                <q-chip
                  :color="getRoleColor(user.role)"
                  text-color="white"
                  size="sm"
                >
                  {{ formatRole(user.role) }}
                </q-chip>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Status</div>
                <q-chip
                  :color="user.is_active ? 'positive' : 'negative'"
                  text-color="white"
                  size="sm"
                >
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </q-chip>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Business Information -->
        <q-card class="glass-card q-mt-md" v-if="user.business">
          <q-card-section>
            <div class="text-h6 q-mb-md">Business Information</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Business Name</div>
                <div class="text-body1">{{ user.business.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Business Email</div>
                <div class="text-body1">{{ user.business.email }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Business Phone</div>
                <div class="text-body1">{{ user.business.phone }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Business Address</div>
                <div class="text-body1">{{ user.business.address }}</div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Branch Information -->
        <q-card class="glass-card q-mt-md" v-if="user.branch">
          <q-card-section>
            <div class="text-h6 q-mb-md">Branch Information</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Branch Name</div>
                <div class="text-body1">{{ user.branch.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">Branch Address</div>
                <div class="text-body1">{{ user.branch.address }}</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Activity Log -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Recent Activity</div>
            <q-list>
              <q-item v-for="activity in activities" :key="activity.id">
                <q-item-section avatar>
                  <q-avatar :color="getActivityColor(activity.type)" text-color="white">
                    <q-icon :name="getActivityIcon(activity.type)" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ activity.description }}</q-item-label>
                  <q-item-label caption>{{ formatDate(activity.created_at) }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'

const route = useRoute()
const $q = useQuasar()

// State
const user = ref({})
const activities = ref([])

// Methods
const fetchUser = async () => {
  try {
    // TODO: Replace with actual API call
    // const response = await api.get(`/super-admin/users/${route.params.id}`)
    // user.value = response.data
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load user' + err,
      icon: 'error'
    })
  }
}

const fetchActivities = async () => {
  try {
    // TODO: Replace with actual API call
    // const response = await api.get(`/super-admin/users/${route.params.id}/activities`)
    // activities.value = response.data
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load activities' + err,
      icon: 'error'
    })
  }
}

const getRoleColor = (role) => {
  switch (role) {
    case 'super_admin':
      return 'purple'
    case 'admin':
      return 'primary'
    case 'branch_manager':
      return 'secondary'
    case 'cashier':
      return 'accent'
    case 'inventory_clerk':
      return 'teal'
    default:
      return 'grey'
  }
}

const formatRole = (role) => {
  return role.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const getActivityColor = (type) => {
  switch (type) {
    case 'login':
      return 'primary'
    case 'logout':
      return 'grey'
    case 'create':
      return 'positive'
    case 'update':
      return 'warning'
    case 'delete':
      return 'negative'
    default:
      return 'grey'
  }
}

const getActivityIcon = (type) => {
  switch (type) {
    case 'login':
      return 'login'
    case 'logout':
      return 'logout'
    case 'create':
      return 'add'
    case 'update':
      return 'edit'
    case 'delete':
      return 'delete'
    default:
      return 'info'
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(() => {
  fetchUser()
  fetchActivities()
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