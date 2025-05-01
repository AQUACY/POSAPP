<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Branch Details -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Branch Details</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="saveBranchDetails" class="q-gutter-md">
              <q-input
                v-model="form.name"
                label="Branch Name"
                :rules="[val => !!val || 'Branch name is required']"
              />

              <q-input
                v-model="form.address"
                label="Address"
                type="textarea"
                :rules="[val => !!val || 'Address is required']"
              />

              <q-input
                v-model="form.phone"
                label="Phone"
                :rules="[val => !!val || 'Phone is required']"
              />

              <q-input
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[
                  val => !!val || 'Email is required',
                  val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email format'
                ]"
              />

              <div>
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Changes"
                  :loading="loading"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Business Hours -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Business Hours</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="saveBusinessHours" class="q-gutter-md">
              <div v-for="(day, index) in businessHours" :key="index" class="row q-col-gutter-md">
                <div class="col-12 col-md-4">
                  <q-select
                    v-model="day.day"
                    :options="days"
                    label="Day"
                    outlined
                  />
                </div>
                <div class="col-12 col-md-4">
                  <q-input
                    v-model="day.open"
                    label="Open"
                    type="time"
                    outlined
                  />
                </div>
                <div class="col-12 col-md-4">
                  <q-input
                    v-model="day.close"
                    label="Close"
                    type="time"
                    outlined
                  />
                </div>
              </div>

              <div>
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Hours"
                  :loading="loading"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <!-- Notifications -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Notification Settings</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="saveNotificationSettings" class="q-gutter-md">
              <q-toggle
                v-model="notifications.lowStock"
                label="Low Stock Alerts"
              />
              <q-toggle
                v-model="notifications.sales"
                label="Sales Reports"
              />
              <q-toggle
                v-model="notifications.staff"
                label="Staff Performance"
              />

              <div>
                <q-btn
                  type="submit"
                  color="primary"
                  label="Save Settings"
                  :loading="loading"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useBranchManagerStore } from 'stores/branchManager'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'BranchManagerSettings',

  setup () {
    const store = useBranchManagerStore()
    const $q = useQuasar()
    const loading = ref(false)

    const form = ref({
      name: '',
      address: '',
      phone: '',
      email: ''
    })

    const days = [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday'
    ]

    const businessHours = ref(
      days.map(day => ({
        day,
        open: '09:00',
        close: '17:00'
      }))
    )

    const notifications = ref({
      lowStock: true,
      sales: true,
      staff: true
    })

    const saveBranchDetails = async () => {
      loading.value = true
      try {
        await store.updateBranchDetails(form.value)
        $q.notify({
          color: 'positive',
          message: 'Branch details updated successfully'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error updating branch details'
        })
      } finally {
        loading.value = false
      }
    }

    const saveBusinessHours = async () => {
      loading.value = true
      try {
        // Implement save business hours
        $q.notify({
          color: 'positive',
          message: 'Business hours updated successfully'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error updating business hours'
        })
      } finally {
        loading.value = false
      }
    }

    const saveNotificationSettings = async () => {
      loading.value = true
      try {
        // Implement save notification settings
        $q.notify({
          color: 'positive',
          message: 'Notification settings updated successfully'
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error updating notification settings'
        })
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      loading.value = true
      try {
        await store.fetchBranchDetails()
        form.value = { ...store.branchDetails }
      } catch (error) {
        console.error('Error loading branch details:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      form,
      days,
      businessHours,
      notifications,
      saveBranchDetails,
      saveBusinessHours,
      saveNotificationSettings
    }
  }
})
</script>
