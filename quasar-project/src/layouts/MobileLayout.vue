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
          {{ currentPageTitle }}
        </q-toolbar-title>

        <q-btn flat round dense icon="notifications" class="q-ml-sm">
          <q-badge color="red" floating>2</q-badge>
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
          v-for="link in essentialLinks"
          :key="link.title"
          v-ripple
          clickable
          :to="getRouteWithParams(link.link)"
          :active="currentRoute === link.link"
          class="nav-link"
        >
          <q-item-section avatar>
            <q-icon :name="link.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ link.title }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition
          name="page"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>

    <!-- Futuristic Custom Bottom Nav -->
    <div class="futuristic-bottom-nav">
      <div class="nav-indicator" :style="indicatorStyle"></div>
      <div class="nav-tabs">
        <div
          v-for="(item, idx) in bottomNavItems"
          :key="item.title"
          :class="['nav-tab', { active: isActive(item) }]"
          @click="goTo(item, idx)"
          ref="tabRefs"
        >
          <transition name="tab-glow">
            <div v-if="isActive(item)" class="tab-glow"></div>
          </transition>
          <q-icon
            :name="item.icon"
            :color="isActive(item) ? accentColor : 'white'"
            size="28px"
            class="tab-icon"
          />
        </div>
      </div>
    </div>
  </q-layout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const leftDrawerOpen = ref(false)
const currentRoute = ref(route.path)
const accentColor = '#00FFC6'

const currentPageTitle = computed(() => {
  const path = route.path
  if (path.includes('/dashboard')) return 'Dashboard'
  if (path.includes('/inventory')) return 'Inventory'
  if (path.includes('/sales')) return 'Sales'
  if (path.includes('/reports')) return 'Reports'
  if (path.includes('/more')) return 'More'
  return 'POS App'
})

const bottomNavItems = [
  {
    title: 'Home',
    icon: 'home',
    link: '/branch/:businessId/:branchId/dashboard'
  },
  {
    title: 'Sales',
    icon: 'shopping_cart',
    link: '/branch/:businessId/:branchId/sales'
  },
  {
    title: 'Inventory',
    icon: 'inventory_2',
    link: '/branch/:businessId/:branchId/inventory'
  },
  {
    title: 'Reports',
    icon: 'assessment',
    link: '/branch/:businessId/:branchId/reports'
  },
  {
    title: 'More',
    icon: 'more_horiz',
    link: '/branch/:businessId/:branchId/more'
  }
]

const essentialLinks = [
  {
    title: 'Dashboard',
    icon: 'dashboard',
    link: '/branch/:businessId/:branchId/dashboard'
  },
  {
    title: 'Inventory',
    icon: 'inventory_2',
    link: '/branch/:businessId/:branchId/inventory'
  },
  {
    title: 'Sales',
    icon: 'shopping_cart',
    link: '/branch/:businessId/:branchId/sales'
  },
  {
    title: 'Customers',
    icon: 'people',
    link: '/branch/:businessId/:branchId/customers'
  },
  {
    title: 'Staff',
    icon: 'group',
    link: '/branch/:businessId/:branchId/staff'
  },
  {
    title: 'Reports',
    icon: 'assessment',
    link: '/branch/:businessId/:branchId/reports'
  },
  {
    title: 'Settings',
    icon: 'settings',
    link: '/branch/:businessId/:branchId/settings'
  }
]

const tabRefs = ref([])
const activeIdx = ref(getActiveIdx())

function getRouteWithParams(routePath) {
  return routePath
    .replace(':businessId', route.params.businessId)
    .replace(':branchId', route.params.branchId)
}

function isActive(item) {
  return route.path.startsWith(getRouteWithParams(item.link))
}

function getActiveIdx() {
  return bottomNavItems.findIndex(item => isActive(item)) || 0
}

function goTo(item, idx) {
  const path = getRouteWithParams(item.link)
  if (route.path !== path) {
    router.push(path)
    activeIdx.value = idx
    nextTick(() => updateIndicator())
  }
}

function updateIndicator() {
  // This will trigger the computed indicatorStyle to update
}

const indicatorStyle = computed(() => {
  // Calculate the left offset and width of the active tab
  const idx = getActiveIdx()
  const tabEls = tabRefs.value
  if (!tabEls[idx]) return {}
  const el = tabEls[idx].$el || tabEls[idx]
  const rect = el.getBoundingClientRect()
  const parentRect = el.parentNode.getBoundingClientRect()
  return {
    left: `${rect.left - parentRect.left}px`,
    width: `${rect.width}px`
  }
})

onMounted(() => {
  nextTick(() => updateIndicator())
})

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style lang="scss">
.futuristic-bottom-nav {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 18px;
  z-index: 100;
  display: flex;
  justify-content: center;
  pointer-events: none;
  user-select: none;
}

.nav-tabs {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(24, 26, 32, 0.95);
  border-radius: 40px;
  box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
  padding: 0 18px;
  height: 64px;
  min-width: 320px;
  max-width: 95vw;
  width: 400px;
  position: relative;
  pointer-events: all;
}

.nav-tab {
  position: relative;
  flex: 1 1 0;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 64px;
  cursor: pointer;
  z-index: 2;
  transition: color 0.25s, filter 0.25s, transform 0.25s;
}

.tab-icon {
  filter: drop-shadow(0 2px 8px rgba(0,0,0,0.18));
  transition: color 0.3s, filter 0.3s, transform 0.3s;
}

.nav-tab.active .tab-icon {
  color: #00FFC6 !important;
  filter: drop-shadow(0 0 8px #00FFC6);
  transform: scale(1.18);
}

.tab-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 44px;
  height: 44px;
  background: rgba(0,255,198,0.12);
  border-radius: 50%;
  box-shadow: 0 0 16px 4px #00FFC6;
  transform: translate(-50%, -50%) scale(1.1);
  z-index: -1;
  pointer-events: none;
  animation: glow-pop 0.4s;
}

@keyframes glow-pop {
  0% { opacity: 0; transform: translate(-50%, -50%) scale(0.7); }
  60% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
  100% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
}

.nav-indicator {
  position: absolute;
  bottom: 0;
  height: 4px;
  background: linear-gradient(90deg, #00FFC6 0%, #00BFFF 100%);
  border-radius: 2px;
  box-shadow: 0 0 8px #00FFC6;
  transition: left 0.35s cubic-bezier(.68,-0.55,.27,1.55), width 0.35s cubic-bezier(.68,-0.55,.27,1.55);
  z-index: 1;
}

.glass-footer {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.1);
}

.bottom-nav {
  .q-tab {
    min-height: 60px;
    transition: all 0.3s ease;
    
    &__icon {
      font-size: 24px;
      transition: transform 0.3s ease;
    }
    
    &__label {
      font-size: 12px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    &--active {
      .q-tab__icon {
        transform: translateY(-2px);
      }
      
      .q-tab__label {
        transform: translateY(-2px);
        opacity: 1;
      }
    }
    
    &:not(.q-tab--active) {
      .q-tab__label {
        opacity: 0.7;
      }
    }
  }
}

.nav-link {
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(0, 0, 0, 0.05);
  }
  
  &.q-router-link-active {
    background: rgba(0, 0, 0, 0.1);
  }
}

// Page transitions
.page-enter-active,
.page-leave-active {
  transition: all 0.3s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.page-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

// Glass effect for header
.q-header {
  background: rgba(0, 0, 0, 0.8) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

// Drawer glass effect
.q-drawer {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-right: 1px solid rgba(255, 255, 255, 0.3);
}
</style> 