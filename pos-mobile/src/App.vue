<template>
  <router-view v-slot="{ Component }">
    <transition
      appear
      enter-active-class="animated fadeIn"
      leave-active-class="animated fadeOut"
    >
      <component :is="Component" />
    </transition>
  </router-view>
</template>

<script setup>
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Set default Quasar configuration
$q.dark.set(true)
</script>

<style lang="scss">
// Import Quasar's default styles
@import 'quasar/dist/quasar.css';

// Global styles
body {
  margin: 0;
  padding: 0;
  font-family: 'Roboto', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

// Animation classes
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
