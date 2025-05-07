<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">Sales History Alex</div>

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
      <div class="col-12 col-md-4">
        <q-input
          v-model="dateRange"
          dense
          outlined
          label="Date Range"
          type="daterange"
        />
      </div>
    </div>

    <!-- Sales History Table -->
    <q-table
      :rows="filteredSales"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :pagination.sync="pagination"
      :rows-per-page-options="[10, 20, 50]"
    >
      <!-- Customer Column -->
      <template v-slot:body-cell-customer="props">
        <q-td :props="props">
          <div>{{ props.row.customer?.name }}</div>
          <div class="text-caption">{{ props.row.customer?.phone }}</div>
        </q-td>
      </template>

      <!-- Amount Column -->
      <template v-slot:body-cell-amount="props">
        <q-td :props="props">
          ${{ Number(props.row.final_amount).toFixed(2) }}
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
            @click="viewSale(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
          <q-btn
            flat
            dense
            color="primary"
            icon="receipt"
            @click="printReceipt(props.row)"
          >
            <q-tooltip>Print Receipt</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'completed'"
            flat
            dense
            color="negative"
            icon="undo"
            @click="initiateRefund(props.row)"
          >
            <q-tooltip>Process Refund</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Sale Details Dialog -->
    <q-dialog v-model="showSaleDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Sale Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedSale">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <div class="text-subtitle1">Sale #{{ selectedSale.sale_number }}</div>
              <div class="text-caption">
                {{ new Date(selectedSale.created_at).toLocaleString() }}
              </div>
            </div>

            <div class="col-12">
              <q-list bordered>
                <q-item v-for="item in selectedSale.items" :key="item.id">
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
              <div class="row justify-between q-mt-md">
                <div>Subtotal:</div>
                <div>${{ selectedSale.total_amount }}</div>
              </div>
              <div class="row justify-between">
                <div>Tax:</div>
                <div>${{ selectedSale.tax_amount }}</div>
              </div>
              <q-separator class="q-my-sm" />
              <div class="row justify-between text-h6">
                <div>Total:</div>
                <div>${{ selectedSale.final_amount }}</div>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
          <q-btn flat label="Print Receipt" color="primary" @click="printReceipt(selectedSale)" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Refund Dialog -->
    <q-dialog v-model="showRefundDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Process Refund</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-select
                v-model="selectedRefundItems"
                :options="refundItems"
                label="Select Items to Refund"
                multiple
                use-chips
                option-label="name"
                option-value="id"
              />
            </div>
            <div class="col-12">
              <q-input
                v-model="refundReason"
                type="textarea"
                label="Reason for Refund"
                :rules="[val => !!val || 'Reason is required']"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Process Refund" color="negative" @click="processRefund" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { posService } from '../../services/pos.service'

const route = useRoute()
const $q = useQuasar()

// State
const sales = ref([])
const loading = ref(false)
const search = ref('')
const selectedStatus = ref(null)
const dateRange = ref(null)
const showSaleDialog = ref(false)
const selectedSale = ref(null)
const showRefundDialog = ref(false)
const selectedRefundItems = ref([])
const refundReason = ref('')
const refundItems = ref([])
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
    field: 'sale_number',
    sortable: true
  },
  {
    name: 'customer',
    label: 'Customer',
    field: row => row.customer?.name,
    sortable: true
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'final_amount',
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
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' }
]

// Computed
const filteredSales = computed(() => {
  return sales.value.filter(sale => {
    const matchesSearch = search.value === '' ||
      sale.sale_number.toLowerCase().includes(search.value.toLowerCase()) ||
      sale.customer?.name.toLowerCase().includes(search.value.toLowerCase()) ||
      sale.customer?.phone.includes(search.value)

    const matchesStatus = !selectedStatus.value || sale.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

// Methods
const loadSales = async () => {
  try {
    loading.value = true
    const response = await posService.getRecentSales(
      route.params.businessId,
      route.params.branchId
    )
    sales.value = response.data?.data || []
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load sales',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const viewSale = (sale) => {
  selectedSale.value = sale
  showSaleDialog.value = true
}

const printReceipt = (sale) => {
  // Implement receipt printing logic
  window.print()
}

const getStatusColor = (status) => {
  switch (status) {
    case 'completed':
      return 'positive'
    case 'cancelled':
      return 'negative'
    default:
      return 'grey'
  }
}

const initiateRefund = (sale) => {
  selectedSale.value = sale
  refundItems.value = sale.items.map(item => ({
    id: item.id,
    name: item.inventory.name,
    quantity: item.quantity,
    unit_price: item.unit_price
  }))
  showRefundDialog.value = true
}

const processRefund = async () => {
  if (!refundReason.value) {
    $q.notify({
      color: 'negative',
      message: 'Please provide a reason for the refund',
      icon: 'error'
    })
    return
  }

  try {
    loading.value = true
    const response = await posService.processRefund(
      route.params.businessId,
      route.params.branchId,
      {
        saleId: selectedSale.value.id,
        items: selectedRefundItems.value,
        reason: refundReason.value
      }
    )

    $q.notify({
      color: 'positive',
      message: response.data.message || 'Refund request submitted successfully. Managers have been notified.',
      icon: 'check'
    })

    // Refresh the sales list
    await loadSales()
    showRefundDialog.value = false
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Failed to process refund',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadSales()
})
</script>

<style lang="scss" scoped>
@media print {
  .q-btn {
    display: none;
  }
}
</style> 