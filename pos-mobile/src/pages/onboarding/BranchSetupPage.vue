<template>
  <q-page class="branch-setup-page flex flex-center">
    <div class="branch-setup-container">
      <div class="text-center q-mb-lg">
        <h2 class="text-h4 text-white q-mb-sm">Branch Setup</h2>
        <p class="text-subtitle1 text-grey-4">Set up your first branch</p>
      </div>

      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-input
          v-model="form.branchName"
          label="Branch Name"
          :rules="rules.branchName"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="store" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.email"
          type="email"
          label="Branch Email"
          :rules="rules.email"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="mail" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.phone"
          label="Branch Phone"
          :rules="rules.phone"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="phone" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.address"
          type="textarea"
          label="Branch Address"
          :rules="rules.address"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="location_on" color="grey-4" />
          </template>
        </q-input>

        <q-select
          v-model="form.type"
          :options="branchTypes"
          label="Branch Type"
          :rules="rules.type"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="category" color="grey-4" />
          </template>
        </q-select>

        <q-input
          v-model="form.openingHours"
          label="Opening Hours"
          :rules="[val => !!val || 'Opening hours are required']"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="schedule" color="grey-4" />
          </template>
        </q-input>

        <q-btn
          type="submit"
          color="primary"
          label="Complete Setup"
          class="full-width q-py-sm"
          size="lg"
          :loading="loading"
        />
      </q-form>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useOnboardingStore } from 'stores/onboarding'

export default defineComponent({
  name: 'BranchSetupPage',
  emits: ['update:step'],
  setup(props, { emit }) {
    const $q = useQuasar()
    const router = useRouter()
    const onboardingStore = useOnboardingStore()
    const loading = ref(false)

    const form = ref({
      branchName: onboardingStore.branchData.branchName || '',
      email: onboardingStore.branchData.email || '',
      phone: onboardingStore.branchData.phone || '',
      address: onboardingStore.branchData.address || '',
      type: onboardingStore.branchData.type || '',
      openingHours: onboardingStore.branchData.openingHours || {
        monday: { open: '09:00', close: '17:00' },
        tuesday: { open: '09:00', close: '17:00' },
        wednesday: { open: '09:00', close: '17:00' },
        thursday: { open: '09:00', close: '17:00' },
        friday: { open: '09:00', close: '17:00' },
        saturday: { open: '10:00', close: '15:00' },
        sunday: { open: '', close: '' }
      }
    })

    const rules = {
      branchName: [
        val => !!val || 'Branch name is required'
      ],
      email: [
        val => !!val || 'Email is required',
        val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email format'
      ],
      phone: [
        val => !!val || 'Phone number is required'
      ],
      address: [
        val => !!val || 'Address is required'
      ],
      type: [
        val => !!val || 'Branch type is required'
      ]
    }

    onMounted(() => {
      emit('update:step', 4)
      onboardingStore.setCurrentStep(4)
    })

    const onSubmit = async () => {
      loading.value = true
      try {
        // TODO: Implement branch setup
        await new Promise(resolve => setTimeout(resolve, 1000))
        onboardingStore.setBranchData(form.value)
        onboardingStore.setBranchData({ completed: true })
        $q.notify({
          color: 'positive',
          message: 'Branch setup completed successfully',
          position: 'top'
        })
        // Redirect to role-specific dashboard
        const userRole = onboardingStore.registrationData.role
        router.push(`/${userRole}/dashboard`)
      } catch {
        $q.notify({
          color: 'negative',
          message: 'Failed to setup branch',
          position: 'top'
        })
      } finally {
        loading.value = false
      }
    }

    const branchTypes = [
      'Main Branch',
      'Subsidiary',
      'Franchise',
      'Outlet'
    ]

    return {
      form,
      rules,
      loading,
      branchTypes,
      onSubmit
    }
  }
})
</script>

<style lang="scss" scoped>
.branch-setup-page {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
  padding-top: 4rem;
}

.branch-setup-container {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
}

.custom-input {
  :deep(.q-field__control) {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
  }

  :deep(.q-field__label) {
    color: rgba(255, 255, 255, 0.7);
  }

  :deep(.q-field__native) {
    color: white;
  }
}
</style> 