<template>
  <q-page padding>
    <div class="row q-mb-lg">
      <div class="col-12">
        <h4 class="q-mt-none q-mb-md">Inventory Reports</h4>
        
        <!-- Date Range Filter -->
        <div class="row q-col-gutter-md q-mb-lg">
          <div class="col-12 col-md-4">
            <q-input
              v-model="dateRange.from"
              label="From Date"
              type="date"
              outlined
              dense
            />
          </div>
          <div class="col-12 col-md-4">
            <q-input
              v-model="dateRange.to"
              label="To Date"
              type="date"
              outlined
              dense
            />
          </div>
          <div class="col-12 col-md-4">
            <q-btn
              color="primary"
              label="Generate Report"
              class="full-width"
              @click="generateReport"
              :loading="loading"
            />
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row q-col-gutter-md q-mb-lg">
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered class="bg-primary text-white">
              <q-card-section>
                <div class="text-subtitle2">Total Stock Changes</div>
                <div class="text-h4">{{ summary.totalChanges }}</div>
                <div class="text-caption">In selected period</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered class="bg-positive text-white">
              <q-card-section>
                <div class="text-subtitle2">Stock Added</div>
                <div class="text-h4">{{ summary.totalAdded }}</div>
                <div class="text-caption">Units added</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered class="bg-negative text-white">
              <q-card-section>
                <div class="text-subtitle2">Stock Removed</div>
                <div class="text-h4">{{ summary.totalRemoved }}</div>
                <div class="text-caption">Units removed</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered class="bg-info text-white">
              <q-card-section>
                <div class="text-subtitle2">Net Change</div>
                <div class="text-h4">{{ summary.netChange }}</div>
                <div class="text-caption">Overall change</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Detailed Reports -->
        <div class="row q-col-gutter-md">
          <!-- Stock Movement History -->
          <div class="col-12 col-md-8">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6">Stock Movement History</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-table
                  :rows="stockMovements"
                  :columns="movementColumns"
                  row-key="id"
                  :loading="loading"
                  :pagination="{ rowsPerPage: 10 }"
                >
                  <template v-slot:body-cell-action_type="props">
                    <q-td :props="props">
                      <q-chip
                        :color="props.row.action_type === 'in' ? 'positive' : 'negative'"
                        text-color="white"
                        size="sm"
                      >
                        {{ props.row.action_type === 'in' ? 'Added' : 'Removed' }}
                      </q-chip>
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-card>
          </div>

          <!-- Most Active Items -->
          <div class="col-12 col-md-4">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6">Most Active Items</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-list separator>
                  <q-item v-for="item in mostActiveItems" :key="item.id">
                    <q-item-section>
                      <q-item-label>{{ item.name }}</q-item-label>
                      <q-item-label caption>
                        {{ item.total_changes }} changes
                      </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-chip
                        :color="item.net_change >= 0 ? 'positive' : 'negative'"
                        text-color="white"
                        size="sm"
                      >
                        {{ item.net_change >= 0 ? '+' : '' }}{{ item.net_change }}
                      </q-chip>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card-section>
            </q-card>

            <!-- Low Stock Items -->
            <q-card flat bordered class="q-mt-md">
              <q-card-section>
                <div class="text-h6">Low Stock Items</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-list separator>
                  <q-item v-for="item in lowStockItems" :key="item.id">
                    <q-item-section>
                      <q-item-label>{{ item.name }}</q-item-label>
                      <q-item-label caption>
                        Current: {{ item.quantity }} | Reorder: {{ item.reorder_level }}
                      </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-btn
                        flat
                        round
                        color="primary"
                        icon="add_circle"
                        @click="onAddStock(item)"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Stock Dialog -->
    <q-dialog v-model="addStockDialog">
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Add Stock</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmitStock" class="q-gutter-md">
            <q-input
              v-model.number="stockForm.quantity"
              type="number"
              label="Quantity *"
              :rules="[
                val => !!val || 'Quantity is required',
                val => val > 0 || 'Quantity must be greater than 0'
              ]"
            />

            <q-input
              v-model="stockForm.reason"
              type="textarea"
              label="Reason"
              hint="Add any additional information"
            />

            <div class="row justify-end q-mt-md">
              <q-btn
                label="Cancel"
                color="grey"
                flat
                class="q-mr-sm"
                v-close-popup
              />
              <q-btn
                label="Add Stock"
                type="submit"
                color="primary"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useInventoryStore } from 'stores/inventory'

const $q = useQuasar()
const inventoryStore = useInventoryStore()

// State
const loading = ref(false)
const dateRange = ref({
  from: new Date().toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
})

const summary = ref({
  totalChanges: 0,
  totalAdded: 0,
  totalRemoved: 0,
  netChange: 0
})

const stockMovements = ref([])
const mostActiveItems = ref([])
const lowStockItems = ref([])

const movementColumns = [
  {
    name: 'date',
    label: 'Date',
    field: row => new Date(row.created_at).toLocaleString(),
    sortable: true
  },
  {
    name: 'item',
    label: 'Item',
    field: 'inventory_name',
    sortable: true
  },
  {
    name: 'action_type',
    label: 'Action',
    field: 'action_type',
    sortable: true
  },
  {
    name: 'quantity',
    label: 'Quantity',
    field: 'quantity',
    sortable: true
  },
  {
    name: 'notes',
    label: 'Notes',
    field: 'notes'
  }
]

const addStockDialog = ref(false)
const stockForm = ref({
  inventory_id: null,
  quantity: null,
  reason: ''
})

// Methods
const generateReport = async () => {
  loading.value = true
  try {
    const response = await inventoryStore.fetchInventoryReport({
      from: dateRange.value.from,
      to: dateRange.value.to
    })

    // Update summary
    summary.value = response.summary

    // Update stock movements
    stockMovements.value = response.movements

    // Update most active items
    mostActiveItems.value = response.most_active_items

    // Update low stock items
    lowStockItems.value = response.low_stock_items
  } catch (err) {
    console.error('Failed to generate report:', err)
    $q.notify({
      color: 'negative',
      message: 'Failed to generate report',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const onAddStock = (item) => {
  stockForm.value.inventory_id = item.id
  addStockDialog.value = true
}

const onSubmitStock = async () => {
  try {
    await inventoryStore.updateInventoryQuantity({
      id: stockForm.value.inventory_id,
      change_type: 'addition',
      quantity: stockForm.value.quantity,
      reason: stockForm.value.reason
    })

    // Show success message
    $q.notify({
      color: 'positive',
      message: 'Stock added successfully',
      icon: 'check'
    })

    // Reset form and close dialog
    stockForm.value = {
      inventory_id: null,
      quantity: null,
      reason: ''
    }
    addStockDialog.value = false

    // Refresh report
    await generateReport()
  } catch (err) {
    console.error('Failed to add stock:', err)
    $q.notify({
      color: 'negative',
      message: 'Failed to add stock',
      icon: 'error'
    })
  }
}

// Lifecycle hooks
onMounted(() => {
  generateReport()
})
</script>

<style lang="scss" scoped>
.q-card {
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
}

.text-h4 {
  font-weight: 500;
}

.text-caption {
  opacity: 0.8;
}
</style>
