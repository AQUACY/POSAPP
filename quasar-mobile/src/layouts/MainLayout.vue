<template>
  <q-layout view="hHh lpR fFf" class="page-container">
    <!-- Header -->
    <q-header elevated class="glass-card">
      <q-toolbar>
        <q-btn dense flat round icon="menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="row items-center">
          <q-avatar size="32px" class="q-mr-sm">
            <img src="../assets/quasar-logo-vertical.svg" alt="Logo">
          </q-avatar>
          <span class="text-gradient">POS System</span>
        </q-toolbar-title>

        <div class="q-gutter-sm row items-center">
          <!-- Theme Toggle -->
          <q-btn round flat :icon="isDark ? 'light_mode' : 'dark_mode'" @click="toggleTheme">
            <q-tooltip>Toggle Theme</q-tooltip>
          </q-btn>

          <!-- Notifications -->
          <q-btn round flat icon="notifications">
            <q-badge color="red" floating>2</q-badge>
            <q-menu class="glass-card">
              <q-list style="min-width: 300px">
                <q-item v-for="n in 2" :key="n" clickable v-close-popup>
                  <q-item-section avatar>
                    <q-avatar color="primary" text-color="white">
                      <q-icon name="notifications" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Notification {{ n }}</q-item-label>
                    <q-item-label caption>2 minutes ago</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>

          <!-- User Menu -->
          <q-btn round flat>
            <q-avatar size="26px">
              <img src="https://cdn.quasar.dev/img/boy-avatar.png">
            </q-avatar>
            <q-menu class="glass-card">
              <q-list style="min-width: 200px">
                <q-item clickable v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="person" />
                  </q-item-section>
                  <q-item-section>Profile</q-item-section>
                </q-item>
                <q-item clickable v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="settings" />
                  </q-item-section>
                  <q-item-section>Settings</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="handleLogout">
                  <q-item-section avatar>
                    <q-icon name="logout" />
                  </q-item-section>
                  <q-item-section>Logout</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <!-- Left Drawer -->
    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      :width="240"
      :breakpoint="400"
      class="glass-card"
    >
      <q-scroll-area class="fit">
        <q-list padding>
          <q-item
            v-for="item in menuItems"
            :key="item.title"
            clickable
            v-ripple
            :to="item.link"
            exact
            class="menu-item"
          >
            <q-item-section avatar>
              <q-icon :name="item.icon" />
            </q-item-section>
            <q-item-section>
              {{ item.title }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <!-- Page Container -->
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
// import { useQuasar } from 'quasar'

// const $q = useQuasar()
const router = useRouter()
const leftDrawerOpen = ref(false)
const isDark = ref(false)

const menuItems = [
  {
    title: 'Dashboard',
    icon: 'dashboard',
    link: '/'
  },
  {
    title: 'POS Terminal',
    icon: 'point_of_sale',
    link: '/pos'
  },
  {
    title: 'Inventory',
    icon: 'inventory_2',
    link: '/inventory'
  },
  {
    title: 'Sales',
    icon: 'shopping_cart',
    link: '/sales'
  },
  {
    title: 'Customers',
    icon: 'people',
    link: '/customers'
  },
  {
    title: 'Reports',
    icon: 'analytics',
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

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.body.classList.toggle('dark-theme')
}

const handleLogout = () => {
  // TODO: Implement logout logic
  router.push('/auth/login')
}
</script>

<style lang="scss" scoped>
.menu-item {
  border-radius: 0 24px 24px 0;
  margin-right: 12px;
  margin-bottom: 4px;
  transition: all 0.3s ease;

  &.q-router-link--active {
    background: var(--gradient-primary);
    color: white;

    .q-icon {
      color: white;
    }
  }

  &:hover:not(.q-router-link--active) {
    background: var(--glass-bg);
  }
}

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
