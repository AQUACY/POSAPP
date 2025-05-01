<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">Refund History</div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="search"
          dense
          outlined
          placeholder="Search by sale number or customer..."
          class="q-mr-sm"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="selectedStatus"
          :options="statusOptions"
          dense
          outlined
          label="Status"
          emit-value
          map-options
        />
      </div>
    </div>

    <!-- Refund History Table -->
    <q-table
      :rows="filteredRefunds"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :pagination.sync="pagination"
      :rows-per-page-options="[10, 20, 50]"
    >
      <!-- Sale Number Column -->
      <template v-slot:body-cell-sale_number="props">
        <q-td :props="props">
          {{ props.row.sale?.sale_number }}
        </q-td>
      </template>

      <!-- Amount Column -->
      <template v-slot:body-cell-total_amount="props">
        <q-td :props="props">
          ${{ Number(props.row.total_amount).toFixed(2) }}
        </q-td>
      </template>

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
            dense
            color="primary"
            icon="visibility"
            @click="viewRefund(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            v-if="isManager && props.row.status === 'pending'"
            flat
            dense
            color="positive"
            icon="check"
            @click="approveRefund(props.row)"
          >
            <q-tooltip>Approve</q-tooltip>
          </q-btn>
          <q-btn
            v-if="isManager && props.row.status === 'pending'"
            flat
            dense
            color="negative"
            icon="close"
            @click="rejectRefund(props.row)"
          >
            <q-tooltip>Reject</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Refund Details Dialog -->
    <q-dialog v-model="showRefundDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Refund Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedRefund">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <div class="text-subtitle1">Sale #{{ selectedRefund.sale?.sale_number }}</div>
              <div class="text-caption">
                {{ new Date(selectedRefund.created_at).toLocaleString() }}
              </div>
            </div>

            <div class="col-12">
              <q-list bordered>
                <q-item v-for="item in selectedRefund.items" :key="item.id">
                  <q-item-section>
                    <q-item-label>{{ item.inventory.name }}</q-item-label>
                    <q-item-label caption>
                      {{ item.quantity }} x ${{ item.unit_price }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    ${{ item.total_amount }}
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="col-12">
              <div class="row justify-between text-h6">
                <div>Total Refund:</div>
                <div>${{ Number(selectedRefund.total_amount).toFixed(2) }}</div>
              </div>
            </div>

            <div class="col-12">
              <div class="text-subtitle2">Reason for Refund:</div>
              <div class="text-body2">{{ selectedRefund.reason }}</div>
            </div>

            <div v-if="selectedRefund.status === 'rejected'" class="col-12">
              <div class="text-subtitle2 text-negative">Rejection Reason:</div>
              <div class="text-body2">{{ selectedRefund.rejection_reason }}</div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Approve/Reject Dialog -->
    <q-dialog v-model="showApprovalDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ isApproving ? 'Approve' : 'Reject' }} Refund</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-input
            v-if="!isApproving"
            v-model="rejectionReason"
            type="textarea"
            label="Reason for Rejection"
            :rules="[val => !!val || 'Reason is required']"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            :color="isApproving ? 'positive' : 'negative'"
            :label="isApproving ? 'Approve' : 'Reject'"
            @click="submitApproval"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { posService } from '../../services/pos.service'

const $q = useQuasar()

// State
const refunds = ref([])
const loading = ref(false)
const search = ref('')
const selectedStatus = ref(null)
const showRefundDialog = ref(false)
const showApprovalDialog = ref(false)
const selectedRefund = ref(null)
const isApproving = ref(true)
const rejectionReason = ref('')
const isManager = ref(false)

const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10
})

// Table columns
const columns = [
  {
    name: 'sale_number',
    label: 'Sale #',
    field: row => row.sale?.sale_number,
    sortable: true
  },
  {
    name: 'total_amount',
    label: 'Amount',
    field: 'total_amount',
    sortable: true
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
    sortable: true
  },
  {
    name: 'created_at',
    label: 'Date',
    field: 'created_at',
    format: val => new Date(val).toLocaleString(),
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'right'
  }
]

const statusOptions = [
  { label: 'All', value: null },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' }
]

// Computed
const filteredRefunds = computed(() => {
  return refunds.value.filter(refund => {
    const matchesSearch = search.value === '' ||
      refund.sale?.sale_number.toLowerCase().includes(search.value.toLowerCase())

    const matchesStatus = !selectedStatus.value || refund.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

// Methods
const loadRefunds = async () => {
  try {
    loading.value = true
    const response = await posService.getRefunds()
    refunds.value = response.data?.data || []
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load refunds',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const viewRefund = (refund) => {
  selectedRefund.value = refund
  showRefundDialog.value = true
}

const approveRefund = (refund) => {
  selectedRefund.value = refund
  isApproving.value = true
  showApprovalDialog.value = true
}

const rejectRefund = (refund) => {
  selectedRefund.value = refund
  isApproving.value = false
  rejectionReason.value = ''
  showApprovalDialog.value = true
}

const submitApproval = async () => {
  if (!isApproving.value && !rejectionReason.value) {
    $q.notify({
      color: 'negative',
      message: 'Please provide a reason for rejection',
      icon: 'error'
    })
    return
  }

  try {
    loading.value = true
    await posService.approveRefund(selectedRefund.value.id, {
      approved: isApproving.value,
      rejection_reason: rejectionReason.value
    })

    $q.notify({
      color: 'positive',
      message: isApproving.value ? 'Refund approved' : 'Refund rejected',
      icon: 'check'
    })

    await loadRefunds()
    showApprovalDialog.value = false
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to process refund approval',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'pending':
      return 'warning'
    case 'approved':
      return 'positive'
    case 'rejected':
      return 'negative'
    default:
      return 'grey'
  }
}

// Lifecycle
onMounted(async () => {
  await loadRefunds()
  // Check user role from auth store
  const userRole = localStorage.getItem('userRole')
  isManager.value = ['branch_manager', 'admin'].includes(userRole)
})
</script> 