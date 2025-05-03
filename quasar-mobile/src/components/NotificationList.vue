<template>
  <div class="notification-list">
    <q-list bordered separator>
      <q-item v-for="notification in notifications" :key="notification.id" clickable @click="handleNotificationClick(notification)">
        <q-item-section>
          <q-item-label>{{ notification.data.message }}</q-item-label>
          <q-item-label caption>
            {{ formatDate(notification.created_at) }}
          </q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="notifications" :color="notification.read_at ? 'grey' : 'primary'" />
        </q-item-section>
      </q-item>
    </q-list>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { posService } from '../services/pos.service'

const $q = useQuasar()
const notifications = ref([])

const loadNotifications = async () => {
  try {
    const response = await posService.getNotifications()
    notifications.value = response.data
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load notifications',
      icon: 'error'
    })
  }
}

const handleNotificationClick = async (notification) => {
  try {
    // Mark notification as read
    await posService.markNotificationAsRead(notification.id)

    // Handle different notification types
    switch (notification.data.type) {
      case 'refund_request':
        // Navigate to refund details or open refund dialog
        // You can implement this based on your routing setup
        break
      // Add other notification types as needed
    }

    // Refresh notifications
    await loadNotifications()
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to process notification',
      icon: 'error'
    })
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(() => {
  loadNotifications()
})
</script>

<style scoped>
.notification-list {
  max-height: 400px;
  overflow-y: auto;
}
</style> 