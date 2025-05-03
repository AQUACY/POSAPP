<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h4">Create Business</div>
      <q-btn
        color="primary"
        icon="arrow_back"
        label="Back to Businesses"
        to="/super-admin/businesses"
      />
    </div>

    <q-form @submit="onSubmit" class="q-gutter-md">
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
                    <img :src="logoPreview" v-if="logoPreview">
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
          label="Create Business"
          type="submit"
          color="primary"
          :loading="loading"
        />
      </div>
    </q-form>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { businessService } from '../../../services/business'

const $q = useQuasar()
const router = useRouter()

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

const loading = ref(false)
const logoPreview = ref(null)

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
    
    // Create business
    const business = await businessService.createBusiness({
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
      await businessService.uploadLogo(business.id, form.value.logo)
    }

    $q.notify({
      color: 'positive',
      message: 'Business created successfully',
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