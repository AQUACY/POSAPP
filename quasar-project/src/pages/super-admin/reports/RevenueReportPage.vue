<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Revenue Reports</div>
      <div class="row q-gutter-sm">
        <q-btn
          color="primary"
          icon="file_download"
          label="Export"
          @click="exportReport"
        />
        <q-btn
          color="secondary"
          icon="print"
          label="Print"
          @click="printReport"
        />
      </div>
    </div>

    <!-- Filters -->
    <q-card class="glass-card q-mb-lg">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.business"
              :options="businessOptions"
              label="Business"
              outlined
              class="glass-input"
              clearable
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
              outlined
              class="glass-input"
              type="date"
            />
          </div>
          <div class="col-12 col-md-3">
            <q-input
              v-model="filters.endDate"
              label="End Date"
              outlined
              class="glass-input"
              type="date"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Revenue Overview -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Revenue</div>
            <div class="text-h4 text-primary">${{ stats.totalRevenue }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.revenueGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Net Profit</div>
            <div class="text-h4 text-primary">${{ stats.netProfit }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.profitGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Operating Costs</div>
            <div class="text-h4 text-primary">${{ stats.operatingCosts }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_down" color="negative" />
              {{ stats.costsGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Profit Margin</div>
            <div class="text-h4 text-primary">{{ stats.profitMargin }}%</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.marginGrowth }}% from previous period
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Revenue Trend</div>
            <!-- TODO: Add chart component -->
            <div class="text-center text-grey-7">
              Chart will be displayed here
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Revenue Table -->
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Revenue Details</div>
            <q-table
              :rows="revenue"
              :columns="columns"
              row-key="id"
              :loading="loading"
              :pagination.sync="pagination"
              :filter="search"
              binary-state-sort
            >
              <template #top-right>
                <q-input
                  v-model="search"
                  outlined
                  dense
                  placeholder="Search..."
                  class="glass-input"
                >
                  <template #prepend>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </template>

              <!-- Business Column -->
              <template #body-cell-business="props">
                <q-td :props="props">
                  <div class="row items-center">
                    <q-avatar size="30px" class="q-mr-sm">
                      <img :src="props.row.business.logo" v-if="props.row.business.logo">
                      <q-icon name="business" v-else />
                    </q-avatar>
                    <div>{{ props.row.business.name }}</div>
                  </div>
                </q-td>
              </template>

              <!-- Amount Column -->
              <template #body-cell-amount="props">
                <q-td :props="props">
                  <div class="text-weight-medium">${{ props.row.amount }}</div>
                </q-td>
              </template>

              <!-- Type Column -->
              <template #body-cell-type="props">
                <q-td :props="props">
                  <q-chip
                    :color="props.row.type === 'income' ? 'positive' : 'negative'"
                    text-color="white"
                    size="sm"
                  >
                    {{ props.row.type }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Table columns
const columns = [
  {
    name: 'id',
    label: 'Transaction ID',
    field: 'id',
    sortable: true,
    align: 'left'
  },
  {
    name: 'business',
    label: 'Business',
    field: row => row.business.name,
    sortable: true,
    align: 'left'
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    sortable: true,
    align: 'right'
  },
  {
    name: 'type',
    label: 'Type',
    field: 'type',
    sortable: true,
    align: 'center'
  },
  {
    name: 'category',
    label: 'Category',
    field: 'category',
    sortable: true,
    align: 'left'
  },
  {
    name: 'created_at',
    label: 'Date',
    field: 'created_at',
    sortable: true,
    align: 'left'
  }
]

// State
const loading = ref(false)
const search = ref('')
const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10
})

// Filters
const filters = ref({
  business: null,
  period: 'month',
  startDate: null,
  endDate: null
})

// Options
const businessOptions = [
  { label: 'All Businesses', value: null },
  { label: 'Business 1', value: 1 },
  { label: 'Business 2', value: 2 }
]

const periodOptions = [
  { label: 'Today', value: 'today' },
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' },
  { label: 'This Year', value: 'year' },
  { label: 'Custom', value: 'custom' }
]

// Stats data
const stats = ref({
  totalRevenue: 0,
  revenueGrowth: 0,
  netProfit: 0,
  profitGrowth: 0,
  operatingCosts: 0,
  costsGrowth: 0,
  profitMargin: 0,
  marginGrowth: 0
})

// Revenue data
const revenue = ref([])

const fetchRevenueData = async () => {
  loading.value = true
  try {
    // TODO: Replace with actual API calls
    // const response = await api.get('/super-admin/reports/revenue', {
    //   params: filters.value
    // })
    // stats.value = response.data.stats
    // revenue.value = response.data.revenue
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load revenue data',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const exportReport = () => {
  // TODO: Implement report export
  $q.notify({
    color: 'positive',
    message: 'Report exported successfully',
    icon: 'check_circle'
  })
}

const printReport = () => {
  // TODO: Implement report printing
  $q.notify({
    color: 'positive',
    message: 'Report printed successfully',
    icon: 'check_circle'
  })
}

// Watch for filter changes
watch(filters, () => {
  fetchRevenueData()
}, { deep: true })

onMounted(() => {
  fetchRevenueData()
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
  .q-field__control {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 8px;
    transition: all 0.3s ease;

    &:hover {
      border-color: var(--accent-color);
    }
  }
}
</style> 