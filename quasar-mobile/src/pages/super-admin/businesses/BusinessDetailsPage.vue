<template>
  <q-page padding>
    <div class="row items-center q-mb-lg">
      <q-btn
        flat
        icon="arrow_back"
        to="/super-admin/businesses"
        class="q-mr-md"
      />
      <div class="text-h4">Business Details</div>
    </div>

    <div v-if="loading" class="row justify-center">
      <q-spinner color="primary" size="3em" />
    </div>

    <template v-else>
      <!-- Business Overview -->
      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md-8">
          <q-card class="glass-card">
            <q-card-section>
              <div class="row items-center">
                <q-avatar size="100px" class="q-mr-md">
                  <img :src="business.logo" v-if="business.logo">
                  <q-icon name="business" size="50px" v-else />
                </q-avatar>
                <div>
                  <div class="text-h5">{{ business.name }}</div>
                  <div class="text-subtitle1 text-grey-7">{{ business.email }}</div>
                  <q-chip
                    :color="business.status === 'active' ? 'positive' : 'negative'"
                    text-color="white"
                    size="sm"
                  >
                    {{ business.status }}
                  </q-chip>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Quick Stats</div>
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <div class="text-subtitle2 text-grey-7">Total Sales</div>
                  <div class="text-h6">${{ business.stats?.totalSales || 0 }}</div>
                </div>
                <div class="col-6">
                  <div class="text-subtitle2 text-grey-7">Total Orders</div>
                  <div class="text-h6">{{ business.stats?.totalOrders || 0 }}</div>
                </div>
                <div class="col-6">
                  <div class="text-subtitle2 text-grey-7">Products</div>
                  <div class="text-h6">{{ business.stats?.totalProducts || 0 }}</div>
                </div>
                <div class="col-6">
                  <div class="text-subtitle2 text-grey-7">Customers</div>
                  <div class="text-h6">{{ business.stats?.totalCustomers || 0 }}</div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Business Information -->
      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Business Information</div>
              <q-list>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Phone</q-item-label>
                    <q-item-label>{{ business.phone }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Address</q-item-label>
                    <q-item-label>{{ business.address }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Currency</q-item-label>
                    <q-item-label>{{ business.currency }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Timezone</q-item-label>
                    <q-item-label>{{ business.timezone }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Created At</q-item-label>
                    <q-item-label>{{ business.created_at }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Business Owner</div>
              <q-list>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar>
                      <q-icon name="person" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ business.owner?.firstName }} {{ business.owner?.lastName }}</q-item-label>
                    <q-item-label caption>{{ business.owner?.email }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row q-col-gutter-md">
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
                    <q-item-label>${{ sale.amount }}</q-item-label>
                    <q-item-label caption>{{ sale.created_at }}</q-item-label>
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

        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Recent Orders</div>
              <q-list>
                <q-item v-for="order in recentOrders" :key="order.id">
                  <q-item-section avatar>
                    <q-avatar color="secondary" text-color="white">
                      <q-icon name="receipt" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Order #{{ order.id }}</q-item-label>
                    <q-item-label caption>{{ order.created_at }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-btn
                      flat
                      round
                      icon="visibility"
                      @click="onViewOrder(order)"
                    />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="row justify-end q-mt-lg">
        <q-btn
          label="Edit Business"
          color="primary"
          icon="edit"
          :to="`/super-admin/businesses/${business.id}/edit`"
          class="q-mr-md"
        />
        <q-btn
          :label="business.status === 'active' ? 'Deactivate' : 'Activate'"
          :color="business.status === 'active' ? 'negative' : 'positive'"
          :icon="business.status === 'active' ? 'block' : 'check_circle'"
          @click="onToggleStatus"
        />
      </div>
    </template>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const router = useRouter()

const business = ref({})
const recentSales = ref([])
const recentOrders = ref([])
const loading = ref(true)

const fetchBusinessDetails = async () => {
  loading.value = true
  try {
    // TODO: Replace with actual API calls
    // const [businessResponse, salesResponse, ordersResponse] = await Promise.all([
    //   api.get(`/super-admin/businesses/${route.params.id}`),
    //   api.get(`/super-admin/businesses/${route.params.id}/sales`),
    //   api.get(`/super-admin/businesses/${route.params.id}/orders`)
    // ])
    // business.value = businessResponse.data
    // recentSales.value = salesResponse.data
    // recentOrders.value = ordersResponse.data
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load business details' + error,
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const onToggleStatus = async () => {
  try {
    // TODO: Implement business status toggle
    // await api.patch(`/super-admin/businesses/${business.value.id}/toggle-status`)
    $q.notify({
      color: 'positive',
      message: `Business ${business.value.status === 'active' ? 'deactivated' : 'activated'} successfully`,
      icon: 'check_circle'
    })
    await fetchBusinessDetails()
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update business status' + error,
      icon: 'error'
    })
  }
}

const onViewSale = (sale) => {
  router.push(`/super-admin/sales/${sale.id}`)
}

const onViewOrder = (order) => {
  router.push(`/super-admin/orders/${order.id}`)
}

onMounted(() => {
  fetchBusinessDetails()
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