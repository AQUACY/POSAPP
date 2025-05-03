<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Edit Business</div>
      <q-btn
        color="primary"
        icon="arrow_back"
        label="Back to Businesses"
        to="/super-admin/businesses"
      />
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md" v-if="business">
      <div class="row q-col-gutter-md">
        <!-- Basic Information -->
        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Basic Information</div>
              
              <q-input
                v-model="form.name"
                label="Business Name"
                :rules="[val => !!val || 'Business name is required']"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[
                  val => !!val || 'Email is required',
                  val => isValidEmail(val) || 'Invalid email format'
                ]"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.phone"
                label="Phone"
                :rules="[val => !!val || 'Phone is required']"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.address"
                label="Address"
                type="textarea"
                :rules="[val => !!val || 'Address is required']"
                outlined
                class="q-mb-md"
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Additional Information -->
        <div class="col-12 col-md-6">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Additional Information</div>
              
              <q-input
                v-model="form.website"
                label="Website"
                type="url"
                outlined
                class="q-mb-md"
              />

              <q-input
                v-model="form.description"
                label="Description"
                type="textarea"
                outlined
                class="q-mb-md"
              />

              <q-select
                v-model="form.currency"
                :options="currencyOptions"
                label="Currency"
                :rules="[val => !!val || 'Currency is required']"
                outlined
                class="q-mb-md"
              />

              <q-select
                v-model="form.timezone"
                :options="timezoneOptions"
                label="Timezone"
                :rules="[val => !!val || 'Timezone is required']"
                outlined
                class="q-mb-md"
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Logo Upload -->
        <div class="col-12">
          <q-card class="glass-card">
            <q-card-section>
              <div class="text-h6 q-mb-md">Business Logo</div>
              
              <div class="row items-center q-gutter-md">
                <div class="col-auto">
                  <q-avatar size="100px">
                    <img :src="logoPreview || business.logo" v-if="logoPreview || business.logo">
                    <q-icon name="business" size="50px" v-else />
                  </q-avatar>
                </div>
                <div class="col">
                  <q-file
                    v-model="form.logo"
                    label="Upload Logo"
                    accept="image/*"
                    outlined
                    @update:model-value="onLogoSelected"
                  >
                    <template #prepend>
                      <q-icon name="attach_file" />
                    </template>
                  </q-file>
                  <div class="text-caption text-grey-7 q-mt-sm">
                    Recommended size: 200x200 pixels. Max file size: 2MB
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="row justify-end q-mt-lg">
        <q-btn
          label="Cancel"
          color="grey"
          class="q-mr-sm"
          to="/super-admin/businesses"
        />
        <q-btn
          label="Update Business"
          type="submit"
          color="primary"
          :loading="loading"
        />
      </div>
    </q-form>

    <div v-else class="text-center q-pa-lg">
      <q-spinner color="primary" size="3em" />
      <div class="text-h6 q-mt-md">Loading business details...</div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { businessService } from '../../../services/business'

const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// State
const business = ref(null)
const loading = ref(false)
const logoPreview = ref(null)

// Form state
const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  website: '',
  description: '',
  currency: '',
  timezone: '',
  logo: null
})

// Options
const currencyOptions = [
  { label: 'USD ($)', value: 'USD' },
  { label: 'EUR (€)', value: 'EUR' },
  { label: 'GBP (£)', value: 'GBP' },
  { label: 'GHS (₵)', value: 'GHS' }
]

const timezoneOptions = [
  { label: 'UTC', value: 'UTC' },
  { label: 'GMT', value: 'GMT' },
  { label: 'Africa/Accra', value: 'Africa/Accra' }
]

// Methods
const isValidEmail = (val) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
  return emailPattern.test(val) || 'Invalid email'
}

const fetchBusiness = async () => {
  try {
    loading.value = true
    business.value = await businessService.getBusiness(route.params.id)
    
    // Set form values
    form.value = {
      name: business.value.name,
      email: business.value.email,
      phone: business.value.phone,
      address: business.value.address,
      website: business.value.website,
      description: business.value.description,
      currency: business.value.currency,
      timezone: business.value.timezone,
      logo: null
    }
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
    router.push('/super-admin/businesses')
  } finally {
    loading.value = false
  }
}

const onLogoSelected = (file) => {
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      $q.notify({
        color: 'negative',
        message: 'Logo file size should not exceed 2MB',
        icon: 'error'
      })
      form.value.logo = null
      return
    }
    logoPreview.value = URL.createObjectURL(file)
  } else {
    logoPreview.value = null
  }
}

const onSubmit = async () => {
  try {
    loading.value = true
    
    // Update business
    const updatedBusiness = await businessService.updateBusiness(route.params.id, {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone,
      address: form.value.address,
      website: form.value.website,
      description: form.value.description,
      currency: form.value.currency,
      timezone: form.value.timezone
    })

    // Upload logo if selected
    if (form.value.logo) {
      await businessService.uploadLogo(updatedBusiness.id, form.value.logo)
    }

    $q.notify({
      color: 'positive',
      message: 'Business updated successfully',
      icon: 'check_circle'
    })

    router.push('/super-admin/businesses')
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: err.message,
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBusiness()
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
</style> 