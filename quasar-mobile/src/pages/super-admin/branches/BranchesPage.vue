<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Branches</div>
      <div class="row q-gutter-sm">
        <q-btn
          color="secondary"
          icon="map"
          label="Map View"
          @click="toggleMapView"
        />
        <q-btn
          color="secondary"
          icon="file_download"
          label="Export"
          @click="exportBranches"
        />
        <q-btn
          color="primary"
          icon="add"
          label="Add Branch"
          @click="openCreateDialog"
        />
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedBranches.length > 0" class="row q-mb-md">
      <q-card class="glass-card full-width">
        <q-card-section class="row items-center">
          <div class="text-subtitle1 q-mr-md">
            {{ selectedBranches.length }} branches selected
          </div>
          <q-btn-group>
            <q-btn
              color="primary"
              icon="check_circle"
              label="Activate"
              @click="bulkActivate"
              :disable="!canBulkActivate"
            />
            <q-btn
              color="negative"
              icon="block"
              label="Deactivate"
              @click="bulkDeactivate"
              :disable="!canBulkDeactivate"
            />
            <q-btn
              color="negative"
              icon="delete"
              label="Delete"
              @click="bulkDelete"
            />
          </q-btn-group>
        </q-card-section>
      </q-card>
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-4">
        <q-input
          v-model="search"
          outlined
          placeholder="Search branches..."
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
    </div>

    <!-- Branches Table -->
    <q-card class="glass-card">
      <q-table
        :rows="branches"
        :columns="columns"
        row-key="id"
        :loading="loading"
        v-model="pagination"
        :filter="search"
        binary-state-sort
        :rows-per-page-options="[10, 20, 50, 100]"
        @request="onRequest"
      >
        <!-- Selection Column -->
        <template #body-cell-selection="props">
          <q-td :props="props">
            <q-checkbox v-model="selectedBranches" :val="props.row" />
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
                @click="viewBranchDetails(props.row)"
              >
                <q-tooltip>View Details</q-tooltip>
              </q-btn>
              <q-btn
                flat
                round
                icon="edit"
                @click="onEditBranch(props.row)"
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
                @click="onDeleteBranch(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </q-btn-group>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="dialog" persistent>
      <q-card class="glass-card" style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">{{ isEditing ? 'Edit Branch' : 'Create Branch' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="form.name"
              label="Branch Name"
              :rules="[val => !!val || 'Branch name is required']"
              outlined
            />

            <q-input
              v-model="form.address"
              label="Address"
              type="textarea"
              :rules="[val => !!val || 'Address is required']"
              outlined
            />

            <q-input
              v-model="form.phone"
              label="Phone"
              :rules="[val => !!val || 'Phone is required']"
              outlined
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
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            flat
            :label="isEditing ? 'Update' : 'Create'"
            color="primary"
            @click="onSubmit"
            :loading="loading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card class="glass-card">
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this branch?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="confirmDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Branch Details Dialog -->
    <q-dialog v-model="detailsDialog" persistent>
      <q-card class="glass-card" style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Branch Details</div>
        </q-card-section>

        <q-card-section>
          <div v-if="selectedBranch" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Branch Name</q-item-label>
                    <q-item-label>{{ selectedBranch.name }}</q-item-label>
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-12 col-md-6">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Business</q-item-label>
                    <q-item-label>{{ selectedBranch.business.name }}</q-item-label>
                  </q-item-section>
                </q-item>
              </div>
            </div>
            <q-item>
              <q-item-section>
                <q-item-label caption>Address</q-item-label>
                <q-item-label>{{ selectedBranch.address }}</q-item-label>
              </q-item-section>
            </q-item>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Phone</q-item-label>
                    <q-item-label>{{ selectedBranch.phone }}</q-item-label>
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-12 col-md-6">
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Email</q-item-label>
                    <q-item-label>{{ selectedBranch.email }}</q-item-label>
                  </q-item-section>
                </q-item>
              </div>
            </div>
            <q-item>
              <q-item-section>
                <q-item-label caption>Status</q-item-label>
                <q-chip
                  :color="selectedBranch.is_active ? 'positive' : 'negative'"
                  text-color="white"
                >
                  {{ selectedBranch.is_active ? 'Active' : 'Inactive' }}
                </q-chip>
              </q-item-section>
            </q-item>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Map View Dialog -->
    <q-dialog v-model="mapDialog" persistent>
      <q-card class="glass-card" style="min-width: 800px; min-height: 600px">
        <q-card-section>
          <div class="text-h6">Branch Locations</div>
        </q-card-section>

        <q-card-section>
          <div id="map" style="height: 500px;"></div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { businessService } from '../../../services/business'
