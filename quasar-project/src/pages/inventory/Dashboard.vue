<template>
  <q-page padding>
    <!-- Header Section -->
    <div class="row q-mb-lg">
      <div class="col-12">
        <h4 class="q-mt-none q-mb-md">Inventory Dashboard</h4>
        <div class="row q-col-gutter-md">
          <!-- Quick Actions -->
          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6">Quick Actions</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="row q-col-gutter-sm">
                  <div class="col-6">
                    <q-btn
                      color="primary"
                      icon="add_circle"
                      label="Add Stock"
                      class="full-width"
                      @click="onAddStock"
                    />
                  </div>
                  <div class="col-6">
                    <q-btn
                      color="secondary"
                      icon="inventory"
                      label="Check Stock"
                      class="full-width"
                      @click="onCheckStock"
                    />
                  </div>
                  <div class="col-6 q-mt-sm">
                    <q-btn
                      color="warning"
                      icon="warning"
                      label="Low Stock"
                      class="full-width"
                      @click="onViewLowStock"
                    />
                  </div>
                  <div class="col-6 q-mt-sm">
                    <q-btn
                      color="negative"
                      icon="report_problem"
                      label="Expired"
                      class="full-width"
                      @click="onViewExpired"
                    />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Key Metrics -->
          <div class="col-12 col-md-9">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6 col-md-3">
                <q-card flat bordered class="bg-positive text-white">
                  <q-card-section>
                    <div class="text-subtitle2">Total Items</div>
                    <div class="text-h4">{{ totalItems }}</div>
                    <div class="text-caption">{{ itemsChange }}% from last month</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-card flat bordered class="bg-warning text-white">
                  <q-card-section>
                    <div class="text-subtitle2">Low Stock Items</div>
                    <div class="text-h4">{{ lowStockItems }}</div>
                    <div class="text-caption">Need immediate attention</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-card flat bordered class="bg-negative text-white">
                  <q-card-section>
                    <div class="text-subtitle2">Expired Items</div>
                    <div class="text-h4">{{ expiredItems }}</div>
                    <div class="text-caption">Needs disposal</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-card flat bordered class="bg-info text-white">
                  <q-card-section>
                    <div class="text-subtitle2">Stock Value</div>
                    <div class="text-h4">GhC {{ stockValue }}</div>
                    <div class="text-caption">Total inventory value</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Section -->
    <div class="row q-col-gutter-md">
      <!-- Recent Activities -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6">Recent Activities</div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <q-list separator>
              <q-item v-for="activity in recentActivities" :key="activity.id">
                <q-item-section avatar>
                  <q-icon 
                    :name="activity.action_type === 'in' ? 'add_circle' : (activity.action_type === 'out' ? 'remove_circle' : 'edit')" 
                    :color="activity.action_type === 'in' ? 'positive' : (activity.action_type === 'out' ? 'negative' : 'warning')" 
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{ activity.action_type === 'in' ? 'Added' : (activity.action_type === 'out' ? 'Removed' : 'Adjusted') }} 
                    {{ activity.quantity }} units 
                    ({{ activity.old_quantity }} → {{ activity.new_quantity }})
                  </q-item-label>
                  <q-item-label caption>
                    {{ activity.notes }} • {{ new Date(activity.created_at).toLocaleString() }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip
                    :color="activity.action_type === 'in' ? 'positive' : (activity.action_type === 'out' ? 'negative' : 'warning')"
                    text-color="white"
                    size="sm"
                  >
                    GhC {{ activity.unit_price }}
                  </q-chip>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Stock Alerts -->
      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6">Stock Alerts</div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <q-list separator>
              <q-item v-for="alert in stockAlerts" :key="alert.id" clickable>
                <q-item-section avatar>
                  <q-icon :name="alert.icon" :color="alert.color" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ alert.title }}</q-item-label>
                  <q-item-label caption>{{ alert.message }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>

        <!-- Upcoming Expiry -->
        <q-card flat bordered class="q-mt-md">
          <q-card-section>
            <div class="text-h6">Upcoming Expiry</div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <q-list separator>
              <q-item v-for="item in upcomingExpiry" :key="item.id" clickable>
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                  <q-item-label caption>Expires in {{ item.daysLeft }} days</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip
                    :color="item.daysLeft <= 7 ? 'negative' : 'warning'"
                    text-color="white"
                    size="sm"
                  >
                    {{ item.quantity }} units
                  </q-chip>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Dialogs -->
    <q-dialog v-model="addStockDialog">
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Add Stock</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmitStock" class="q-gutter-md">
            <q-select
              v-model="stockForm.inventory_id"
              :options="inventoryItems"
              option-label="name"
              option-value="id"
              label="Select Item *"
              emit-value
              map-options
              :rules="[val => !!val || 'Item is required']"
            />

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
import { ref, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useInventoryStore } from 'stores/inventory'

// Define component name
defineOptions({
  name: 'InventoryDashboard'
})

const $q = useQuasar()
const router = useRouter()
const inventoryStore = useInventoryStore()

// State
const addStockDialog = ref(false)
const totalItems = ref(0)
const itemsChange = ref(0)
const lowStockItems = ref(0)
const expiredItems = ref(0)
const stockValue = ref(0)
const recentActivities = ref([])
const stockAlerts = ref([])
const upcomingExpiry = ref([])
const stockForm = ref({
  inventory_id: null,
  quantity: null,
  reason: ''
})
const inventoryItems = ref([])

// Methods
const fetchDashboardData = async () => {
  try {
    // Use store actions instead of direct API calls
    await Promise.all([
      inventoryStore.fetchDashboardSummary(),
      inventoryStore.fetchRecentActivities(),
      inventoryStore.fetchStockAlerts(),
      inventoryStore.fetchExpiringItems()
    ])

    // Update metrics from store state
    totalItems.value = inventoryStore.dashboardSummary.total_items
    itemsChange.value = inventoryStore.dashboardSummary.items_change
    lowStockItems.value = inventoryStore.dashboardSummary.low_stock_items
    expiredItems.value = inventoryStore.dashboardSummary.expired_items
    stockValue.value = inventoryStore.dashboardSummary.stock_value

    // Update lists from store state
    console.log('Store recent activities:', inventoryStore.recentActivities)
    recentActivities.value = inventoryStore.recentActivities
    console.log('Local recent activities:', recentActivities.value)
    stockAlerts.value = inventoryStore.stockAlerts
    upcomingExpiry.value = inventoryStore.expiringItems
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
    $q.notify({
      color: 'negative',
      message: 'Failed to load dashboard data',
      icon: 'error'
    })
  }
}

const fetchInventoryItems = async () => {
  try {
    await inventoryStore.fetchInventoryItems()
    console.log('Store inventory items:', inventoryStore.inventoryItems)
    inventoryItems.value = inventoryStore.inventoryItems
    console.log('Local inventory items:', inventoryItems.value)
  } catch (err) {
    console.error('Failed to fetch inventory items:', err)
    $q.notify({
      color: 'negative',
      message: 'Failed to load inventory items',
      icon: 'error'
    })
  }
}

const onAddStock = () => {
  addStockDialog.value = true
}

const onCheckStock = () => {
  router.push('inventory')
}

const onViewLowStock = () => {
  router.push({ 
    path: 'inventory',
    query: { filter: 'low-stock' }
  })
}

const onViewExpired = () => {
  router.push({ 
    path: 'inventory',
    query: { filter: 'expired' }
  })
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

    // Refresh dashboard data
    await fetchDashboardData()
  } catch (err) {
    console.error('Failed to add stock:', err)
    $q.notify({
      color: 'negative',
      message: 'Failed to add stock',
      icon: 'error'
    })
  }
}

// Load inventory items when dialog opens
watch(addStockDialog, (newVal) => {
  if (newVal) {
    fetchInventoryItems()
  }
})

// Lifecycle hooks
onMounted(() => {
  fetchDashboardData()
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