<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Refunds</div>
      <div class="row q-gutter-sm">
        <q-btn
          color="primary"
          icon="print"
          label="Print Report"
          @click="printReport"
        />
        <q-btn
          color="secondary"
          icon="download"
          label="Export"
          @click="exportReport"
        />
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search refunds..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-3">
        <q-select
          v-model="filter.branch"
          :options="branches"
          dense
          outlined
          label="Filter by Branch"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
      <div class="col-12 col-md-3">
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
      <div class="col-12 col-md-3">
        <q-input
          v-model="filter.date_range"
          dense
          outlined
          label="Date Range"
          type="daterange"
          @update:model-value="onSearch"
        />
      </div>
    </div>

    <!-- Refunds Summary -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Refunds</div>
            <div class="text-h4">${{ totalRefunds.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-negative text-white">
          <q-card-section>
            <div class="text-h6">Total Transactions</div>
            <div class="text-h4">{{ totalTransactions }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-warning text-white">
          <q-card-section>
            <div class="text-h6">Average Refund</div>
            <div class="text-h4">${{ averageRefund.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">Items Refunded</div>
            <div class="text-h4">{{ totalItems }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Refunds Table -->
    <q-table
      :rows="refunds"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :pagination.sync="pagination"
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

      <!-- Amount Column -->
      <template v-slot:body-cell-amount="props">
        <q-td :props="props">
          ${{ props.row.amount.toFixed(2) }}
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
            @click="viewRefund(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="receipt"
            @click="printReceipt(props.row)"
          >
            <q-tooltip>Print Receipt</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Refund Details Dialog -->
    <q-dialog v-model="refundDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Refund Details</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Refund ID</div>
              <div>{{ selectedRefund?.id }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Date</div>
              <div>{{ new Date(selectedRefund?.created_at).toLocaleString() }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Sale Number</div>
              <div>{{ selectedRefund?.sale?.sale_number }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Branch</div>
              <div>{{ selectedRefund?.branch?.name }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Processed By</div>
              <div>{{ selectedRefund?.user?.name }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Status</div>
              <div>
                <q-chip
                  :color="getStatusColor(selectedRefund?.status)"
                  text-color="white"
                  dense
                >
                  {{ selectedRefund?.status }}
                </q-chip>
              </div>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Refunded Items</div>
              <q-table
                :rows="selectedRefund?.items || []"
                :columns="itemColumns"
                row-key="id"
                dense
                flat
              />
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Refund Details</div>
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                  <div>Total Amount: ${{ Number(selectedRefund?.total_amount).toFixed(2) }}</div>
                  <div>Reason: {{ selectedRefund?.reason }}</div>
                </div>
                <div v-if="selectedRefund?.rejection_reason" class="col-12 col-md-6">
                  <div class="text-negative">Rejection Reason:</div>
                  <div>{{ selectedRefund?.rejection_reason }}</div>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Close" v-close-popup />
          <q-btn
            v-if="selectedRefund?.status === 'pending'"
            flat
            label="Approve"
            color="positive"
            @click="approveRefund(selectedRefund)"
          />
          <q-btn
            v-if="selectedRefund?.status === 'pending'"
            flat
            label="Reject"
            color="negative"
            @click="rejectRefund(selectedRefund)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Rejection Reason Dialog -->
    <q-dialog v-model="showRejectionDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Reject Refund</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="rejectionReason"
            type="textarea"
            label="Reason for Rejection"
            :rules="[val => !!val || 'Reason is required']"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            color="negative"
            label="Reject"
            @click="submitRejection"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
// import { format } from 'date-fns'

export default {
  name: 'RefundsPage',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const refunds = ref([])
    const branches = ref([])
    const refundDialog = ref(false)
    const selectedRefund = ref(null)
    const showRejectionDialog = ref(false)
    const rejectionReason = ref('')
    const selectedRefundForRejection = ref(null)

    const columns = [
      { name: 'id', label: 'Refund ID', field: 'id', sortable: true },
      { name: 'sale_number', label: 'Sale #', field: row => row.sale?.sale_number, sortable: true },
      { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
      { name: 'processed_by', label: 'Processed By', field: row => row.user?.name, sortable: true },
      { name: 'total_amount', label: 'Amount', field: 'total_amount', sortable: true, format: val => `$${Number(val).toFixed(2)}` },
      { name: 'status', label: 'Status', field: 'status', sortable: true },
      { name: 'created_at', label: 'Date', field: 'created_at', sortable: true, format: val => new Date(val).toLocaleString() },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const itemColumns = [
      { name: 'name', label: 'Item', field: row => row.inventory?.name },
      { name: 'quantity', label: 'Quantity', field: 'quantity' },
      { name: 'unit_price', label: 'Unit Price', field: 'unit_price', format: val => `$${Number(val).toFixed(2)}` },
      { name: 'total_amount', label: 'Total', field: 'total_amount', format: val => `$${Number(val).toFixed(2)}` }
    ]

    const statusOptions = [
      { label: 'All', value: null },
      { label: 'Pending', value: 'pending' },
      { label: 'Approved', value: 'approved' },
      { label: 'Rejected', value: 'rejected' }
    ]

    const filter = ref({
      search: '',
      branch: null,
      status: null,
      date_range: null
    })

    const pagination = ref({
      sortBy: 'created_at',
      descending: true,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const totalRefunds = computed(() => {
      return refunds.value.reduce((sum, refund) => sum + refund.amount, 0)
    })

    const totalTransactions = computed(() => {
      return refunds.value.length
    })

    const averageRefund = computed(() => {
      return totalTransactions.value ? totalRefunds.value / totalTransactions.value : 0
    })

    const totalItems = computed(() => {
      return refunds.value.reduce((sum, refund) => {
        return sum + refund.items.reduce((itemSum, item) => itemSum + item.quantity, 0)
      }, 0)
    })

    const fetchRefunds = async () => {
      loading.value = true
      try {
        const response = await api.get(`/admin/refunds/business/${route.params.businessId}`, {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        refunds.value = response.data.data
        pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Error fetching refunds:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch refunds',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchBranches = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/branches`)
        branches.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        console.error('Error fetching branches:', error)
      }
    }

    const viewRefund = (refund) => {
      selectedRefund.value = refund
      refundDialog.value = true
    }

    const printReceipt = (refund) => {
      window.open(`/refunds/${refund.id}/receipt`, '_blank')
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchRefunds()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchRefunds()
    }

    const getStatusColor = (status) => {
      const colors = {
        completed: 'positive',
        pending: 'warning',
        failed: 'negative'
      }
      return colors[status] || 'grey'
    }

    const printReport = () => {
      window.print()
    }

    const exportReport = async () => {
      try {
        const response = await api.get('/refunds/export', {
          params: filter.value,
          responseType: 'blob'
        })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'refunds_report.xlsx')
        document.body.appendChild(link)
        link.click()
        link.remove()
      } catch (error) {
        console.error('Error exporting report:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to export report',
          icon: 'error'
        })
      }
    }

    const approveRefund = async (refund) => {
      try {
        loading.value = true
        await api.post(`/admin/refunds/business/${route.params.businessId}/${refund.id}/approve`, {
          approved: true
        })

        $q.notify({
          color: 'positive',
          message: 'Refund approved successfully',
          icon: 'check'
        })

        await fetchRefunds()
        refundDialog.value = false
      } catch (error) {
        console.error('Error approving refund:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to approve refund',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const rejectRefund = (refund) => {
      selectedRefundForRejection.value = refund
      rejectionReason.value = ''
      showRejectionDialog.value = true
    }

    const submitRejection = async () => {
      if (!rejectionReason.value) {
        $q.notify({
          color: 'negative',
          message: 'Please provide a reason for rejection',
          icon: 'error'
        })
        return
      }

      try {
        loading.value = true
        await api.post(`/admin/refunds/business/${route.params.businessId}/${selectedRefundForRejection.value.id}/approve`, {
          approved: false,
          rejection_reason: rejectionReason.value
        })

        $q.notify({
          color: 'negative',
          message: 'Refund rejected successfully',
          icon: 'check'
        })

        await fetchRefunds()
        showRejectionDialog.value = false
        refundDialog.value = false
      } catch (error) {
        console.error('Error rejecting refund:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to reject refund',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchRefunds()
      fetchBranches()
    })

    return {
      loading,
      refunds,
      branches,
      columns,
      itemColumns,
      statusOptions,
      filter,
      pagination,
      refundDialog,
      selectedRefund,
      showRejectionDialog,
      rejectionReason,
      totalRefunds,
      totalTransactions,
      averageRefund,
      totalItems,
      viewRefund,
      printReceipt,
      onRequest,
      onSearch,
      getStatusColor,
      printReport,
      exportReport,
      approveRefund,
      rejectRefund,
      submitRejection
    }
  }
}
</script> 