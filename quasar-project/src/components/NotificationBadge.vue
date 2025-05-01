<template>
  <q-badge
    v-if="unreadCount > 0"
    color="red"
    floating
    transparent
  >
    {{ unreadCount }}
  </q-badge>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { posService } from '../services/pos.service'

const unreadCount = ref(0)
let intervalId = null

const loadUnreadCount = async () => {
  try {
    const response = await posService.getUnreadNotificationCount()
    unreadCount.value = response.data.count
  } catch (error) {
    console.error('Failed to load unread count:', error)
  }
}

onMounted(() => {
  loadUnreadCount()
  // Refresh count every 30 seconds
  intervalId = setInterval(loadUnreadCount, 30000)
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script> 