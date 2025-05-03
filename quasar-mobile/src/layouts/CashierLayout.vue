<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          POS System - Cashier
        </q-toolbar-title>

        <q-btn-dropdown flat icon="person">
          <q-list>
            <q-item clickable v-close-popup @click="onLogout">
              <q-item-section>
                <q-item-label>Logout</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-grey-1"
    >
      <q-list>
        <q-item-label header>Navigation</q-item-label>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'pos', params: { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id }}"
          exact
          @click="logNavigation('pos', { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id })"
        >
          <q-item-section avatar>
            <q-icon name="shopping_cart" />
          </q-item-section>
          <q-item-section>
            POS
          </q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'pending-sales', params: { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id }}"
          exact
          @click="logNavigation('pending-sales', { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id })"
        >
          <q-item-section avatar>
            <q-icon name="pending_actions" />
          </q-item-section>
          <q-item-section>
            Pending Sales
          </q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'sales-history', params: { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id }}"
          exact
          @click="logNavigation('sales-history', { businessId: authStore.user?.business_id, branchId: authStore.user?.branch_id })"
        >
          <q-item-section avatar>
            <q-icon name="history" />
          </q-item-section>
          <q-item-section>
            Sales History
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from 'stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const leftDrawerOpen = ref(false)

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const onLogout = async () => {
  await authStore.logout()
  router.push('/auth/login')
}

// Add navigation logging
const logNavigation = (routeName, params) => {
  console.log('Navigating to:', {
    name: routeName,
    params: params,
    fullPath: router.resolve({ name: routeName, params }).href
  })
}
</script>

<style lang="scss" scoped>
.q-header {
  backdrop-filter: blur(10px);
  background: var(--glass-bg) !important;
  border-bottom: 1px solid var(--glass-border);
}

.q-drawer {
  background: var(--glass-bg) !important;
  border-right: 1px solid var(--glass-border);
}
</style> 