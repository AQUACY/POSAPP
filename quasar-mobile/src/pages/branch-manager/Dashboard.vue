<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Sales Overview Card -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Today's Sales</div>
            <div class="text-h4">${{ salesSummary?.today || 0 }}</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Weekly Sales Card -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">This Week</div>
            <div class="text-h4">${{ salesSummary?.this_week || 0 }}</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Monthly Sales Card -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="bg-accent text-white">
          <q-card-section>
            <div class="text-h6">This Month</div>
            <div class="text-h4">${{ salesSummary?.this_month || 0 }}</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Total Transactions Card -->
      <div class="col-12 col-md-6 col-lg-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Total Transactions</div>
            <div class="text-h4">{{ salesSummary?.total_transactions || 0 }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted, computed } from 'vue'
import { useBranchManagerStore } from 'stores/branchManager'

export default defineComponent({
  name: 'BranchManagerDashboard',

  setup () {
    const store = useBranchManagerStore()
    const loading = ref(false)

    onMounted(async () => {
      loading.value = true
      try {
        await store.fetchSalesSummary()
      } catch (error) {
        console.error('Error loading dashboard data:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      salesSummary: computed(() => store.salesSummary)
    }
  }
})
</script>
