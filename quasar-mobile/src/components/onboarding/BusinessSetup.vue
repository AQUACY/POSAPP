<template>
  <div class="business-setup">
    <q-form @submit="onSubmit" class="q-gutter-md">
      <div class="row q-col-gutter-md">
        <div class="col-12">
          <q-input
            v-model="form.business_name"
            label="Business Name"
            :rules="[val => !!val || 'Business name is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="business" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.business_type"
            label="Business Type"
            :rules="[val => !!val || 'Business type is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="category" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.address"
            label="Business Address"
            type="textarea"
            :rules="[val => !!val || 'Address is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="location_on" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.whatsapp_contact"
            label="WhatsApp Contact"
            :rules="[
              val => !!val || 'WhatsApp contact is required',
              val => isValidPhone(val) || 'Invalid phone number'
            ]"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="whatsapp" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.email"
            label="Business Email"
            type="email"
            :rules="[
              val => !!val || 'Email is required',
              val => isValidEmail(val) || 'Invalid email format'
            ]"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <q-input
            v-model="form.tax_id"
            label="Tax ID"
            :rules="[val => !!val || 'Tax ID is required']"
            outlined
          >
            <template v-slot:prepend>
              <q-icon name="receipt" />
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <div class="text-subtitle2 q-mb-sm">Business Logo</div>
          <q-file
            v-model="form.business_logo"
            label="Upload Logo"
            accept=".jpg,.jpeg,.png"
            :rules="[val => !!val || 'Logo is required']"
            outlined
            @update:model-value="onFileSelected"
          >
            <template v-slot:prepend>
              <q-icon name="image" />
            </template>
            <template v-slot:append>
              <q-icon name="close" @click.stop="form.business_logo = null" class="cursor-pointer" />
            </template>
          </q-file>
          <div v-if="logoPreview" class="q-mt-sm">
            <q-img
              :src="logoPreview"
              style="max-width: 200px"
              class="rounded-borders"
            />
          </div>
        </div>

        <div class="col-12">
          <div class="text-subtitle2 q-mb-sm">Receipt Settings</div>
          <q-input
            v-model="form.receipt_settings.header"
            label="Receipt Header"
            type="textarea"
            outlined
          />
          <q-input
            v-model="form.receipt_settings.footer"
            label="Receipt Footer"
            type="textarea"
            outlined
            class="q-mt-sm"
          />
        </div>

        <div class="col-12">
          <div class="text-subtitle2 q-mb-sm">Report Settings</div>
          <q-toggle
            v-model="form.report_settings.show_profit"
            label="Show Profit in Reports"
          />
          <q-toggle
            v-model="form.report_settings.show_tax"
            label="Show Tax in Reports"
            class="q-mt-sm"
          />
        </div>
      </div>

      <div class="row justify-end q-mt-md">
        <q-btn
          label="Save Business Details"
          type="submit"
          color="primary"
          :loading="loading"
          class="full-width"
        />
      </div>
    </q-form>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useQuasar } from 'quasar'
import  api from '../../services/api'

export default {
  name: 'BusinessSetup',
  emits: ['business-setup-complete'],
  setup (props, { emit }) {
    const $q = useQuasar()
    const loading = ref(false)
    const logoPreview = ref(null)

    const form = reactive({
      business_name: '',
      business_type: '',
      address: '',
      whatsapp_contact: '',
      email: '',
      tax_id: '',
      business_logo: null,
      settings: {
        currency: 'GHS',
        timezone: 'Africa/Accra',
        date_format: 'Y-m-d',
        time_format: 'H:i:s'
      },
      receipt_settings: {
        header: '',
        footer: '',
        show_logo: true,
        show_tax_id: true,
        show_address: true,
        show_contact: true
      },
      report_settings: {
        show_profit: true,
        show_tax: true,
        show_costs: true,
        show_margins: true,
        default_period: 'monthly'
      }
    })

    const isValidEmail = (email) => {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      return emailPattern.test(email)
    }

    const isValidPhone = (phone) => {
      return phone.length >= 10
    }

    const onFileSelected = (file) => {
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          logoPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
      } else {
        logoPreview.value = null
      }
    }

    const onSubmit = async () => {
      try {
        loading.value = true
        const formData = new FormData()
        
        // Append all form fields to FormData
        Object.keys(form).forEach(key => {
          if (key === 'settings' || key === 'receipt_settings' || key === 'report_settings') {
            formData.append(key, JSON.stringify(form[key]))
          } else {
            formData.append(key, form[key])
          }
        })

        // First create the business
        const businessResponse = await api.post('/onboarding/setup-business', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Then update the user with the business ID
        // await api.put('/onboarding/update-user-business', {
        //   business_id: businessResponse.data.business_id
        // })

        emit('business-setup-complete', businessResponse.data.business_id)
        
        $q.notify({
          type: 'positive',
          message: 'Business setup completed successfully!'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Business setup failed. Please try again.'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      logoPreview,
      isValidEmail,
      isValidPhone,
      onFileSelected,
      onSubmit
    }
  }
}
</script>

<style lang="scss" scoped>
.business-setup {
  padding: 20px 0;
}
</style> 