<template>
  <q-page padding>
    <div class="row q-mb-md items-center justify-between">
      <div class="text-h5">Stock Requests</div>
      <q-btn
        color="primary"
        icon="add"
        label="New Request"
        @click="openRequestDialog"
      />
    </div>

    <q-table
      :rows="stockRequests"
      :columns="columns"
      :loading="loading"
      v-model:pagination="pagination"
      row-key="id"
      @request="onRequest"
    >
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="getStatusColor(props.row.status)"
            text-color="white"
            dense
          >
            {{ props.row.status }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="visibility"
            size="sm"
            @click="viewRequestDetails(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'pending'"
            flat
            round
            color="negative"
            icon="cancel"
            size="sm"
            @click="cancelRequest(props.row.id)"
          >
            <q-tooltip>Cancel Request</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Request Dialog -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">New Stock Request</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-select
              v-model="form.warehouse_id"
              :options="warehouses"
              label="Warehouse"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Warehouse is required']"
            />

            <div class="text-subtitle2 q-mb-sm">Request Items</div>
            <div v-for="(item, index) in form.items" :key="index" class="row q-col-gutter-md">
              <div class="col-6">
                <q-select
                  v-model="item.inventory_id"
                  :options="inventoryItems"
                  label="Item"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Item is required']"
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model.number="item.quantity_requested"
                  label="Quantity"
                  type="number"
                  outlined
                  :rules="[val => val > 0 || 'Quantity must be greater than 0']"
                />
              </div>
              <div class="col-2">
                <q-btn
                  flat
                  round
                  color="negative"
                  icon="delete"
                  @click="removeItem(index)"
                />
              </div>
            </div>

            <q-btn
              flat
              color="primary"
              icon="add"
              label="Add Item"
              @click="addItem"
            />

            <q-input
              v-model="form.notes"
              label="Notes"
              type="textarea"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Submit" color="primary" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Details Dialog -->
    <q-dialog v-model="showDetailsDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Request Details</div>
        </q-card-section>

        <q-card-section>
          <div v-if="selectedRequest">
            <div class="row q-col-gutter-md">
              <div class="col-6">
                <div class="text-subtitle2">Status</div>
                <q-chip
                  :color="getStatusColor(selectedRequest.status)"
                  text-color="white"
                >
                  {{ selectedRequest.status }}
                </q-chip>
              </div>
              <div class="col-6">
                <div class="text-subtitle2">Warehouse</div>
                <div>{{ selectedRequest.warehouse?.name }}</div>
              </div>
            </div>

            <div class="q-mt-md">
              <div class="text-subtitle2">Requested Items</div>
              <q-table
                :rows="selectedRequest.items"
                :columns="itemColumns"
                dense
                flat
              >
                <template v-slot:body-cell-quantity="props">
                  <q-td :props="props">
                    <div>Requested: {{ props.row.quantity_requested }}</div>
                    <div v-if="props.row.quantity_approved">
                      Approved: {{ props.row.quantity_approved }}
                    </div>
                    <div v-if="props.row.quantity_fulfilled">
                      Fulfilled: {{ props.row.quantity_fulfilled }}
                    </div>
                  </q-td>
                </template>
              </q-table>
            </div>

            <div v-if="selectedRequest.notes" class="q-mt-md">
              <div class="text-subtitle2">Notes</div>
              <div>{{ selectedRequest.notes }}</div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useBranchManagerStore } from 'stores/branchManager'

