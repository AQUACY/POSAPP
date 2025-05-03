<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Sales</div>
      <q-btn
        color="primary"
        icon="add"
        label="New Sale"
        @click="openSaleDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search transactions..."
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

    <!-- Sales Summary Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4">${{ totalSales.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Total Transactions</div>
            <div class="text-h4">{{ totalTransactions }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-warning text-white">
          <q-card-section>
            <div class="text-h6">Average Sale</div>
            <div class="text-h4">${{ averageSale.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">Items Sold</div>
            <div class="text-h4">{{ totalItems }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Sales Table -->
    <q-table
      :rows="sales"
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
            @click="viewSale(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'completed'"
            flat
            round
            color="info"
            icon="receipt"
            @click="printReceipt(props.row)"
          >
            <q-tooltip>Print Receipt</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'completed'"
            flat
            round
            color="warning"
            icon="replay"
            @click="openRefundDialog(props.row)"
          >
            <q-tooltip>Refund</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Sale Details Dialog -->
    <q-dialog v-model="saleDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Sale Details</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Transaction ID</div>
              <div>{{ selectedSale?.id }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Date</div>
              <div>{{ formatDate(selectedSale?.created_at) }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Branch</div>
              <div>{{ selectedSale?.branch?.name }}</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="text-subtitle2">Cashier</div>
              <div>{{ selectedSale?.cashier?.name }}</div>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Items</div>
              <q-table
                :rows="selectedSale?.items || []"
                :columns="itemColumns"
                row-key="id"
                dense
                flat
              >
                <template v-slot:body-cell-price="props">
                  <q-td :props="props">
                    ${{ props.row.price.toFixed(2) }}
                  </q-td>
                </template>
                <template v-slot:body-cell-total="props">
                  <q-td :props="props">
                    ${{ (props.row.price * props.row.quantity).toFixed(2) }}
                  </q-td>
                </template>
              </q-table>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Payment Details</div>
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                  <div>Subtotal: ${{ selectedSale?.subtotal?.toFixed(2) }}</div>
                  <div>Tax: ${{ selectedSale?.tax?.toFixed(2) }}</div>
                  <div>Discount: ${{ selectedSale?.discount?.toFixed(2) }}</div>
                  <div class="text-h6">Total: ${{ selectedSale?.amount?.toFixed(2) }}</div>
                </div>
                <div class="col-12 col-md-6">
                  <div>Payment Method: {{ selectedSale?.payment_method }}</div>
                  <div>Status: {{ selectedSale?.status }}</div>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Close" v-close-popup />
          <q-btn
            v-if="selectedSale?.status === 'completed'"
            flat
            label="Print Receipt"
            @click="printReceipt(selectedSale)"
          />
          <q-btn
            v-if="selectedSale?.status === 'completed'"
            flat
            label="Refund"
            @click="openRefundDialog(selectedSale)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Refund Dialog -->
    <q-dialog v-model="refundDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Process Refund</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onRefundSubmit" class="q-gutter-md">
            <q-input
              v-model.number="refundData.amount"
              label="Refund Amount"
              type="number"
              :rules="[
                val => !!val || 'Amount is required',
                val => val > 0 || 'Amount must be greater than 0',
                val => val <= selectedSale?.amount || 'Amount cannot exceed sale total'
              ]"
              outlined
            />

            <q-input
              v-model="refundData.reason"
              label="Reason"
              type="textarea"
              :rules="[val => !!val || 'Reason is required']"
              outlined
            />

            <q-select
              v-model="refundData.payment_method"
              :options="paymentMethods"
              label="Refund Method"
              :rules="[val => !!val || 'Payment method is required']"
              outlined
              emit-value
              map-options
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Process Refund" @click="onRefundSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { format } from 'date-fns'

export default {
  name: 'SalesPage',

  setup () {
    const $q = useQuasar()
    const loading = ref(false)
    const sales = ref([])
    const branches = ref([])
    const saleDialog = ref(false)
    const refundDialog = ref(false)
    const selectedSale = ref(null)

    const refundData = ref({
      amount: 0,
      reason: '',
      payment_method: ''
    })

    const columns = [
      { name: 'id', label: 'Transaction ID', field: 'id', sortable: true },
      { name: 'date', label: 'Date', field: 'created_at', sortable: true, format: val => format(new Date(val), 'MMM dd, yyyy HH:mm') },
      { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
      { name: 'cashier', label: 'Cashier', field: row => row.cashier?.name, sortable: true },
      { name: 'amount', label: 'Amount', field: 'amount', sortable: true },
      { name: 'status', label: 'Status', field: 'status', sortable: true },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const itemColumns = [
      { name: 'name', label: 'Item', field: 'name' },
      { name: 'price', label: 'Price', field: 'price' },
      { name: 'quantity', label: 'Quantity', field: 'quantity' },
      { name: 'total', label: 'Total', field: row => row.price * row.quantity }
    ]

    const statusOptions = [
      { label: 'All', value: null },
      { label: 'Completed', value: 'completed' },
      { label: 'Refunded', value: 'refunded' },
      { label: 'Cancelled', value: 'cancelled' }
    ]

    const paymentMethods = [
      { label: 'Cash', value: 'cash' },
      { label: 'Credit Card', value: 'credit_card' },
      { label: 'Debit Card', value: 'debit_card' },
      { label: 'Bank Transfer', value: 'bank_transfer' }
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

    const totalSales = computed(() => {
      return sales.value.reduce((sum, sale) => sum + sale.amount, 0)
    })

    const totalTransactions = computed(() => {
      return sales.value.length
    })

    const averageSale = computed(() => {
      return totalTransactions.value ? totalSales.value / totalTransactions.value : 0
    })

    const totalItems = computed(() => {
      return sales.value.reduce((sum, sale) => {
        return sum + sale.items.reduce((itemSum, item) => itemSum + item.quantity, 0)
      }, 0)
    })

    const fetchSales = async () => {
      loading.value = true
      try {
        const response = await api.get('/sales', {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        sales.value = response.data.data
        pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Error fetching sales:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch sales',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchBranches = async () => {
      try {
        const response = await api.get('/branches')
        branches.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        console.error('Error fetching branches:', error)
      }
    }

    const viewSale = (sale) => {
      selectedSale.value = sale
      saleDialog.value = true
    }

    const openRefundDialog = (sale) => {
      selectedSale.value = sale
      refundData.value = {
        amount: sale.amount,
        reason: '',
        payment_method: ''
      }
      refundDialog.value = true
    }

    const onRefundSubmit = async () => {
      try {
        await api.post(`/sales/${selectedSale.value.id}/refund`, refundData.value)
        refundDialog.value = false
        fetchSales()
        $q.notify({
          color: 'positive',
          message: 'Refund processed successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error processing refund:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to process refund',
          icon: 'error'
        })
      }
    }

    const printReceipt = (sale) => {
      // Implement receipt printing logic
      window.open(`/api/sales/${sale.id}/receipt`, '_blank')
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchSales()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchSales()
    }

    const getStatusColor = (status) => {
      const colors = {
        completed: 'positive',
        refunded: 'warning',
        cancelled: 'negative'
      }
      return colors[status] || 'grey'
    }

    const formatDate = (date) => {
      return date ? format(new Date(date), 'MMM dd, yyyy HH:mm') : ''
    }

    onMounted(() => {
      fetchSales()
      fetchBranches()
    })

    return {
      loading,
      sales,
      branches,
      columns,
      itemColumns,
      statusOptions,
      paymentMethods,
      filter,
      pagination,
      saleDialog,
      refundDialog,
      selectedSale,
      refundData,
      totalSales,
      totalTransactions,
      averageSale,
      totalItems,
      viewSale,
      openRefundDialog,
      onRefundSubmit,
      printReceipt,
      onRequest,
      onSearch,
      getStatusColor,
      formatDate
    }
  }
}
</script> 