import { branchService } from '../../../services/branch'
import { exportToExcel } from '../../../utils/export'

const $q = useQuasar()
const route = useRoute()

// Get business ID from URL
const businessId = computed(() => route.params.businessId)

// Table columns
const columns = [
  {
    name: 'selection',
    label: '',
    field: 'selection',
    align: 'left',
    style: 'width: 50px'
  },
  {
    name: 'name',
    label: 'Branch Name',
    field: 'name',
    sortable: true,
    align: 'left'
  },
  {
    name: 'address',
    label: 'Address',
    field: 'address',
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
    name: 'email',
    label: 'Email',
    field: 'email',
    sortable: true,
    align: 'left'
  },
  {
    name: 'status',
    label: 'Status',
    field: 'is_active',
    sortable: true,
    align: 'center'
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'center'
  }
]

// State
const branches = ref([])
const loading = ref(false)
const search = ref('')
const statusFilter = ref('all')
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})
const dialog = ref(false)
const deleteDialog = ref(false)
const isEditing = ref(false)
const branchToDelete = ref(null)
const selectedBusiness = ref(null)

// New refs for enhanced features
const detailsDialog = ref(false)
const mapDialog = ref(false)
const selectedBranch = ref(null)
const selectedBranches = ref([])

// Form state
const form = ref({
  name: '',
  address: '',
  phone: '',
  email: ''
})

