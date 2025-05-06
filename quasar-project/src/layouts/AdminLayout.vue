<template>
  <q-layout view="lHh Lpr lFf">
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
          POS Admin Dashboard
        </q-toolbar-title>

        <q-btn flat round dense icon="person" class="q-ml-sm">
          <q-menu>
            <q-list style="min-width: 100px">
              <q-item clickable v-close-popup @click="logout">
                <q-item-section>Logout</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
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
          v-for="link in links"
          :key="link.title"
          clickable
          v-ripple
          :to="`/business/${$route.params.businessId}${link.link}`"
          exact
        >
          <q-item-section avatar>
            <q-icon :name="link.icon" />
          </q-item-section>
          <q-item-section>{{ link.title }}</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth'

export default {
  name: 'AdminLayout',

  setup () {
    const leftDrawerOpen = ref(false)
    const router = useRouter()
    const authStore = useAuthStore()

    const links = [
      {
        title: 'Dashboard',
        icon: 'dashboard',
        link: '/dashboard'
      },
      
      {
        title: 'Branches',
        icon: 'store',
        link: '/branches'
      },
      {
        title: 'Warehouses',
        icon: 'warehouse',
        link: '/warehouses'
      },
      {
        title: 'Inventory',
        icon: 'inventory_2',
        link: '/inventory'
      },
      {
        title: 'Categories',
        icon: 'category',
        link: '/categories'
      },
      {
        title: 'Staff',
        icon: 'people',
        link: '/staff'
      },
      {
        title: 'Sales',
        icon: 'point_of_sale',
        link: '/sales'
      },
      {
        title: 'Refunds',
        icon: 'assignment_return',
        link: '/refunds'
      },
      {
        title: 'Reports',
        icon: 'assessment',
        link: '/reports'
      },
      {
        title: 'Settings',
        icon: 'settings',
        link: '/settings'
      }
    ]

    const toggleLeftDrawer = () => {
      leftDrawerOpen.value = !leftDrawerOpen.value
    }

    const logout = async () => {
      await authStore.logout()
      router.push('/auth/login')
    }

    return {
      leftDrawerOpen,
      links,
      toggleLeftDrawer,
      logout
    }
  }
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

.q-item {
  border-radius: 0 24px 24px 0;
  margin-right: 12px;
  margin-bottom: 4px;

  &.q-router-link-active {
    background: var(--primary-color);
    color: white;
  }
}
</style>
