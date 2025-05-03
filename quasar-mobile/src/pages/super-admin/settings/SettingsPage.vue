<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">System Settings</div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- General Settings -->
      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">General Settings</div>
            <q-form @submit="onSubmitGeneral" class="q-gutter-md">
              <q-input
                v-model="generalSettings.systemName"
                label="System Name"
                outlined
                class="glass-input"
              />
              <q-input
                v-model="generalSettings.systemEmail"
                label="System Email"
                type="email"
                outlined
                class="glass-input"
              />
              <q-input
                v-model="generalSettings.supportPhone"
                label="Support Phone"
                outlined
                class="glass-input"
              />
              <q-input
                v-model="generalSettings.supportEmail"
                label="Support Email"
                type="email"
                outlined
                class="glass-input"
              />
              <div class="row justify-end">
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save General Settings"
                  :loading="loading.general"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Security Settings -->
      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Security Settings</div>
            <q-form @submit="onSubmitSecurity" class="q-gutter-md">
              <q-input
                v-model="securitySettings.sessionTimeout"
                label="Session Timeout (minutes)"
                type="number"
                outlined
                class="glass-input"
              />
              <q-input
                v-model="securitySettings.maxLoginAttempts"
                label="Max Login Attempts"
                type="number"
                outlined
                class="glass-input"
              />
              <q-toggle
                v-model="securitySettings.enableTwoFactor"
                label="Enable Two-Factor Authentication"
                color="primary"
              />
              <q-toggle
                v-model="securitySettings.requireStrongPassword"
                label="Require Strong Passwords"
                color="primary"
              />
              <div class="row justify-end">
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Security Settings"
                  :loading="loading.security"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Notification Settings -->
      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Notification Settings</div>
            <q-form @submit="onSubmitNotifications" class="q-gutter-md">
              <q-toggle
                v-model="notificationSettings.emailNotifications"
                label="Enable Email Notifications"
                color="primary"
              />
              <q-toggle
                v-model="notificationSettings.smsNotifications"
                label="Enable SMS Notifications"
                color="primary"
              />
              <q-toggle
                v-model="notificationSettings.systemNotifications"
                label="Enable System Notifications"
                color="primary"
              />
              <div class="row justify-end">
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Notification Settings"
                  :loading="loading.notifications"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Backup Settings -->
      <div class="col-12 col-md-6">
        <q-card class="glass-card">
          <q-card-section>
            <div class="text-h6 q-mb-md">Backup Settings</div>
            <q-form @submit="onSubmitBackup" class="q-gutter-md">
              <q-toggle
                v-model="backupSettings.autoBackup"
                label="Enable Automatic Backups"
                color="primary"
              />
              <q-input
                v-model="backupSettings.backupFrequency"
                label="Backup Frequency (hours)"
                type="number"
                outlined
                class="glass-input"
                :disable="!backupSettings.autoBackup"
              />
              <q-input
                v-model="backupSettings.retentionPeriod"
                label="Retention Period (days)"
                type="number"
                outlined
                class="glass-input"
                :disable="!backupSettings.autoBackup"
              />
              <div class="row justify-end">
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Backup Settings"
                  :loading="loading.backup"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// State
const loading = ref({
  general: false,
  security: false,
  notifications: false,
  backup: false
})

const generalSettings = ref({
  systemName: '',
  systemEmail: '',
  supportPhone: '',
  supportEmail: ''
})

const securitySettings = ref({
  sessionTimeout: 30,
  maxLoginAttempts: 5,
  enableTwoFactor: false,
  requireStrongPassword: true
})

const notificationSettings = ref({
  emailNotifications: true,
  smsNotifications: false,
  systemNotifications: true
})

const backupSettings = ref({
  autoBackup: true,
  backupFrequency: 24,
  retentionPeriod: 30
})

// Methods
const fetchSettings = async () => {
  try {
    // TODO: Replace with actual API call
    // const response = await api.get('/super-admin/settings')
    // const settings = response.data
    // generalSettings.value = settings.general
    // securitySettings.value = settings.security
    // notificationSettings.value = settings.notifications
    // backupSettings.value = settings.backup
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load settings' + err,
      icon: 'error'
    })
  }
}

const onSubmitGeneral = async () => {
  loading.value.general = true
  try {
    // TODO: Replace with actual API call
    // await api.put('/super-admin/settings/general', generalSettings.value)
    $q.notify({
      color: 'positive',
      message: 'General settings updated successfully',
      icon: 'check_circle'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update general settings' + err,
      icon: 'error'
    })
  } finally {
    loading.value.general = false
  }
}

const onSubmitSecurity = async () => {
  loading.value.security = true
  try {
    // TODO: Replace with actual API call
    // await api.put('/super-admin/settings/security', securitySettings.value)
    $q.notify({
      color: 'positive',
      message: 'Security settings updated successfully',
      icon: 'check_circle'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update security settings' + err,
      icon: 'error'
    })
  } finally {
    loading.value.security = false
  }
}

const onSubmitNotifications = async () => {
  loading.value.notifications = true
  try {
    // TODO: Replace with actual API call
    // await api.put('/super-admin/settings/notifications', notificationSettings.value)
    $q.notify({
      color: 'positive',
      message: 'Notification settings updated successfully',
      icon: 'check_circle'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update notification settings' + err,
      icon: 'error'
    })
  } finally {
    loading.value.notifications = false
  }
}

const onSubmitBackup = async () => {
  loading.value.backup = true
  try {
    // TODO: Replace with actual API call
    // await api.put('/super-admin/settings/backup', backupSettings.value)
    $q.notify({
      color: 'positive',
      message: 'Backup settings updated successfully',
      icon: 'check_circle'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to update backup settings' + err,
      icon: 'error'
    })
  } finally {
    loading.value.backup = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<style lang="scss" scoped>
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px var(--glass-shadow);
  }
}

.glass-input {
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  border-radius: 8px;
}
</style> 