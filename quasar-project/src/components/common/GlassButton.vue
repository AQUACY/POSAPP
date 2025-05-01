<template>
  <q-btn
    v-bind="$attrs"
    class="glass-button"
    :class="[
      { 'text-gradient': textGradient },
      { 'full-width': fullWidth }
    ]"
  >
    <slot />
  </q-btn>
</template>

<script setup>
defineProps({
  textGradient: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})
</script>

<style lang="scss" scoped>
.glass-button {
  background: var(--gradient-primary);
  color: white;
  border: none;
  border-radius: 8px;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px var(--glass-shadow);
  }

  &.text-gradient {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
}
</style> 