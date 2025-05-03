<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container class="auth-container">
      <div class="row full-width items-center justify-center">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
          <div class="q-pa-md">
            <!-- Logo and Branding -->
            <div class="text-center q-mb-xl">
              <q-avatar size="80px" class="q-mb-md">
                <img src="../assets/quasar-logo-vertical.svg" alt="Logo">
              </q-avatar>
              <div class="text-h4 text-weight-bold text-gradient">POS System</div>
              <div class="text-subtitle1 text-grey-7">Point of Sale Management</div>
            </div>

            <!-- Auth Illustration -->
            <div class="auth-illustration q-mb-xl">
              <img
                :src="illustration"
                alt="Authentication"
                class="full-width"
              >
            </div>

            <!-- Auth Form Card -->
            <q-card class="auth-card">
              <router-view />
            </q-card>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Get the appropriate illustration based on the current route
const illustration = computed(() => {
  if (route.path.includes('login')) {
    return 'https://cdn.jsdelivr.net/gh/creativetimofficial/public-assets@master/argon-dashboard-pro-vuejs/illustrations/login.svg'
  }
  return 'https://cdn.jsdelivr.net/gh/creativetimofficial/public-assets@master/argon-dashboard-pro-vuejs/illustrations/register.svg'
})
</script>

<style lang="scss" scoped>
.auth-container {
  background: var(--gradient-primary);
  min-height: 100vh;
}

.auth-card {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 0 8px 32px var(--glass-shadow);
  padding: 40px;
  animation: fadeIn 0.5s ease;
}

.auth-illustration {
  img {
    max-width: 100%;
    height: auto;
    animation: fadeIn 0.5s ease;
  }
}
</style>
