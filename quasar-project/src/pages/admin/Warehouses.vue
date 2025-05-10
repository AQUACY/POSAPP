<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Warehouses</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Warehouse"
        @click="openWarehouseDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search warehouses..."
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

    <!-- Warehouses Table -->
    <q-table
      :rows="warehouses"
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

      <!-- Stock Requests Column -->
      <template v-slot:body-cell-stock_request_count="props">
        <q-td :props="props">
          <q-btn
            flat
            dense
            class="text-primary"
            :label="props.row.stock_requests_count || 0"
            @click="viewStockRequests(props.row)"
          >
            <q-tooltip>View Stock Requests</q-tooltip>
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
            icon="requests"
            @click="openWarehouseDialog(props.row)"
          >
          <q-tooltip>Stock Requests</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="primary"
            icon="edit"
            @click="openWarehouseDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
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

    <!-- Warehouse Dialog -->
    <q-dialog v-model="warehouseDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingWarehouse.id ? 'Edit' : 'Add' }} Warehouse</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingWarehouse.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingWarehouse.address"
              label="Address"
              :rules="[val => !!val || 'Address is required']"
              outlined
            />

            <q-input
              v-model="editingWarehouse.phone"
              label="Phone"
              :rules="[val => !!val || 'Phone is required']"
              outlined
            />

            <q-input
              v-model="editingWarehouse.email"
              label="Email"
              type="email"
              :rules="[
                val => !!val || 'Email is required',
                val => isValidEmail(val) || 'Invalid email format'
              ]"
              outlined
            />

            <q-select
              v-model="editingWarehouse.business_id"
              :options="businesses"
              label="Business"
              :rules="[val => !!val || 'Business is required']"
              outlined
              emit-value
              map-options
            />

            <q-toggle
              v-model="editingWarehouse.is_active"
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
          <span class="q-ml-sm">Are you sure you want to delete this warehouse?</span>
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
  name: 'WarehousesPage',

  setup () {
    const $q = useQuasar()
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const warehouses = ref([])
    const businesses = ref([])
    const warehouseDialog = ref(false)
    const deleteDialog = ref(false)
    const editingWarehouse = ref({
      name: '',
      address: '',
      phone: '',
      email: '',
      business_id: null,
      is_active: true
    })
    const warehouseToDelete = ref(null)

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'business', label: 'Business', field: row => row.business?.name, sortable: true },
      { name: 'address', label: 'Address', field: 'address' },
      { name: 'phone', label: 'Phone', field: 'phone' },
      { name: 'email', label: 'Email', field: 'email' },
      { name: 'status', label: 'Status', field: 'is_active' },
      { name: 'stock_request_count', label: 'Stock Requests', field: 'stock_request_count' },
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

    const fetchWarehouses = async () => {
      loading.value = true
      try {
        const response = await api.get('/warehouses', {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        warehouses.value = Array.isArray(response.data) ? response.data : response.data.data || []
        pagination.value.rowsNumber = Array.isArray(response.data) ? response.data.length : response.data.total || 0
      } catch (error) {
        console.error('Error fetching warehouses:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch warehouses',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }
    const viewStockRequests = (warehouse) => {
      router.push(`/business/${route.params.businessId}/warehouse/${warehouse.id}/stock-requests`)
    }
    const fetchBusinesses = async () => {
      try {
        const response = await api.get('/businesses')
        businesses.value = response.data.map(business => ({
          label: business.name,
          value: business.id
        }))
      } catch (error) {
        console.error('Error fetching businesses:', error)
      }
    }

    const openWarehouseDialog = (warehouse = null) => {
      if (warehouse) {
        editingWarehouse.value = { ...warehouse }
      } else {
        editingWarehouse.value = {
          name: '',
          address: '',
          phone: '',
          email: '',
          business_id: null,
          is_active: true
        }
      }
      warehouseDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingWarehouse.value.id) {
          await api.put(`/warehouses/${editingWarehouse.value.id}`, editingWarehouse.value)
        } else {
          await api.post('/warehouses', editingWarehouse.value)
        }
        warehouseDialog.value = false
        fetchWarehouses()
        $q.notify({
          color: 'positive',
          message: `Warehouse ${editingWarehouse.value.id ? 'updated' : 'created'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving warehouse:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save warehouse',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (warehouse) => {
      warehouseToDelete.value = warehouse
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/warehouses/${warehouseToDelete.value.id}`)
        fetchWarehouses()
        $q.notify({
          color: 'positive',
          message: 'Warehouse deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting warehouse:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete warehouse',
          icon: 'error'
        })
      }
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchWarehouses()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchWarehouses()
    }

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    onMounted(() => {
      fetchWarehouses()
      fetchBusinesses()
    })

    return {
      loading,
      warehouses,
      businesses,
      columns,
      statusOptions,
      filter,
      pagination,
      warehouseDialog,
      deleteDialog,
      editingWarehouse,
      openWarehouseDialog,
      onSubmit,
      confirmDelete,
      onDelete,
      onRequest,
      viewStockRequests,
      onSearch,
      isValidEmail
    }
  }
}
</script>
