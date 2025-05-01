<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Businesses</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Business"
        to="/super-admin/businesses/create"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-4">
        <q-input
          v-model="search"
          outlined
          placeholder="Search businesses..."
          class="glass-input"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
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
      <div class="col-12 col-md-3">
        <q-select
          v-model="sortBy"
          :options="sortOptions"
          outlined
          label="Sort By"
          class="glass-input"
        />
      </div>
    </div>

    <!-- Businesses Table -->
    <q-card class="glass-card">
      <q-table
        :rows="businesses"
        :columns="columns"
        row-key="id"
        :loading="loading"
        v-model="pagination"
        :filter="search"
        binary-state-sort
        :rows-per-page-options="[10, 20, 50, 100]"
        @request="onRequest"
      >
        <!-- Logo Column -->
        <template #body-cell-logo="props">
          <q-td :props="props">
            <q-avatar size="40px">
              <img :src="props.row.logo" v-if="props.row.logo">
              <q-icon name="business" v-else />
            </q-avatar>
          </q-td>
        </template>

        <!-- Status Column -->
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              :color="props.row.status === 'active' ? 'positive' : 'negative'"
              text-color="white"
              size="sm"
            >
              {{ props.row.status }}
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
                @click="onViewBusiness(props.row)"
              >
                <q-tooltip>View Details</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="edit"
                @click="onEditBusiness(props.row)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                :icon="props.row.status === 'active' ? 'block' : 'check_circle'"
                @click="onToggleStatus(props.row)"
              >
                <q-tooltip>{{ props.row.status === 'active' ? 'Deactivate' : 'Activate' }}</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="delete"
                @click="onDeleteBusiness(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="store"
                @click="onViewBranches(props.row)"
              >
                <q-tooltip>View Branches</q-tooltip>
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
          <span class="q-ml-sm">Are you sure you want to delete this business?</span>
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
import { businessService } from '../../../services/business'

const $q = useQuasar()
const router = useRouter()

// Table columns
const columns = [
  {
    name: 'logo',
    label: '',
    field: 'logo',
    align: 'center',
    style: 'width: 50px'
  },
  {
    name: 'name',
    label: 'Business Name',
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
    name: 'phone',
    label: 'Phone',
    field: 'phone',
    sortable: true,
    align: 'left'
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
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
const businesses = ref([])
const loading = ref(false)
const search = ref('')
const statusFilter = ref('all')
const sortBy = ref('name')
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})
const deleteDialog = ref(false)
const businessToDelete = ref(null)

// Options
const statusOptions = [
  { label: 'All', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

const sortOptions = [
  { label: 'Name', value: 'name' },
  { label: 'Email', value: 'email' },
  { label: 'Status', value: 'status' },
  { label: 'Created At', value: 'created_at' }
]

// Methods
const fetchBusinesses = async () => {
  loading.value = true
  try {
    const params = {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      sort: sortBy.value,
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage
    }
    console.log('Fetching businesses with params:', params)
    const response = await businessService.getBusinesses(params)
    console.log('API Response:', response)
    
    // Update businesses with the data array
    businesses.value = response.data || []
    console.log('Businesses data:', businesses.value)
    
    // Update pagination with server-side pagination info
    pagination.value = {
      ...pagination.value,
      rowsNumber: response.total || response.data.length || 0,
      page: response.current_page || 1,
      rowsPerPage: response.per_page || 10
    }
    console.log('Updated pagination:', pagination.value)
  } catch (err) {
    console.error('Error fetching businesses:', err)
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
    // Set empty array on error
    businesses.value = []
  } finally {
    loading.value = false
  }
}

const onRequest = (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  
  pagination.value = {
    ...pagination.value,
    page,
    rowsPerPage,
    sortBy,
    descending
  }
  
  fetchBusinesses()
}

const onViewBusiness = (business) => {
  router.push(`/super-admin/businesses/${business.id}`)
}

const onEditBusiness = (business) => {
  router.push(`/super-admin/businesses/${business.id}/edit`)
}

const onToggleStatus = async (business) => {
  try {
    await businessService.toggleBusinessStatus(business.id)
    $q.notify({
      color: 'positive',
      message: `Business ${business.status === 'active' ? 'deactivated' : 'activated'} successfully`,
      icon: 'check_circle'
    })
    await fetchBusinesses()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const onDeleteBusiness = (business) => {
  businessToDelete.value = business
  deleteDialog.value = true
}

const confirmDelete = async () => {
  try {
    await businessService.deleteBusiness(businessToDelete.value.id)
    $q.notify({
      color: 'positive',
      message: 'Business deleted successfully',
      icon: 'check_circle'
    })
    await fetchBusinesses()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const onViewBranches = (business) => {
  router.push(`/super-admin/businesses/${business.id}/branches`)
}

// Watch for filter changes
watch([statusFilter, sortBy], () => {
  pagination.value.page = 1 // Reset to first page when filters change
  fetchBusinesses()
})

onMounted(() => {
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