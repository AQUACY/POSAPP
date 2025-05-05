<template>
  <q-page class="business-setup-page flex flex-center">
    <div class="business-setup-container">
      <div class="text-center q-mb-lg">
        <h2 class="text-h4 text-white q-mb-sm">Business Setup</h2>
        <p class="text-subtitle1 text-grey-4">Tell us about your business</p>
      </div>

      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-input
          v-model="form.businessName"
          label="Business Name"
          :rules="rules.businessName"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="business" color="grey-4" />
          </template>
        </q-input>

        <q-input
          v-model="form.email"
          type="email"
          label="Business Email"
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
          label="Phone Number"
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
          label="Business Address"
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
          v-model="form.businessType"
          :options="businessTypes"
          label="Business Type"
          :rules="rules.businessType"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="category" color="grey-4" />
          </template>
        </q-select>

        <q-input
          v-model="form.taxId"
          label="Tax ID"
          :rules="rules.taxId"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="receipt" color="grey-4" />
          </template>
        </q-input>

        <q-file
          v-model="form.logo"
          label="Business Logo"
          accept=".jpg, .jpeg, .png"
          dark
          outlined
          class="custom-input"
        >
          <template v-slot:prepend>
            <q-icon name="image" color="grey-4" />
          </template>
          <template v-slot:append>
            <q-icon
              v-if="form.logo"
              name="close"
              @click.stop="form.logo = null"
              class="cursor-pointer"
              color="grey-4"
            />
          </template>
        </q-file>

        <q-btn
          type="submit"
          color="primary"
          label="Continue"
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
import onboardingService from 'services/onboarding'

export default defineComponent({
  name: 'BusinessSetupPage',
  emits: ['update:step'],
  setup(props, { emit }) {
    const $q = useQuasar()
    const router = useRouter()
    const onboardingStore = useOnboardingStore()
    const loading = ref(false)

    const form = ref({
      businessName: onboardingStore.businessData.businessName || '',
      email: onboardingStore.businessData.email || '',
      phone: onboardingStore.businessData.phone || '',
      address: onboardingStore.businessData.address || '',
      businessType: onboardingStore.businessData.businessType || '',
      taxId: onboardingStore.businessData.taxId || '',
      logo: onboardingStore.businessData.logo || null
    })

    const rules = {
      businessName: [
        val => !!val || 'Business name is required'
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
      businessType: [
        val => !!val || 'Business type is required'
      ],
      taxId: [
        val => !!val || 'Tax ID is required'
      ]
    }

    const businessTypes = [
      'Retail',
      'Restaurant',
      'Service',
      'Manufacturing',
      'Other'
    ]

    onMounted(() => {
      emit('update:step', 3)
      onboardingStore.setCurrentStep(3)
    })

    const onSubmit = async () => {
      loading.value = true
      try {
        const response = await onboardingService.setupBusiness(form.value)
        onboardingStore.setBusinessData(form.value)
        onboardingStore.setBusinessData({ completed: true })
        $q.notify({
          color: 'positive',
          message: response.message || 'Business setup completed successfully',
          position: 'top'
        })
        router.push('/onboarding/setup-branch')
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Failed to setup business'
        $q.notify({
          color: 'negative',
          message: errorMessage,
          position: 'top'
        })
      } finally {
        loading.value = false
      }
    }

    const onLogoUpload = (files) => {
      if (files.length > 0) {
        form.value.logo = files[0]
      }
    }

    return {
      form,
      rules,
      loading,
      businessTypes,
      onSubmit,
      onLogoUpload
    }
  }
})
</script>

<style lang="scss" scoped>
.business-setup-page {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
  padding-top: 4rem;
}

.business-setup-container {
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