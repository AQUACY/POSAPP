<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Welcome Section -->
      <div class="col-12">
        <q-card class="welcome-card">
          <q-card-section>
            <div class="text-h5 text-weight-bold">Welcome, {{ user?.name || 'User' }}</div>
            <div class="text-subtitle1 text-grey-7">Here's what's happening with your business today</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Stats Cards -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card">
          <q-card-section>
            <div class="text-h6 text-weight-bold">Total Sales</div>
            <div class="text-h4 text-primary">$12,345</div>
            <div class="text-caption text-green">
              <q-icon name="trending_up" /> 12% increase
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card">
          <q-card-section>
            <div class="text-h6 text-weight-bold">Orders</div>
            <div class="text-h4 text-primary">156</div>
            <div class="text-caption text-green">
              <q-icon name="trending_up" /> 8% increase
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card">
          <q-card-section>
            <div class="text-h6 text-weight-bold">Customers</div>
            <div class="text-h4 text-primary">2,345</div>
            <div class="text-caption text-green">
              <q-icon name="trending_up" /> 5% increase
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card">
          <q-card-section>
            <div class="text-h6 text-weight-bold">Products</div>
            <div class="text-h4 text-primary">1,234</div>
            <div class="text-caption text-red">
              <q-icon name="trending_down" /> 3% decrease
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Sales -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <div class="text-h6 text-weight-bold">Recent Sales</div>
          </q-card-section>
          <q-card-section>
            <q-table
              :rows="recentSales"
              :columns="salesColumns"
              row-key="id"
              flat
              bordered
            />
          </q-card-section>
        </q-card>
      </div>

      <!-- Low Stock Items -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6 text-weight-bold">Low Stock Items</div>
          </q-card-section>
          <q-card-section>
            <q-list>
              <q-item v-for="item in lowStockItems" :key="item.id">
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                  <q-item-label caption>Stock: {{ item.stock }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn flat color="primary" icon="add" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from 'stores/auth'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

// Mock data for recent sales
const recentSales = ref([
  {
    id: 1,
    customer: 'John Doe',
    amount: '$123.45',
    date: '2024-03-20',
    status: 'Completed'
  },
  {
    id: 2,
    customer: 'Jane Smith',
    amount: '$234.56',
    date: '2024-03-20',
    status: 'Pending'
  },
  {
    id: 3,
    customer: 'Bob Johnson',
    amount: '$345.67',
    date: '2024-03-19',
    status: 'Completed'
  }
])

const salesColumns = [
  {
    name: 'customer',
    label: 'Customer',
    field: 'customer',
    align: 'left'
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right'
  },
  {
    name: 'date',
    label: 'Date',
    field: 'date',
    align: 'center'
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
    align: 'center'
  }
]

// Mock data for low stock items
const lowStockItems = ref([
  {
    id: 1,
    name: 'Product A',
    stock: 5
  },
  {
    id: 2,
    name: 'Product B',
    stock: 3
  },
  {
    id: 3,
    name: 'Product C',
    stock: 2
  }
])
</script>

<style lang="scss" scoped>
.welcome-card {
  background: linear-gradient(145deg, $primary 0%, darken($primary, 20%) 100%);
  color: white;
}

.stat-card {
  transition: all 0.3s ease;
  
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}
</style> 