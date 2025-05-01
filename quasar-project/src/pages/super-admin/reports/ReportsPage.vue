<template>
  <q-page padding>
    <div class="text-h4 q-mb-lg">Reports & Analytics</div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Revenue</div>
            <div class="text-h4 text-primary">${{ stats.totalRevenue }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.revenueGrowth }}% from last month
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4 text-primary">${{ stats.totalSales }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.salesGrowth }}% from last month
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Active Businesses</div>
            <div class="text-h4 text-primary">{{ stats.activeBusinesses }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.businessGrowth }}% from last month
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Orders</div>
            <div class="text-h4 text-primary">{{ stats.totalOrders }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="trending_up" color="positive" />
              {{ stats.ordersGrowth }}% from last month
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Report Types -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-4">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Sales Reports</div>
            <q-list>
              <q-item clickable v-ripple to="/super-admin/reports/sales">
                <q-item-section avatar>
                  <q-avatar color="primary" text-color="white">
                    <q-icon name="trending_up" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Sales Overview</q-item-label>
                  <q-item-label caption>View detailed sales reports and analytics</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Revenue Reports</div>
            <q-list>
              <q-item clickable v-ripple to="/super-admin/reports/revenue">
                <q-item-section avatar>
                  <q-avatar color="secondary" text-color="white">
                    <q-icon name="attach_money" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Revenue Analysis</q-item-label>
                  <q-item-label caption>Track revenue growth and trends</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Business Reports</div>
            <q-list>
              <q-item clickable v-ripple to="/super-admin/reports/business">
                <q-item-section avatar>
                  <q-avatar color="accent" text-color="white">
                    <q-icon name="business" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Business Performance</q-item-label>
                  <q-item-label caption>Monitor business growth and metrics</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row q-col-gutter-md q-mt-lg">
      <div class="col-12">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Recent Activity</div>
            <q-timeline color="primary">
              <q-timeline-entry
                v-for="activity in recentActivity"
                :key="activity.id"
                :title="activity.title"
                :subtitle="activity.date"
                :icon="activity.icon"
                :color="activity.color"
              >
                <div>{{ activity.description }}</div>
              </q-timeline-entry>
            </q-timeline>
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

// Stats data
const stats = ref({
  totalRevenue: 0,
  revenueGrowth: 0,
  totalSales: 0,
  salesGrowth: 0,
  activeBusinesses: 0,
  businessGrowth: 0,
  totalOrders: 0,
  ordersGrowth: 0
})

// Recent activity data
const recentActivity = ref([])

const fetchReportData = async () => {
  try {
    // TODO: Replace with actual API calls
    // const response = await api.get('/super-admin/reports/overview')
    // stats.value = response.data.stats
    // recentActivity.value = response.data.recentActivity
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load report data',
      icon: 'error'
    })
  }
}

onMounted(() => {
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
</style> 