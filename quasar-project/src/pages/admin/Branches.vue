<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Branches</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Branch"
        @click="openBranchDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search branches..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.business"
          :options="businesses"
          dense
          outlined
          label="Filter by Business"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.status"
          :options="statusOptions"
          dense
          outlined
          label="Status"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
    </div>

    <!-- Branches Table -->
    <q-table
      :rows="branches"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.is_active ? 'positive' : 'negative'"
            text-color="white"
            dense
          >
            {{ props.row.is_active ? 'Active' : 'Inactive' }}
          </q-chip>
        </q-td>
      </template>

      <!-- Staff Column -->
      <template v-slot:body-cell-staff_count="props">
        <q-td :props="props">
          <q-btn
            flat
            dense
            class="text-primary"
            :label="props.row.staff?.length || 0"
            @click="viewStaff(props.row)"
          >
            <q-tooltip>View Staff</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <!-- Actions Column -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="edit"
            @click="openBranchDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="inventory_2"
            @click="viewInventory(props.row)"
          >
            <q-tooltip>View Inventory</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="negative"
            icon="delete"
            @click="confirmDelete(props.row)"
          >
            <q-tooltip>Delete</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Branch Dialog -->
    <q-dialog v-model="branchDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingBranch.id ? 'Edit' : 'Add' }} Branch</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingBranch.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingBranch.address"
              label="Address"
              :rules="[val => !!val || 'Address is required']"
              outlined
            />

            <q-input
              v-model="editingBranch.phone"
              label="Phone"
              :rules="[val => !!val || 'Phone is required']"
              outlined
            />

            <q-input
              v-model="editingBranch.email"
              label="Email"
              type="email"
              :rules="[
                val => !!val || 'Email is required',
                val => isValidEmail(val) || 'Invalid email format'
              ]"
              outlined
            />

            <q-select
              v-model="editingBranch.business_id"
              :options="businesses"
              label="Business"
              :rules="[val => !!val || 'Business is required']"
              outlined
              emit-value
              map-options
            />

            <q-toggle
              v-model="editingBranch.is_active"
              label="Active"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this branch?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="onDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useRouter, useRoute } from 'vue-router'

export default {
  name: 'BranchesPage',

  setup () {
    const $q = useQuasar()
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const branches = ref([])
    const businesses = ref([])
    const branchDialog = ref(false)
    const deleteDialog = ref(false)
    const businessIDfromdata = null
    const editingBranch = ref({
      name: '',
      address: '',
      phone: '',
      email: '',
      business_id: null,
      is_active: true
    })
    const branchToDelete = ref(null)

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'business', label: 'Business', field: row => row.business?.name, sortable: true },
      { name: 'address', label: 'Address', field: 'address' },
      { name: 'phone', label: 'Phone', field: 'phone' },
      { name: 'email', label: 'Email', field: 'email' },
      { 
        name: 'staff_count', 
        label: 'Staff', 
        field: row => row.business?.staff?.filter(staff => staff.branch_id === row.id).length || 0, 
        sortable: true 
      },
      { name: 'inventory_count', label: 'Inventory', field: row => row.inventory?.length || 0, sortable: true },
      { name: 'sales_count', label: 'Sales', field: row => row.sales?.length || 0, sortable: true },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const statusOptions = [
      { label: 'All', value: null },
      { label: 'Active', value: true },
      { label: 'Inactive', value: false }
    ]

    const filter = ref({
      search: '',
      business: null,
      status: null
    })

    const pagination = ref({
      sortBy: 'name',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const fetchBranches = async () => {
      loading.value = true
      try {
        const response = await api.get(`/business/${route.params.businessId}/branches`, {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        branches.value = response.data
        pagination.value.rowsNumber = response.data.length
      } catch (error) {
        console.error('Error fetching branches:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch branches',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchBusinesses = async () => {
      try {
        const response = await api.get(`/businesses/${route.params.businessId}`)
        const business = response.data;

        businesses.value = [{
          label: business.name,
          value: business.id
        }];
      } catch (error) {
        console.error('Error fetching businesses:', error)
      }
    }

    const openBranchDialog = (branch = null) => {
      if (branch) {
        editingBranch.value = { ...branch }
      } else {
        editingBranch.value = {
          name: '',
          address: '',
          phone: '',
          email: '',
          business_id: null,
          is_active: true
        }
      }
      branchDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingBranch.value.id) {
          await api.put(`/branches/${editingBranch.value.id}`, editingBranch.value)
        } else {
          await api.post('/branches', editingBranch.value)
        }
        branchDialog.value = false
        fetchBranches()
        $q.notify({
          color: 'positive',
          message: `Branch ${editingBranch.value.id ? 'updated' : 'created'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving branch:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save branch',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (branch) => {
      branchToDelete.value = branch
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/branches/${branchToDelete.value.id}`)
        fetchBranches()
        $q.notify({
          color: 'positive',
          message: 'Branch deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting branch:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete branch',
          icon: 'error'
        })
      }
    }

    const viewInventory = (branch) => {
      router.push(`/business/${route.params.businessId}/branch/${branch.id}/inventory`)
    }

    const viewStaff = (branch) => {
      router.push(`/business/${route.params.businessId}/branch/${branch.id}/staff`)
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchBranches()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchBranches()
    }

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    onMounted(() => {
      fetchBranches()
      fetchBusinesses()
    })

    return {
      loading,
      branches,
      businesses,
      columns,
      statusOptions,
      filter,
      pagination,
      branchDialog,
      deleteDialog,
      editingBranch,
      openBranchDialog,
      onSubmit,
      confirmDelete,
      onDelete,
      viewInventory,
      viewStaff,
      onRequest,
      onSearch,
      isValidEmail
    }
  }
}
</script> 