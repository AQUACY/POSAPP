<template>
  <q-card
    v-bind="$attrs"
    class="glass-card"
    :class="{ 'fade-in': fadeIn }"
  >
    <slot />
  </q-card>
</template>

<script setup>
defineProps({
  fadeIn: {
    type: Boolean,
    default: false
  }
})
</script>

<style lang="scss" scoped>
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  box-shadow: 0 8px 32px var(--glass-shadow);
  border-radius: 16px;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px var(--glass-shadow);
  }
}
</style> 