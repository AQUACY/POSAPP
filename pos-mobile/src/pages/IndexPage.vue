<template>
  <q-page class="q-pa-md">
    <div class="text-h4 q-mb-md">Dashboard</div>
    
    <div class="row q-col-gutter-md">
      <!-- Sales Overview Card -->
      <div class="col-12 col-md-4">
        <q-card class="dashboard-card">
          <q-card-section>
            <div class="text-h6">Today's Sales</div>
            <div class="text-h4">$1,234.56</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Products Card -->
      <div class="col-12 col-md-4">
        <q-card class="dashboard-card">
          <q-card-section>
            <div class="text-h6">Total Products</div>
            <div class="text-h4">150</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Customers Card -->
      <div class="col-12 col-md-4">
        <q-card class="dashboard-card">
          <q-card-section>
            <div class="text-h6">Total Customers</div>
            <div class="text-h4">45</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="q-mt-lg">
      <div class="text-h6 q-mb-md">Recent Sales</div>
      <q-table
        :rows="recentSales"
        :columns="columns"
        row-key="id"
        :pagination="{ rowsPerPage: 5 }"
      />
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const columns = [
  {
    name: 'id',
    label: 'ID',
    field: 'id',
    align: 'left'
  },
  {
    name: 'date',
    label: 'Date',
    field: 'date',
    align: 'left'
  },
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
  }
]

const recentSales = ref([
  {
    id: 1,
    date: '2024-03-05',
    customer: 'John Doe',
    amount: '$123.45'
  },
  {
    id: 2,
    date: '2024-03-05',
    customer: 'Jane Smith',
    amount: '$234.56'
  },
  {
    id: 3,
    date: '2024-03-04',
    customer: 'Bob Johnson',
    amount: '$345.67'
  }
])
</script>

<style lang="scss" scoped>
.dashboard-card {
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
}
</style>