export default {
  name: 'StockRequestsPage',
  setup() {
    const $q = useQuasar()
    const route = useRoute()
    const store = useBranchManagerStore()
    const loading = ref(false)
    const showDialog = ref(false)
    const showDetailsDialog = ref(false)
    const selectedRequest = ref(null)

    const pagination = ref({
      sortBy: 'created_at',
      descending: true,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const form = ref({
      warehouse_id: null,
      items: [{ inventory_id: null, quantity_requested: 1 }],
      notes: ''
    })

    const columns = [
      {
        name: 'id',
        label: 'ID',
        field: 'id',
        sortable: true,
        align: 'left'
      },
      {
        name: 'warehouse',
        label: 'Warehouse',
        field: row => row.warehouse?.name,
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
        name: 'requested_by',
        label: 'Requested By',
        field: row => row.requested_by?.name,
        sortable: true,
        align: 'left'
      },
      {
        name: 'created_at',
        label: 'Requested At',
        field: 'created_at',
        sortable: true,
        align: 'left',
        format: val => new Date(val).toLocaleString()
      },
      {
        name: 'actions',
        label: 'Actions',
        field: 'actions',
        align: 'center'
      }
    ]

    const itemColumns = [
      {
        name: 'inventory',
        label: 'Item',
        field: row => row.inventory?.name,
        align: 'left'
      },
      {
        name: 'quantity',
        label: 'Quantity',
        field: 'quantity_requested',
        align: 'center'
      },
      {
        name: 'notes',
        label: 'Notes',
        field: 'notes',
        align: 'left'
      }
    ]

    const stockRequests = computed(() => {
      if (!store.stockRequests) return []
      return Array.isArray(store.stockRequests.data) ? store.stockRequests.data : []
    })

    const warehouses = computed(() => {
      if (!store.warehouses) return []
      // Handle single warehouse case
      if (!Array.isArray(store.warehouses)) {
        return store.warehouses.data ? [
          {
            label: store.warehouses.data.name,
            value: store.warehouses.data.id
          }
        ] : []
      }
      // Handle multiple warehouses case
      return store.warehouses.map(warehouse => ({
        label: warehouse.name,
        value: warehouse.id
      }))
    })

    const inventoryItems = ref([])

    const loadWarehouseInventory = async (warehouseId) => {
      try {
        const { businessId, branchId } = route.params
        const inventory = await store.fetchWarehouseInventory(businessId, branchId, warehouseId)
        inventoryItems.value = inventory.map(item => ({
          label: item.name,
          value: item.id
        }))
      } catch (error) {
        console.error('Error loading warehouse inventory:', error)
        $q.notify({
          type: 'negative',
          message: 'Failed to load warehouse inventory'
        })
      }
    }

    const loadData = async () => {
      loading.value = true
      try {
        const { businessId, branchId } = route.params
        await Promise.all([
          store.fetchStockRequests(businessId, branchId, {
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }),
          store.fetchWarehouses(businessId, branchId)
        ])
        
        if (store.stockRequests) {
          pagination.value.rowsNumber = store.stockRequests.total
          pagination.value.page = store.stockRequests.current_page
          pagination.value.rowsPerPage = store.stockRequests.per_page
        }
      } catch (error) {
        console.error('Error loading data:', error)
        $q.notify({
          type: 'negative',
          message: 'Failed to load data'
        })
      } finally {
        loading.value = false
      }
    }

    // Watch for warehouse selection to load its inventory
    watch(() => form.value.warehouse_id, async (newWarehouseId) => {
      if (newWarehouseId) {
        await loadWarehouseInventory(newWarehouseId)
      } else {
        inventoryItems.value = []
      }
    })

    const onRequest = (props) => {
      pagination.value = props.pagination
      loadData()
    }

    const openRequestDialog = () => {
      form.value = {
        warehouse_id: null,
        items: [{ inventory_id: null, quantity_requested: 1 }],
        notes: ''
      }
      nextTick(() => {
        showDialog.value = true
      })
    }

    const addItem = () => {
      form.value.items.push({
        inventory_id: null,
        quantity_requested: 1
      })
    }

    const removeItem = (index) => {
      form.value.items.splice(index, 1)
    }

    const onSubmit = async () => {
      try {
        const { businessId, branchId } = route.params
        await store.createStockRequest(businessId, branchId, form.value)
        $q.notify({
          type: 'positive',
          message: 'Stock request created successfully'
        })
        showDialog.value = false
        loadData()
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to create stock request'
        })
      }
    }

    const viewRequestDetails = async (request) => {
      try {
        const { businessId, branchId } = route.params
        const details = await store.getStockRequestDetails(businessId, branchId, request.id)
        selectedRequest.value = details
        showDetailsDialog.value = true
      } catch {
        $q.notify({
          type: 'negative',
          message: 'Failed to load request details'
        })
      }
    }

    const cancelRequest = async (id) => {
      try {
        await $q.dialog({
          title: 'Confirm',
          message: 'Are you sure you want to cancel this request?',
          cancel: true,
          persistent: true
        })

        const { businessId, branchId } = route.params
        await store.cancelStockRequest(businessId, branchId, id)
        $q.notify({
          type: 'positive',
          message: 'Request cancelled successfully'
        })
        loadData()
      } catch {
        $q.notify({
          type: 'negative',
          message: 'Failed to cancel request'
        })
      }
    }

    const getStatusColor = (status) => {
      const colors = {
        pending: 'warning',
        approved: 'positive',
        rejected: 'negative',
        fulfilled: 'info'
      }
      return colors[status] || 'grey'
    }

    onMounted(() => {
      loadData()
    })

    return {
      loading,
      showDialog,
      showDetailsDialog,
      selectedRequest,
      form,
      columns,
      itemColumns,
      pagination,
      warehouses,
      inventoryItems,
      stockRequests,
      onRequest,
      openRequestDialog,
      addItem,
      removeItem,
      onSubmit,
      viewRequestDetails,
      cancelRequest,
      getStatusColor
    }
  }
}
</script>
