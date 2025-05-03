<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Sales Reports</div>
      <div>
        <q-btn
          color="primary"
          icon="file_download"
          label="Export Report"
          @click="exportReport"
          class="q-mr-sm"
        />
        <q-btn
          color="primary"
          icon="print"
          label="Print Report"
          @click="printReport"
        />
      </div>
    </div>

    <!-- Filters -->
    <div class="row q-col-gutter-md q-mb-lg">
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.business"
              :options="businessOptions"
              label="Business"
              outlined
              class="glass-input"
            />
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.period"
              :options="periodOptions"
              label="Time Period"
              outlined
              class="glass-input"
            />
          </div>
          <div class="col-12 col-md-3">
            <q-input
              v-model="filters.startDate"
              label="Start Date"
          type="date"
              outlined
              class="glass-input"
            />
          </div>
          <div class="col-12 col-md-3">
            <q-input
              v-model="filters.endDate"
              label="End Date"
          type="date"
              outlined
              class="glass-input"
            />
          </div>
        </div>

    <!-- Summary Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4 text-primary">${{ summary.totalSales }}</div>
            <div class="text-caption" :class="summary.salesGrowth >= 0 ? 'text-positive' : 'text-negative'">
              {{ summary.salesGrowth >= 0 ? '+' : '' }}{{ summary.salesGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Orders</div>
            <div class="text-h4 text-primary">{{ summary.totalOrders }}</div>
            <div class="text-caption" :class="summary.ordersGrowth >= 0 ? 'text-positive' : 'text-negative'">
              {{ summary.ordersGrowth >= 0 ? '+' : '' }}{{ summary.ordersGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Average Order Value</div>
            <div class="text-h4 text-primary">${{ summary.averageOrderValue }}</div>
            <div class="text-caption" :class="summary.aovGrowth >= 0 ? 'text-positive' : 'text-negative'">
              {{ summary.aovGrowth >= 0 ? '+' : '' }}{{ summary.aovGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Products Sold</div>
            <div class="text-h4 text-primary">{{ summary.totalProducts }}</div>
            <div class="text-caption" :class="summary.productsGrowth >= 0 ? 'text-positive' : 'text-negative'">
              {{ summary.productsGrowth >= 0 ? '+' : '' }}{{ summary.productsGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Charts -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-8">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Sales Trend</div>
            <div class="chart-container">
              <LineChart
                :data="salesChartData"
                :options="salesChartOptions"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Sales by Payment Method</div>
            <div class="chart-container">
              <PieChart
                :data="paymentChartData"
                :options="paymentChartOptions"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Detailed Table -->
        <q-card class="glass-card">
          <q-card-section>
        <div class="text-h6 q-mb-md">Detailed Sales Data</div>
            <q-table
          :rows="salesData"
              :columns="columns"
              row-key="id"
              :loading="loading"
              :pagination.sync="pagination"
              binary-state-sort
            >
          <!-- Date Column -->
          <template #body-cell-date="props">
                <q-td :props="props">
              {{ formatDate(props.row.date) }}
                </q-td>
              </template>

              <!-- Amount Column -->
              <template #body-cell-amount="props">
                <q-td :props="props">
              ${{ props.row.amount.toFixed(2) }}
                </q-td>
              </template>

          <!-- Growth Column -->
          <template #body-cell-growth="props">
                <q-td :props="props">
              <span :class="props.row.growth >= 0 ? 'text-positive' : 'text-negative'">
                {{ props.row.growth >= 0 ? '+' : '' }}{{ props.row.growth }}%
              </span>
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useSuperAdminStore } from 'stores/super-admin'
import LineChart from 'components/charts/LineChart.vue'
import PieChart from 'components/charts/PieChart.vue'

const $q = useQuasar()
const superAdminStore = useSuperAdminStore()

// State
const loading = ref(false)
const filters = ref({
  business: null,
  period: 'month',
  startDate: null,
  endDate: null
})

const summary = ref({
  totalSales: 0,
  salesGrowth: 0,
  totalOrders: 0,
  ordersGrowth: 0,
  averageOrderValue: 0,
  aovGrowth: 0,
  totalProducts: 0,
  productsGrowth: 0
})

const salesData = ref([])

// Options
const businessOptions = ref([])
const periodOptions = [
  { label: 'Today', value: 'day' },
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' },
  { label: 'This Year', value: 'year' },
  { label: 'Custom', value: 'custom' }
]

// Table Configuration
const columns = [
  {
    name: 'date',
    required: true,
    label: 'Date',
    align: 'left',
    field: row => row.date,
    sortable: true
  },
  {
    name: 'orders',
    required: true,
    label: 'Orders',
    align: 'right',
    field: row => row.orders,
    sortable: true
  },
  {
    name: 'amount',
    required: true,
    label: 'Amount',
    align: 'right',
    field: row => row.amount,
    sortable: true
  },
  {
    name: 'growth',
    required: true,
    label: 'Growth',
    align: 'right',
    field: row => row.growth,
    sortable: true
  }
]

const pagination = ref({
  sortBy: 'date',
  descending: true,
  page: 1,
  rowsPerPage: 10
})

// Chart Data
const salesChartData = computed(() => ({
  labels: salesData.value.map(item => formatDate(item.date)),
  datasets: [
    {
      label: 'Sales',
      data: salesData.value.map(item => item.amount),
      borderColor: 'var(--q-primary)',
      backgroundColor: 'rgba(var(--q-primary-rgb), 0.1)',
      tension: 0.4,
      fill: true
    }
  ]
}))

const salesChartOptions = {
  plugins: {
    title: {
      display: true,
      text: 'Sales Trend'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: value => `$${value}`
      }
    }
  }
}

const paymentChartData = computed(() => ({
  labels: ['Cash', 'Credit Card', 'Debit Card', 'Mobile Payment', 'Other'],
  datasets: [
    {
      data: [30, 25, 20, 15, 10],
      backgroundColor: [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)'
      ]
    }
  ]
}))

const paymentChartOptions = {
  plugins: {
    title: {
      display: true,
      text: 'Sales by Payment Method'
    }
  }
}

// Methods
const fetchBusinesses = async () => {
  try {
    await superAdminStore.fetchBusinesses()
    businessOptions.value = superAdminStore.businesses.map(business => ({
      label: business.name,
      value: business.id
    }))
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load businesses' + err,
      icon: 'error'
    })
  }
}

const fetchReportData = async () => {
  loading.value = true
  try {
    const response = await superAdminStore.getSalesReport(filters.value)
    summary.value = response.summary
    salesData.value = response.sales
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load report data' + err,
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const exportReport = async () => {
  try {
    await superAdminStore.exportSalesReport(filters.value)
  } catch (err) {
  $q.notify({
      color: 'negative',
      message: 'Failed to export report' + err,
      icon: 'error'
    })
  }
}

const printReport = () => {
  window.print()
}

// Watch for filter changes
watch(filters, () => {
  fetchReportData()
}, { deep: true })

onMounted(() => {
  fetchBusinesses()
  fetchReportData()
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
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 8px;
}

.chart-container {
  height: 300px;
  width: 100%;
}

@media print {
  .glass-card {
    background: none;
    backdrop-filter: none;
    border: 1px solid #ddd;
    box-shadow: none;
  }

  .glass-input {
    background: none;
    backdrop-filter: none;
    border: 1px solid #ddd;
  }

  .q-btn {
    display: none;
  }
}
</style> 