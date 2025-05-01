<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Users</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add User"
        to="/super-admin/users/create"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-4">
        <q-input
          v-model="search"
          outlined
          placeholder="Search users..."
          class="glass-input"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-3">
        <q-select
          v-model="roleFilter"
          :options="roleOptions"
          outlined
          label="Role"
          class="glass-input"
        />
      </div>
      <div class="col-12 col-md-3">
        <q-select
          v-model="statusFilter"
          :options="statusOptions"
          outlined
          label="Status"
          class="glass-input"
        />
      </div>
    </div>

    <!-- Users Table -->
    <q-card class="glass-card">
      <q-table
        :rows="users"
        :columns="columns"
        row-key="id"
        :loading="loading"
        v-model:pagination="pagination"
        :filter="search"
        binary-state-sort
      >
        <!-- Role Column -->
        <template #body-cell-role="props">
          <q-td :props="props">
            <q-chip
              :color="getRoleColor(props.row.role)"
              text-color="white"
              size="sm"
            >
              {{ formatRole(props.row.role) }}
            </q-chip>
          </q-td>
        </template>

        <!-- Status Column -->
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              :color="props.row.is_active ? 'positive' : 'negative'"
              text-color="white"
              size="sm"
            >
              {{ props.row.is_active ? 'Active' : 'Inactive' }}
            </q-chip>
          </q-td>
        </template>

        <!-- Actions Column -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn-group flat>
              <q-btn
                flat
                round
                icon="visibility"
                @click="onViewUser(props.row)"
              >
                <q-tooltip>View Details</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="edit"
                @click="onEditUser(props.row)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                :icon="props.row.is_active ? 'block' : 'check_circle'"
                @click="onToggleStatus(props.row)"
              >
                <q-tooltip>{{ props.row.is_active ? 'Deactivate' : 'Activate' }}</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="delete"
                @click="onDeleteUser(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </q-btn-group>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card class="glass-card">
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this user?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="confirmDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { userService } from '../../../services/user'
import { businessService } from '../../../services/business'

const $q = useQuasar()
const router = useRouter()

// Table columns
const columns = [
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    sortable: true,
    align: 'left'
  },
  {
    name: 'email',
    label: 'Email',
    field: 'email',
    sortable: true,
    align: 'left'
  },
  {
    name: 'role',
    label: 'Role',
    field: 'role',
    sortable: true,
    align: 'center'
  },
  {
    name: 'status',
    label: 'Status',
    field: 'is_active',
    sortable: true,
    align: 'center'
  },
  {
    name: 'created_at',
    label: 'Created At',
    field: 'created_at',
    sortable: true,
    align: 'left'
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'center'
  }
]

// State
const users = ref([])
const loading = ref(false)
const search = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const businessFilter = ref('all')
const branchFilter = ref('all')
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10
})
const deleteDialog = ref(false)
const userToDelete = ref(null)
const businessOptions = ref([])
const branchOptions = ref([])

// Options
const roleOptions = [
  { label: 'All Roles', value: 'all' },
  { label: 'Admin', value: 'admin' },
  { label: 'Branch Manager', value: 'branch_manager' },
  { label: 'Cashier', value: 'cashier' },
  { label: 'Inventory Clerk', value: 'inventory_clerk' },
  { label: 'Default', value: 'default' }
]

const statusOptions = [
  { label: 'All Status', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

// Methods
const fetchUsers = async () => {
  loading.value = true
  try {
    const params = {
      role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      business_id: businessFilter.value !== 'all' ? businessFilter.value : undefined,
      branch_id: branchFilter.value !== 'all' ? branchFilter.value : undefined
    }
    users.value = await userService.getUsers(params)
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

const fetchBusinesses = async () => {
  try {
    const businesses = await businessService.getBusinesses()
    businessOptions.value = [
      { label: 'All Businesses', value: 'all' },
      ...businesses.map(business => ({
        label: business.name,
        value: business.id
      }))
    ]
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
    const branches = await businessService.getBranches(businessId)
    branchOptions.value = [
      { label: 'All Branches', value: 'all' },
      ...branches.map(branch => ({
        label: branch.name,
        value: branch.id
      }))
    ]
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const formatRole = (role) => {
  const roleMap = {
    admin: 'Admin',
    branch_manager: 'Branch Manager',
    cashier: 'Cashier',
    inventory_clerk: 'Inventory Clerk',
    default: 'Default'
  }
  return roleMap[role] || role
}

const getRoleColor = (role) => {
  const colorMap = {
    admin: 'primary',
    branch_manager: 'secondary',
    cashier: 'accent',
    inventory_clerk: 'info',
    default: 'grey'
  }
  return colorMap[role] || 'grey'
}

const onViewUser = (user) => {
  router.push(`/super-admin/users/${user.id}`)
}

const onEditUser = (user) => {
  router.push(`/super-admin/users/${user.id}/edit`)
}

const onToggleStatus = async (user) => {
  try {
    await userService.toggleUserStatus(user.id)
    $q.notify({
      color: 'positive',
      message: `User ${user.is_active ? 'deactivated' : 'activated'} successfully`,
      icon: 'check_circle'
    })
    await fetchUsers()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const onDeleteUser = (user) => {
  userToDelete.value = user
  deleteDialog.value = true
}

const confirmDelete = async () => {
  try {
    await userService.deleteUser(userToDelete.value.id)
    $q.notify({
      color: 'positive',
      message: 'User deleted successfully',
      icon: 'check_circle'
    })
    await fetchUsers()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

// Watch for filter changes
watch([roleFilter, statusFilter, businessFilter, branchFilter], () => {
  fetchUsers()
})

// Watch for business selection to update branches
watch(() => businessFilter.value, (newVal) => {
  if (newVal && newVal !== 'all') {
    fetchBranches(newVal)
  } else {
    branchOptions.value = [{ label: 'All Branches', value: 'all' }]
    branchFilter.value = 'all'
  }
})

onMounted(() => {
  fetchUsers()
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
}
</style> 