// Options
const statusOptions = [
  { label: 'All Status', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

// Computed properties for bulk actions
const canBulkActivate = computed(() => {
  return selectedBranches.value.some(branch => !branch.is_active)
})

const canBulkDeactivate = computed(() => {
  return selectedBranches.value.some(branch => branch.is_active)
})

// Methods
const isValidEmail = (val) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
  return emailPattern.test(val) || 'Invalid email'
}

const fetchBranches = async () => {
  if (!businessId.value) return
  
  loading.value = true
  try {
    const params = {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      search: search.value || undefined,
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage
    }
    console.log('Fetching branches with params:', params)
    const response = await branchService.getBranches(businessId.value, params)
    console.log('API Response:', response)
    
    // Update branches with the data array
    branches.value = response.data || []
    console.log('Branches data:', branches.value)
    
    // Update pagination with server-side pagination info
    pagination.value = {
      ...pagination.value,
      rowsNumber: response.total || response.data.length || 0,
      page: response.current_page || 1,
      rowsPerPage: response.per_page || 10
    }
    console.log('Updated pagination:', pagination.value)
  } catch (err) {
    console.error('Error fetching branches:', err)
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
    // Set empty array on error
    branches.value = []
  } finally {
    loading.value = false
  }
}

const fetchBusiness = async () => {
  if (!businessId.value) return
  
  try {
    selectedBusiness.value = await businessService.getBusiness(businessId.value)
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const openCreateDialog = () => {
  isEditing.value = false
  form.value = {
    name: '',
    address: '',
    phone: '',
    email: ''
  }
  dialog.value = true
}

const onEditBranch = (branch) => {
  isEditing.value = true
  form.value = {
    name: branch.name,
    address: branch.address,
    phone: branch.phone,
    email: branch.email
  }
  dialog.value = true
}

const onToggleStatus = async (branch) => {
  try {
    await branchService.toggleBranchStatus(businessId.value, branch.id)
    $q.notify({
      color: 'positive',
      message: `Branch ${branch.is_active ? 'deactivated' : 'activated'} successfully`,
      icon: 'check_circle'
    })
    await fetchBranches()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const onDeleteBranch = (branch) => {
  branchToDelete.value = branch
  deleteDialog.value = true
}

const confirmDelete = async () => {
  try {
    await branchService.deleteBranch(businessId.value, branchToDelete.value.id)
    $q.notify({
      color: 'positive',
      message: 'Branch deleted successfully',
      icon: 'check_circle'
    })
    await fetchBranches()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  }
}

const onSubmit = async () => {
  try {
    loading.value = true
    
    if (isEditing.value) {
      await branchService.updateBranch(
        businessId.value,
        branchToDelete.value.id,
        form.value
      )
    } else {
      await branchService.createBranch(businessId.value, form.value)
    }

    $q.notify({
      color: 'positive',
      message: `Branch ${isEditing.value ? 'updated' : 'created'} successfully`,
      icon: 'check_circle'
    })

    dialog.value = false
    await fetchBranches()
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

// Watch for filter changes
watch([statusFilter, search], () => {
  pagination.value.page = 1 // Reset to first page when filters change
  fetchBranches()
})

// Watch for business ID changes
watch(businessId, () => {
  fetchBusiness()
  fetchBranches()
})

// New methods for enhanced features
const viewBranchDetails = (branch) => {
  selectedBranch.value = branch
  detailsDialog.value = true
}

const toggleMapView = () => {
  mapDialog.value = true
  // Initialize map after dialog is shown
  setTimeout(() => {
    initMap()
  }, 100)
}

const initMap = () => {
  // Get the map container
  const mapContainer = document.getElementById('map')
  if (!mapContainer) return

  // Create map instance
  const map = new google.maps.Map(mapContainer, {
    zoom: 12,
    center: { lat: 0, lng: 0 }, // Default center
    styles: [
      {
        featureType: 'all',
        elementType: 'all',
        stylers: [
          { visibility: 'on' },
          { color: '#ffffff' }
        ]
      },
      {
        featureType: 'water',
        elementType: 'geometry',
        stylers: [
          { color: '#e9e9e9' },
          { lightness: 17 }
        ]
      }
    ]
  })

  // Create markers for each branch
  const bounds = new google.maps.LatLngBounds()
  const geocoder = new google.maps.Geocoder()

  branches.value.forEach(branch => {
    geocoder.geocode({ address: branch.address }, (results, status) => {
      if (status === 'OK' && results[0]) {
        const location = results[0].geometry.location
        const marker = new google.maps.Marker({
          position: location,
          map: map,
          title: branch.name,
          animation: google.maps.Animation.DROP
        })

        // Add info window
        const infoWindow = new google.maps.InfoWindow({
          content: `
            <div style="padding: 10px;">
              <h3 style="margin: 0 0 5px 0;">${branch.name}</h3>
              <p style="margin: 0;">${branch.address}</p>
              <p style="margin: 5px 0 0 0;">
                <strong>Phone:</strong> ${branch.phone}<br>
                <strong>Email:</strong> ${branch.email}
              </p>
            </div>
          `
        })

        marker.addListener('click', () => {
          infoWindow.open(map, marker)
        })

        bounds.extend(location)
      }
    })
  })

  // Fit map to show all markers
  setTimeout(() => {
    if (!bounds.isEmpty()) {
      map.fitBounds(bounds)
    }
  }, 1000)
}

const exportBranches = () => {
  const data = branches.value.map(branch => ({
    'Branch Name': branch.name,
    'Address': branch.address,
    'Phone': branch.phone,
    'Email': branch.email,
    'Status': branch.is_active ? 'Active' : 'Inactive'
  }))
  
  exportToExcel(data, `branches_${businessId.value}`)
}

const bulkActivate = async () => {
  try {
    await Promise.all(
      selectedBranches.value
        .filter(branch => !branch.is_active)
        .map(branch => branchService.updateBranch(businessId.value, branch.id, { is_active: true }))
    )
    await fetchBranches()
    selectedBranches.value = []
    $q.notify({
      type: 'positive',
      message: 'Selected branches activated successfully'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to activate branches'
    })
  }
}

const bulkDeactivate = async () => {
  try {
    await Promise.all(
      selectedBranches.value
        .filter(branch => branch.is_active)
        .map(branch => branchService.updateBranch(businessId.value, branch.id, { is_active: false }))
    )
    await fetchBranches()
    selectedBranches.value = []
    $q.notify({
      type: 'positive',
      message: 'Selected branches deactivated successfully'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to deactivate branches'
    })
  }
}

const bulkDelete = async () => {
  try {
    await Promise.all(
      selectedBranches.value.map(branch => branchService.deleteBranch(businessId.value, branch.id))
    )
    await fetchBranches()
    selectedBranches.value = []
    $q.notify({
      type: 'positive',
      message: 'Selected branches deleted successfully'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to delete branches'
    })
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
  
  fetchBranches()
}

onMounted(() => {
  fetchBusiness()
  fetchBranches()
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