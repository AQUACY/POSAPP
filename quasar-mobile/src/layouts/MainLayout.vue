<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
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
          POS App
        </q-toolbar-title>

        <q-btn flat round dense icon="person" class="q-ml-sm">
          <q-menu>
            <q-list style="min-width: 100px">
              <q-item clickable v-close-popup @click="onLogout">
                <q-item-section>
                  <q-item-label>Logout</q-item-label>
                </q-item-section>
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
    >
      <q-list>
        <q-item-label header>
          Navigation
        </q-item-label>

        <q-item
          v-for="link in links"
          :key="link.title"
          v-ripple
          clickable
          :to="link.link"
        >
          <q-item-section avatar>
            <q-icon :name="link.icon" />
          </q-item-section>
          <q-item-section>
            {{ link.title }}
          </q-item-section>
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
import { useQuasar } from 'quasar'
import { authService } from 'src/services/auth'

export default {
  name: 'MainLayout',
  setup() {
    const $q = useQuasar()
    const router = useRouter()
    const leftDrawerOpen = ref(false)

    const links = [
      {
        title: 'Dashboard',
        icon: 'dashboard',
        link: '/'
      },
      {
        title: 'Sales',
        icon: 'shopping_cart',
        link: '/sales'
      },
      {
        title: 'Inventory',
        icon: 'inventory',
        link: '/inventory'
      }
    ]

    const onLogout = async () => {
      try {
        await authService.logout()
        router.push('/login')
        $q.notify({
          color: 'positive',
          message: 'Logged out successfully'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error logging out'
        })
      }
    }

    return {
      leftDrawerOpen,
      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value
      },
      links,
      onLogout
    }
  }
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
