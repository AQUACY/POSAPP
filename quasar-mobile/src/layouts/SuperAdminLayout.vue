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
          POS System - Super Admin
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
          v-ripple
          clickable
          to="/super-admin/dashboard"
          exact
        >
          <q-item-section avatar>
            <q-icon name="dashboard" />
          </q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>

        <q-item
          v-ripple
          clickable
          to="/super-admin/businesses"
          exact
        >
          <q-item-section avatar>
            <q-icon name="business" />
          </q-item-section>
          <q-item-section>Businesses</q-item-section>
        </q-item>

        <q-item
          v-ripple
          clickable
          to="/super-admin/users"
          exact
        >
          <q-item-section avatar>
            <q-icon name="people" />
          </q-item-section>
          <q-item-section>Users</q-item-section>
        </q-item>

        <q-separator />

        <q-item-label header>Reports</q-item-label>

        <q-item
          v-ripple
          clickable
          to="/super-admin/reports"
          exact
        >
          <q-item-section avatar>
            <q-icon name="assessment" />
          </q-item-section>
          <q-item-section>Reports Overview</q-item-section>
        </q-item>

        <q-item
          v-ripple
          clickable
          to="/super-admin/reports/sales"
          exact
        >
          <q-item-section avatar>
            <q-icon name="trending_up" />
          </q-item-section>
          <q-item-section>Sales Reports</q-item-section>
        </q-item>

        <q-item
          v-ripple
          clickable
          to="/super-admin/reports/revenue"
          exact
        >
          <q-item-section avatar>
            <q-icon name="attach_money" />
          </q-item-section>
          <q-item-section>Revenue Reports</q-item-section>
        </q-item>

        <q-item
          v-ripple
          clickable
          to="/super-admin/reports/business"
          exact
        >
          <q-item-section avatar>
            <q-icon name="business_center" />
          </q-item-section>
          <q-item-section>Business Reports</q-item-section>
        </q-item>

        <q-separator />

        <q-item-label header>Settings</q-item-label>

        <q-item
          v-ripple
          clickable
          to="/super-admin/settings"
          exact
        >
          <q-item-section avatar>
            <q-icon name="settings" />
          </q-item-section>
          <q-item-section>System Settings</q-item-section>
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
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const leftDrawerOpen = ref(false)

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const onLogout = async () => {
  await authStore.logout()
  router.push('/auth/login')
}
</script>

<style lang="scss" scoped>
.q-drawer {
  .q-item {
    border-radius: 0 24px 24px 0;
    margin-right: 12px;
    margin-bottom: 4px;

    &.q-router-link-active {
      background: var(--primary-color);
      color: white;
    }
  }
}
</style> 