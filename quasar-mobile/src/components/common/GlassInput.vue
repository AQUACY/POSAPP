<template>
  <q-input
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    class="glass-input"
  >
    <template #prepend>
      <slot name="prepend" />
    </template>
    <template #append>
      <slot name="append" />
    </template>
    <template #before>
      <slot name="before" />
    </template>
    <template #after>
      <slot name="after" />
    </template>
    <template #hint>
      <slot name="hint" />
    </template>
  </q-input>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>

<style lang="scss" scoped>
.glass-input {
  .q-field__control {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 8px;
    transition: all 0.3s ease;

    &:hover {
      border-color: var(--accent-color);
    }
  }

  .q-field__label {
    color: var(--text-secondary);
  }
}
</style> 