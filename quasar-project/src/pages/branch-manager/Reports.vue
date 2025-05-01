<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Report Type Selection -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Generate Reports</div>
          </q-card-section>

          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-select
                  v-model="reportType"
                  :options="reportTypes"
                  label="Report Type"
                  outlined
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input
                  v-model="dateRange"
                  label="Date Range"
                  outlined
                  readonly
                >
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date
                          v-model="dateRange"
                          range
                          mask="YYYY-MM-DD"
                        />
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-4">
                <q-btn
                  color="primary"
                  icon="download"
                  label="Generate Report"
                  @click="generateReport"
                  :loading="loading"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Sales Report -->
      <div v-if="reportType === 'sales'" class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Sales Report</div>
          </q-card-section>

          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-card class="bg-primary text-white">
                  <q-card-section>
                    <div class="text-h6">Total Sales</div>
                    <div class="text-h4">${{ salesReport?.total || 0 }}</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-md-4">
                <q-card class="bg-secondary text-white">
                  <q-card-section>
                    <div class="text-h6">Total Transactions</div>
                    <div class="text-h4">{{ salesReport?.transactions || 0 }}</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-md-4">
                <q-card class="bg-accent text-white">
                  <q-card-section>
                    <div class="text-h6">Average Sale</div>
                    <div class="text-h4">${{ salesReport?.average || 0 }}</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Inventory Report -->
      <div v-if="reportType === 'inventory'" class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Inventory Report</div>
          </q-card-section>

          <q-card-section>
            <q-table
              :rows="inventoryReport"
              :columns="inventoryColumns"
              row-key="id"
              :loading="loading"
              flat
              bordered
            />
          </q-card-section>
        </q-card>
      </div>

      <!-- Staff Performance Report -->
      <div v-if="reportType === 'staff'" class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Staff Performance Report</div>
          </q-card-section>

          <q-card-section>
            <q-table
              :rows="staffReport"
              :columns="staffColumns"
              row-key="id"
              :loading="loading"
              flat
              bordered
            />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useBranchManagerStore } from 'stores/branchManager'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'BranchManagerReports',

  setup () {
    const store = useBranchManagerStore()
    const $q = useQuasar()
    const loading = ref(false)
    const reportType = ref('sales')
    const dateRange = ref({
      from: '',
      to: ''
    })

    const reportTypes = [
      { label: 'Sales Report', value: 'sales' },
      { label: 'Inventory Report', value: 'inventory' },
      { label: 'Staff Performance', value: 'staff' }
    ]

    const inventoryColumns = [
      { name: 'id', label: 'ID', field: 'id', align: 'left' },
      { name: 'name', label: 'Name', field: 'name', align: 'left' },
      { name: 'quantity', label: 'Quantity', field: 'quantity', align: 'right' },
      { name: 'sold', label: 'Sold', field: 'sold', align: 'right' },
      { name: 'revenue', label: 'Revenue', field: 'revenue', align: 'right' }
    ]

    const staffColumns = [
      { name: 'id', label: 'ID', field: 'id', align: 'left' },
      { name: 'name', label: 'Name', field: 'name', align: 'left' },
      { name: 'sales', label: 'Total Sales', field: 'sales', align: 'right' },
      { name: 'transactions', label: 'Transactions', field: 'transactions', align: 'right' },
      { name: 'rating', label: 'Rating', field: 'rating', align: 'center' }
    ]

    const generateReport = async () => {
      loading.value = true
      try {
        // Implement report generation based on type and date range
        $q.notify({
          color: 'positive',
          message: 'Report generated successfully'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error generating report'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      loading,
      reportType,
      dateRange,
      reportTypes,
      inventoryColumns,
      staffColumns,
      generateReport
    }
  }
})
</script>
