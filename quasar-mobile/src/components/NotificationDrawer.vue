<template>
  <q-drawer
    v-model="drawer"
    side="right"
    bordered
    :width="300"
    :breakpoint="400"
  >
    <q-scroll-area class="fit">
      <div class="q-pa-md">
        <div class="row items-center justify-between">
          <div class="text-h6">Notifications</div>
          <q-btn
            flat
            dense
            icon="done_all"
            @click="markAllAsRead"
            :disable="!hasUnread"
          >
            <q-tooltip>Mark all as read</q-tooltip>
          </q-btn>
        </div>
      </div>

      <NotificationList />
    </q-scroll-area>
  </q-drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import NotificationList from './NotificationList.vue'
import { posService } from '../services/pos.service'

const $q = useQuasar()
const drawer = ref(false)
const hasUnread = ref(false)

const markAllAsRead = async () => {
  try {
    await posService.markAllNotificationsAsRead()
    hasUnread.value = false
    $q.notify({
      color: 'positive',
      message: 'All notifications marked as read',
      icon: 'check'
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to mark notifications as read',
      icon: 'error'
    })
  }
}

// Expose methods to parent components
defineExpose({
  toggle: () => drawer.value = !drawer.value,
  open: () => drawer.value = true,
  close: () => drawer.value = false
})
</script> 