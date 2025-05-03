<template>
  <q-page padding>
    <div class="text-h4 q-mb-lg">Dashboard</div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Businesses</div>
            <div class="text-h4 text-primary">{{ stats.totalBusinesses }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Active Businesses</div>
            <div class="text-h4 text-positive">{{ stats.activeBusinesses }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Revenue</div>
            <div class="text-h4 text-primary">${{ stats.totalRevenue }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6">Total Sales</div>
            <div class="text-h4 text-primary">${{ stats.totalSales }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Quick Actions</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  color="primary"
                  icon="add_business"
                  label="Add Business"
                  to="/super-admin/businesses/create"
                  class="full-width"
                />
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  color="secondary"
                  icon="assessment"
                  label="View Reports"
                  to="/super-admin/reports"
                  class="full-width"
                />
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  color="accent"
                  icon="people"
                  label="Manage Users"
                  to="/super-admin/users"
                  class="full-width"
                />
              </div>
              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  color="positive"
                  icon="settings"
                  label="System Settings"
                  to="/super-admin/settings"
                  class="full-width"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Recent Businesses</div>
            <q-list>
              <q-item v-for="business in recentBusinesses" :key="business.id">
                <q-item-section avatar>
                  <q-avatar>
                    <img :src="business.logo" v-if="business.logo">
                    <q-icon name="business" v-else />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ business.name }}</q-item-label>
                  <q-item-label caption>{{ business.created_at }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat
                    round
                    icon="more_vert"
                    @click="onBusinessMenu(business)"
                  >
                    <q-menu>
                      <q-list>
                        <q-item clickable v-close-popup @click="onViewBusiness(business)">
                          <q-item-section>View Details</q-item-section>
                        </q-item>
                        <q-item clickable v-close-popup @click="onEditBusiness(business)">
                          <q-item-section>Edit</q-item-section>
                        </q-item>
                        <q-item clickable v-close-popup @click="onToggleStatus(business)">
                          <q-item-section>
                            {{ business.status === 'active' ? 'Deactivate' : 'Activate' }}
                          </q-item-section>
                        </q-item>
                      </q-list>
                    </q-menu>
                  </q-btn>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Recent Sales</div>
            <q-list>
              <q-item v-for="sale in recentSales" :key="sale.id">
                <q-item-section avatar>
                  <q-avatar color="primary" text-color="white">
                    <q-icon name="shopping_cart" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ sale.business_name }}</q-item-label>
                  <q-item-label caption>${{ sale.amount }} - {{ sale.created_at }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat
                    round
                    icon="visibility"
                    @click="onViewSale(sale)"
                  />
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const router = useRouter()

// Mock data - replace with actual API calls
const stats = ref({
  totalBusinesses: 0,
  activeBusinesses: 0,
  totalRevenue: 0,
  totalSales: 0
})

const recentBusinesses = ref([])
const recentSales = ref([])

const fetchDashboardData = async () => {
  try {
    // TODO: Replace with actual API calls
    // const response = await api.get('/super-admin/dashboard')
    // stats.value = response.data.stats
    // recentBusinesses.value = response.data.recentBusinesses
    // recentSales.value = response.data.recentSales
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load dashboard data' + err,
      icon: 'error'
    })
  }
}

const onBusinessMenu = (business) => {
  console.log(business)
  // Handle business menu actions
}

const onViewBusiness = (business) => {
  router.push(`/super-admin/businesses/${business.id}`)
}

const onEditBusiness = (business) => {
  router.push(`/super-admin/businesses/${business.id}/edit`)
}

const onToggleStatus = async (business) => {
  try {
    // TODO: Implement business status toggle
    // await api.patch(`/super-admin/businesses/${business.id}/toggle-status`)
    $q.notify({
      color: 'positive',
      message: `Business ${business.status === 'active' ? 'deactivated' : 'activated'} successfully`,
      icon: 'check_circle'
    })
    await fetchDashboardData()
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update business status' + err,
      icon: 'error'
    })
  }
}

const onViewSale = (sale) => {
  router.push(`/super-admin/sales/${sale.id}`)
}

onMounted(() => {
  fetchDashboardData()
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