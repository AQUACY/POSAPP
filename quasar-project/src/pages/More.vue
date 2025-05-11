<template>
  <q-page class="q-pa-md">
    <div class="text-h6 q-mb-md">More Options</div>
    
    <q-list bordered separator>
      <q-item clickable v-ripple to="/mobile/customers">
        <q-item-section avatar>
          <q-icon name="people" color="primary" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Customers</q-item-label>
          <q-item-label caption>Manage your customers</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>
      </q-item>

      <q-item clickable v-ripple to="/mobile/settings">
        <q-item-section avatar>
          <q-icon name="settings" color="primary" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Settings</q-item-label>
          <q-item-label caption>App settings and preferences</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>
      </q-item>

      <q-item clickable v-ripple to="/mobile/profile">
        <q-item-section avatar>
          <q-icon name="person" color="primary" />
        </q-item-section>
        <q-item-section>
          <q-item-label>Profile</q-item-label>
          <q-item-label caption>Your account information</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>
      </q-item>

      <q-item clickable v-ripple @click="logout">
        <q-item-section avatar>
          <q-icon name="logout" color="negative" />
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-negative">Logout</q-item-label>
          <q-item-label caption>Sign out of your account</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>

    <div class="text-caption text-grey q-mt-lg q-mb-sm">App Information</div>
    <q-list bordered separator>
      <q-item>
        <q-item-section>
          <q-item-label>Version</q-item-label>
          <q-item-label caption>1.0.0</q-item-label>
        </q-item-section>
      </q-item>

      <q-item clickable v-ripple @click="openPrivacyPolicy">
        <q-item-section>
          <q-item-label>Privacy Policy</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>
      </q-item>

      <q-item clickable v-ripple @click="openTerms">
        <q-item-section>
          <q-item-label>Terms of Service</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" />
        </q-item-section>
      </q-item>
    </q-list>
  </q-page>
</template>

<script>
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

export default {
  name: 'MorePage',
  setup () {
    const $q = useQuasar()
    const router = useRouter()

    const logout = () => {
      $q.dialog({
        title: 'Confirm',
        message: 'Are you sure you want to logout?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        localStorage.removeItem('token')
        router.push('/login')
      })
    }

    const openPrivacyPolicy = () => {
      // Implement privacy policy view
      $q.notify('Privacy Policy coming soon')
    }

    const openTerms = () => {
      // Implement terms of service view
      $q.notify('Terms of Service coming soon')
    }

    return {
      logout,
      openPrivacyPolicy,
      openTerms
    }
  }
}
</script> 