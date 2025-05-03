<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">Pending Sales</div>

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

    <!-- Pending Sales Table -->
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
          ${{ Number(props.row.final_amount || 0).toFixed(2) }}
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
            v-if="props.row.status === 'pending'"
            flat
            dense
            color="positive"
            icon="payments"
            @click="processPayment(props.row)"
          >
            <q-tooltip>Process Payment</q-tooltip>
          </q-btn>
          <q-btn
            v-if="props.row.status === 'pending'"
            flat
            dense
            color="negative"
            icon="cancel"
            @click="cancelSale(props.row)"
          >
            <q-tooltip>Cancel Sale</q-tooltip>
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
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Payment Processing Modal -->
    <payment-processing-modal
      v-model="showPaymentModal"
      :sale="selectedSale"
      :business-id="route.params.businessId"
      :branch-id="route.params.branchId"
      @payment-processed="handlePaymentProcessed"
    />
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { posService } from '../../services/pos.service'
import PaymentProcessingModal from '../../components/pos/PaymentProcessingModal.vue'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

// State
const sales = ref([])
const loading = ref(false)
const search = ref('')
const selectedStatus = ref('pending')
const showSaleDialog = ref(false)
const selectedSale = ref(null)
const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10
})
const showPaymentModal = ref(false)

// Cleanup function
const cleanup = () => {
  sales.value = []
  selectedSale.value = null
  showSaleDialog.value = false
}

// Lifecycle hooks
onMounted(() => {
  loadSales()
})

onBeforeUnmount(() => {
  cleanup()
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
  { label: 'Pending', value: 'pending' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' }
]

// Computed
const filteredSales = computed(() => {
  if (!Array.isArray(sales.value)) return []
  
  return sales.value.filter(sale => {
    const matchesSearch = search.value === '' ||
      sale.sale_number?.toLowerCase().includes(search.value.toLowerCase()) ||
      sale.customer?.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      sale.customer?.phone?.includes(search.value)

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
      route.params.branchId,
      { status: 'pending' }
    )
    sales.value = response.data?.data || []
  } catch (error) {
    console.error('Error loading sales:', error)
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Failed to load pending sales',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const viewSale = (sale) => {
  if (!sale) return
  selectedSale.value = sale
  showSaleDialog.value = true
}

const processPayment = (sale) => {
  if (!sale) return
  
  try {
    if (sale.payment_url) {
      window.open(sale.payment_url, '_blank')
    } else {
      selectedSale.value = sale
      showPaymentModal.value = true
    }
  } catch (error) {
    console.error('Error processing payment:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to process payment',
      icon: 'error'
    })
  }
}

const cancelSale = async (sale) => {
  if (!sale) return

  try {
    await $q.dialog({
      title: 'Cancel Sale',
      message: 'Are you sure you want to cancel this sale?',
      cancel: true,
      persistent: true
    })

    loading.value = true
    await posService.cancelSale(
      route.params.businessId,
      route.params.branchId,
      sale.id
    )

    $q.notify({
      color: 'positive',
      message: 'Sale cancelled successfully',
      icon: 'check_circle'
    })

    await loadSales()
  } catch (error) {
    if (error) {
      $q.notify({
        color: 'negative',
        message: error.message || 'Failed to cancel sale',
        icon: 'error'
      })
    }
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'pending':
      return 'warning'
    case 'completed':
      return 'positive'
    case 'cancelled':
      return 'negative'
    default:
      return 'grey'
  }
}

const handlePaymentProcessed = async () => {
  await loadSales()
}
</script> 