<template>
  <q-page padding>
    <div class="row q-mb-md items-center justify-between">
      <div class="text-h5">Stock Requests</div>
      <q-btn color="primary" icon="add" label="New Request" @click="openRequestDialog()" />
    </div>

    <!-- Filters -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-select
          v-model="filters.status"
          :options="statusOptions"
          label="Status"
          emit-value
          map-options
          clearable
          @update:model-value="loadRequests"
        />
      </div>
      <div class="col-12 col-md-3">
        <q-select
          v-model="filters.branch"
          :options="branchOptions"
          label="Branch"
          emit-value
          map-options
          clearable
          @update:model-value="loadRequests"
        />
      </div>
      <div class="col-12 col-md-3">
        <q-input
          v-model="filters.search"
          label="Search"
          clearable
          @update:model-value="loadRequests"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
    </div>

    <!-- Stock Requests Table -->
    <q-table
      :rows="requests"
      :columns="columns"
      :loading="loading"
      v-model:pagination="pagination"
      row-key="id"
      @request="onRequest"
    >
      <!-- Status Column -->
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

      <!-- Actions Column -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="visibility"
            @click="viewRequest(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'pending'"
            flat
            round
            color="positive"
            icon="check"
            @click="openApprovalDialog(props.row)"
          >
            <q-tooltip>Approve</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'pending'"
            flat
            round
            color="negative"
            icon="close"
            @click="rejectRequest(props.row)"
          >
            <q-tooltip>Reject</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Request Details Dialog -->
    <q-dialog v-model="detailsDialog" persistent>
      <q-card style="min-width: 700px">
        <q-card-section class="row items-center">
          <div class="text-h6">Request Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-item>
                <q-item-section>
                  <q-item-label caption>Request ID</q-item-label>
                  <q-item-label>{{ selectedRequest.id }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
            <div class="col-12 col-md-6">
              <q-item>
                <q-item-section>
                  <q-item-label caption>Status</q-item-label>
                  <q-item-label>
                    <q-chip
                      :color="getStatusColor(selectedRequest.status)"
                      text-color="white"
                      dense
                    >
                      {{ selectedRequest.status }}
                    </q-chip>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </div>
            <div class="col-12 col-md-6">
              <q-item>
                <q-item-section>
                  <q-item-label caption>Branch</q-item-label>
                  <q-item-label>{{ selectedRequest.branch?.name }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
            <div class="col-12 col-md-6">
              <q-item>
                <q-item-section>
                  <q-item-label caption>Requested By</q-item-label>
                  <q-item-label>{{ selectedRequest.requested_by?.name }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
          </div>

          <q-table
            :rows="selectedRequest.items || []"
            :columns="itemColumns"
            row-key="id"
            dense
            class="q-mt-md"
          >
            <template v-slot:body-cell-quantity="props">
              <q-td :props="props">
                <q-chip
                  :color="props.row.quantity > props.row.available_quantity ? 'negative' : 'positive'"
                  text-color="white"
                  dense
                >
                  {{ props.row.quantity }}
                </q-chip>
              </q-td>
            </template>
          </q-table>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            v-if="selectedRequest.status === 'pending'"
            color="positive"
            label="Approve"
            @click="openApprovalDialog(selectedRequest)"
          />
          <q-btn
            v-if="selectedRequest.status === 'pending'"
            color="negative"
            label="Reject"
            @click="rejectRequest(selectedRequest)"
          />
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Reject Dialog -->
    <q-dialog v-model="rejectDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Reject Request</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="rejectReason"
            label="Reason for rejection"
            type="textarea"
            :rules="[val => !!val || 'Reason is required']"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            color="negative"
            label="Reject"
            @click="confirmReject"
            :disable="!rejectReason"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Approval Dialog -->
    <q-dialog v-model="approvalDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Approve Stock Request</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onApproveSubmit" class="q-gutter-md">
            <div v-for="(item, index) in selectedRequest?.items" :key="index" class="q-mb-md">
              <div class="text-subtitle2">{{ item.inventory.name }}</div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                  <q-radio v-model="item.action" val="merge" label="Merge with existing inventory" />
                  <q-radio v-model="item.action" val="new" label="Create new inventory item" />
                </div>
                
                <div v-if="item.action === 'merge'" class="col-12">
                  <q-select
                    v-model="item.target_inventory_id"
                    :options="getBranchInventoryOptions(item.inventory.name)"
                    label="Select existing inventory"
                    outlined
                    emit-value
                    map-options
                    :rules="[val => item.action !== 'merge' || !!val || 'Please select an inventory item']"
                  />
                </div>
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Approve" @click="onApproveSubmit" :loading="approving" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'
import { useRoute } from 'vue-router'

export default defineComponent({
  name: 'AdminStockRequests',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const requests = ref([])
    const branches = ref([])
    const selectedRequest = ref({})
    const detailsDialog = ref(false)
    const rejectDialog = ref(false)
    const rejectReason = ref('')
    const requestToReject = ref(null)
    const approvalDialog = ref(false)
    const approving = ref(false)
    const branchInventory = ref([])

    // Ensure route parameters are properly typed
    const businessId = parseInt(route.params.businessId)

    const filters = ref({
      status: null,
      branch: null,
      search: ''
    })

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Approved', value: 'approved' },
      { label: 'Rejected', value: 'rejected' },
      { label: 'Completed', value: 'completed' }
    ]

    const columns = [
      { name: 'id', label: 'Request ID', field: 'id', align: 'left' },
      { name: 'branch', label: 'Branch', field: row => row.branch?.name, align: 'left' },
      { name: 'requested_by', label: 'Requested By', field: row => row.requested_by?.name, align: 'left' },
      { name: 'status', label: 'Status', field: 'status', align: 'left' },
      { name: 'created_at', label: 'Requested Date', field: 'created_at', align: 'left' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
    ]

    const itemColumns = [
      { name: 'name', label: 'Item', field: row => row.inventory?.name || 'N/A', align: 'left' },
      { name: 'quantity_requested', label: 'Requested Quantity', field: 'quantity_requested', align: 'center' },
      { name: 'available_quantity', label: 'Available Quantity', field: 'available_quantity', align: 'center' },
      { name: 'unit', label: 'Unit', field: row => row.inventory?.unit || 'N/A', align: 'center' },
      { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
    ]

    const pagination = ref({
      sortBy: 'created_at',
      descending: true,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const loadRequests = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.value.page,
          per_page: pagination.value.rowsPerPage,
          sort_by: pagination.value.sortBy,
          descending: pagination.value.descending,
          ...filters.value
        }

        const response = await api.get(`/admin/stock-requests`, { params })
        requests.value = response.data
        pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Error loading requests:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to load stock requests'
        })
      } finally {
        loading.value = false
      }
    }

    const loadBranches = async () => {
      try {
        const response = await api.get(`/business/${businessId}/branches`)
        branches.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        console.error('Error loading branches:', error)
      }
    }

    const viewRequest = async (request) => {
      try {
        // Load full request details including inventory information
        const response = await api.get(`/admin/stock-requests/${request.id}`)
        selectedRequest.value = response.data
        detailsDialog.value = true
      } catch (error) {
        console.error('Error loading request details:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to load request details'
        })
      }
    }

    const openApprovalDialog = async (request) => {
      selectedRequest.value = {
        ...request,
        items: request.items.map(item => ({
          ...item,
          action: 'new',
          target_inventory_id: null
        }))
      }
      
      // Load branch inventory for merging options
      try {
        const response = await api.get(`/business/${businessId}/branch/${request.branch_id}/inventory`)
        branchInventory.value = response.data
      } catch (error) {
        console.error('Error loading branch inventory:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to load branch inventory'
        })
      }
      
      approvalDialog.value = true
    }

    const getBranchInventoryOptions = (name) => {
      if (!name || !branchInventory.value) return []
      
      return branchInventory.value
        .filter(item => item && item.name && item.name.toLowerCase() === name.toLowerCase())
        .map(item => ({
          label: `${item.name} (SKU: ${item.sku || 'N/A'})`,
          value: item.id
        }))
    }

    const onApproveSubmit = async () => {
      if (!selectedRequest.value) return

      approving.value = true
      try {
        // Process each item in the request
        for (const item of selectedRequest.value.items) {
          const payload = {
            action: item.action
          }
          
          // Only include target_inventory_id if action is merge
          if (item.action === 'merge' && item.target_inventory_id) {
            payload.target_inventory_id = item.target_inventory_id
          }

          const response = await api.post(`/admin/stock-requests/${selectedRequest.value.id}/approve`, payload)
          
          if (response.data.status === 'error') {
            throw new Error(response.data.message || 'Failed to approve stock request')
          }
        }

        $q.notify({
          color: 'positive',
          message: 'Stock request approved successfully'
        })

        approvalDialog.value = false
        loadRequests() // Refresh the list
      } catch (error) {
        console.error('Error approving stock request:', error)
        $q.notify({
          color: 'negative',
          message: error.response?.data?.message || error.message || 'Failed to approve stock request'
        })
      } finally {
        approving.value = false
      }
    }

    const rejectRequest = (request) => {
      requestToReject.value = request
      rejectDialog.value = true
    }

    const confirmReject = async () => {
      try {
        await api.post(`/admin/stock-requests/${requestToReject.value.id}/reject`, {
          reason: rejectReason.value
        })
        $q.notify({
          color: 'positive',
          message: 'Request rejected successfully'
        })
        loadRequests()
        rejectDialog.value = false
        detailsDialog.value = false
        rejectReason.value = ''
      } catch (error) {
        console.error('Error rejecting request:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to reject request'
        })
      }
    }

    const getStatusColor = (status) => {
      const colors = {
        pending: 'warning',
        approved: 'positive',
        rejected: 'negative',
        completed: 'info'
      }
      return colors[status] || 'grey'
    }

    const onRequest = (props) => {
      const { page, rowsPerPage, sortBy, descending } = props.pagination
      pagination.value = { page, rowsPerPage, sortBy, descending }
      loadRequests()
    }

    onMounted(() => {
      loadRequests()
      loadBranches()
    })

    return {
      loading,
      requests,
      columns,
      itemColumns,
      pagination,
      filters,
      statusOptions,
      branchOptions: branches,
      selectedRequest,
      detailsDialog,
      rejectDialog,
      rejectReason,
      viewRequest,
      openApprovalDialog,
      rejectRequest,
      confirmReject,
      getStatusColor,
      onRequest,
      loadRequests,
      approvalDialog,
      approving,
      branchInventory,
      getBranchInventoryOptions,
      onApproveSubmit
    }
  }
})
</script>

<style scoped>
.q-table__card {
  box-shadow: none;
  border: 1px solid #ddd;
}
</style>
