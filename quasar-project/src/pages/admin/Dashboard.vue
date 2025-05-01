<template>
  <q-page padding>
    <div class="text-h4 q-mb-md">Dashboard</div>

    <div class="row q-col-gutter-md">
      <!-- Summary Cards -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Businesses</div>
            <div class="text-h4">{{ stats.totalBusinesses }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">Total Branches</div>
            <div class="text-h4">{{ stats.totalBranches }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-accent text-white">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4">${{ stats.totalSales }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Active Staff</div>
            <div class="text-h4">{{ stats.activeStaff }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row q-mt-lg">
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Recent Sales</div>
          </q-card-section>
          <q-card-section>
            <q-list separator>
              <q-item v-for="sale in recentSales" :key="sale.id">
                <q-item-section>
                  <q-item-label>{{ sale.business_name }}</q-item-label>
                  <q-item-label caption>{{ sale.created_at }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label>${{ sale.total_amount }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Low Stock Items</div>
          </q-card-section>
          <q-card-section>
            <q-list separator>
              <q-item v-for="item in lowStockItems" :key="item.id">
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                  <q-item-label caption>{{ item.business_name }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label>{{ item.quantity }} left</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'

export default {
  name: 'AdminDashboard',

  setup () {
    const stats = ref({
      totalBusinesses: 0,
      totalBranches: 0,
      totalSales: 0,
      activeStaff: 0
    })

    const recentSales = ref([])
    const lowStockItems = ref([])

    const fetchDashboardData = async () => {
      try {
        // Fetch dashboard data from your API
        const response = await api.get('/admin/dashboard')
        stats.value = response.data.stats
        recentSales.value = response.data.recentSales
        lowStockItems.value = response.data.lowStockItems
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
      }
    }

    onMounted(() => {
      fetchDashboardData()
    })

    return {
      stats,
      recentSales,
      lowStockItems
    }
  }
}
</script>
