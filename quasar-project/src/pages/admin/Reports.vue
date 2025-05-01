<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Reports</div>
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

    <!-- Report Type Selection -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-select
          v-model="reportType"
          :options="reportTypes"
          label="Report Type"
          outlined
          emit-value
          map-options
          @update:model-value="onReportTypeChange"
        />
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.branch"
          :options="branches"
          label="Branch"
          outlined
          emit-value
          map-options
          @update:model-value="fetchReport"
        />
      </div>
      <div class="col-12 col-md-4">
        <q-input
          v-model="filter.date_range"
          label="Date Range"
          type="daterange"
          outlined
          @update:model-value="fetchReport"
        />
      </div>
    </div>

    <!-- Sales Summary -->
    <div v-if="reportType === 'sales'" class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4">${{ reportData.total_sales?.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Total Transactions</div>
            <div class="text-h4">{{ reportData.total_transactions }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-warning text-white">
          <q-card-section>
            <div class="text-h6">Average Sale</div>
            <div class="text-h4">${{ reportData.average_sale?.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">Items Sold</div>
            <div class="text-h4">{{ reportData.total_items }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Inventory Summary -->
    <div v-if="reportType === 'inventory'" class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Items</div>
            <div class="text-h4">{{ reportData.total_items }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-negative text-white">
          <q-card-section>
            <div class="text-h6">Low Stock Items</div>
            <div class="text-h4">{{ reportData.low_stock_items }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-warning text-white">
          <q-card-section>
            <div class="text-h6">Out of Stock</div>
            <div class="text-h4">{{ reportData.out_of_stock_items }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">Total Value</div>
            <div class="text-h4">${{ reportData.total_value?.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Staff Summary -->
    <div v-if="reportType === 'staff'" class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Staff</div>
            <div class="text-h4">{{ reportData.total_staff }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Active Staff</div>
            <div class="text-h4">{{ reportData.active_staff }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-warning text-white">
          <q-card-section>
            <div class="text-h6">Total Sales by Staff</div>
            <div class="text-h4">${{ reportData.total_sales_by_staff?.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-h6">Average Sales/Staff</div>
            <div class="text-h4">${{ reportData.average_sales_per_staff?.toFixed(2) }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Report Data Table -->
    <q-table
      :rows="reportData.items || []"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Amount Column -->
      <template v-slot:body-cell-amount="props">
        <q-td :props="props">
          ${{ props.row.amount?.toFixed(2) }}
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
    </q-table>

    <!-- Charts Section -->
    <div class="row q-col-gutter-md q-mt-md">
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Sales Trend</div>
            <div ref="salesChart" style="height: 300px"></div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Top Selling Items</div>
            <div ref="itemsChart" style="height: 300px"></div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { format } from 'date-fns'
import Chart from 'chart.js/auto'

export default {
  name: 'ReportsPage',

  setup () {
    const $q = useQuasar()
    const loading = ref(false)
    const reportType = ref('sales')
    const branches = ref([])
    const reportData = ref({})
    const salesChart = ref(null)
    const itemsChart = ref(null)

    const reportTypes = [
      { label: 'Sales Report', value: 'sales' },
      { label: 'Inventory Report', value: 'inventory' },
      { label: 'Staff Performance', value: 'staff' }
    ]

    const filter = ref({
      branch: null,
      date_range: null
    })

    const pagination = ref({
      sortBy: 'created_at',
      descending: true,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const columns = ref([])

    const getColumns = (type) => {
      const commonColumns = [
        { name: 'id', label: 'ID', field: 'id', sortable: true }
      ]

      switch (type) {
        case 'sales':
          return [
            ...commonColumns,
            { name: 'date', label: 'Date', field: 'created_at', sortable: true },
            { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
            { name: 'cashier', label: 'Cashier', field: row => row.cashier?.name, sortable: true },
            { name: 'amount', label: 'Amount', field: 'amount', sortable: true },
            { name: 'status', label: 'Status', field: 'status', sortable: true }
          ]
        case 'inventory':
          return [
            ...commonColumns,
            { name: 'name', label: 'Item Name', field: 'name', sortable: true },
            { name: 'category', label: 'Category', field: row => row.category?.name, sortable: true },
            { name: 'stock_level', label: 'Stock Level', field: 'stock_level', sortable: true },
            { name: 'reorder_level', label: 'Reorder Level', field: 'reorder_level', sortable: true },
            { name: 'value', label: 'Value', field: 'value', sortable: true }
          ]
        case 'staff':
          return [
            ...commonColumns,
            { name: 'name', label: 'Name', field: 'name', sortable: true },
            { name: 'role', label: 'Role', field: 'role', sortable: true },
            { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
            { name: 'sales', label: 'Total Sales', field: 'total_sales', sortable: true },
            { name: 'transactions', label: 'Transactions', field: 'total_transactions', sortable: true }
          ]
        default:
          return commonColumns
      }
    }

    const fetchReport = async () => {
      loading.value = true
      try {
        const response = await api.get(`/reports/${reportType.value}`, {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        reportData.value = response.data
        pagination.value.rowsNumber = response.data.total
        updateCharts()
      } catch (error) {
        console.error('Error fetching report:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch report data',
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

    const updateCharts = () => {
      if (reportType.value === 'sales') {
        updateSalesChart()
        updateItemsChart()
      }
    }

    const updateSalesChart = () => {
      if (!salesChart.value) return

      const ctx = salesChart.value.getContext('2d')
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: reportData.value.sales_trend?.map(item => format(new Date(item.date), 'MMM dd')),
          datasets: [{
            label: 'Sales',
            data: reportData.value.sales_trend?.map(item => item.amount),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      })
    }

    const updateItemsChart = () => {
      if (!itemsChart.value) return

      const ctx = itemsChart.value.getContext('2d')
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: reportData.value.top_items?.map(item => item.name),
          datasets: [{
            label: 'Quantity Sold',
            data: reportData.value.top_items?.map(item => item.quantity),
            backgroundColor: 'rgb(54, 162, 235)'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      })
    }

    const onReportTypeChange = () => {
      columns.value = getColumns(reportType.value)
      fetchReport()
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchReport()
    }

    const getStatusColor = (status) => {
      const colors = {
        completed: 'positive',
        refunded: 'warning',
        cancelled: 'negative'
      }
      return colors[status] || 'grey'
    }

    const printReport = () => {
      window.print()
    }

    const exportReport = async () => {
      try {
        const response = await api.get(`/reports/${reportType.value}/export`, {
          params: filter.value,
          responseType: 'blob'
        })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `${reportType.value}_report.xlsx`)
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

    watch(reportType, () => {
      columns.value = getColumns(reportType.value)
    })

    onMounted(() => {
      fetchBranches()
      columns.value = getColumns(reportType.value)
      fetchReport()
    })

    return {
      loading,
      reportType,
      reportTypes,
      branches,
      filter,
      pagination,
      columns,
      reportData,
      salesChart,
      itemsChart,
      onReportTypeChange,
      onRequest,
      getStatusColor,
      printReport,
      exportReport
    }
  }
}
</script> 