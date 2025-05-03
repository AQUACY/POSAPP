<template>
  <q-dialog v-model="show" persistent>
    <q-card style="min-width: 400px">
      <q-card-section class="row items-center">
        <div class="text-h6">Process Payment</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <div class="row q-col-gutter-md">
          <!-- Sale Information -->
          <div class="col-12">
            <div class="text-subtitle1">Sale #{{ sale?.sale_number }}</div>
            <div class="text-caption">
              {{ new Date(sale?.created_at).toLocaleString() }}
            </div>
          </div>

          <!-- Payment Amount -->
          <div class="col-12">
            <q-input
              v-model="amount"
              type="number"
              label="Amount"
              :rules="[val => val > 0 || 'Amount must be greater than 0']"
              :disable="true"
            >
              <template v-slot:prepend>
                <q-icon name="attach_money" />
              </template>
            </q-input>
          </div>

          <!-- Payment Method -->
          <div class="col-12">
            <q-select
              v-model="paymentMethod"
              :options="paymentMethods"
              label="Payment Method"
              :rules="[val => !!val || 'Please select a payment method']"
            />
          </div>

          <!-- Reference Number -->
          <div class="col-12">
            <q-input
              v-model="referenceNumber"
              label="Reference Number"
              :rules="[val => !!val || 'Reference number is required']"
            >
              <template v-slot:prepend>
                <q-icon name="receipt" />
              </template>
            </q-input>
          </div>

          <!-- Notes -->
          <div class="col-12">
            <q-input
              v-model="notes"
              type="textarea"
              label="Notes"
              rows="3"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="negative" v-close-popup />
        <q-btn
          flat
          label="Process Payment"
          color="primary"
          :loading="loading"
          :disable="!isValid"
          @click="processPayment"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import { posService } from '../../services/pos.service'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  sale: {
    type: Object,
    required: true
  },
  businessId: {
    type: [String, Number],
    required: true
  },
  branchId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'payment-processed'])

const $q = useQuasar()

// State
const show = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const amount = ref(0)
const paymentMethod = ref(null)
const referenceNumber = ref('')
const notes = ref('')
const loading = ref(false)

// Payment methods
const paymentMethods = [
  { label: 'Cash', value: 'cash' },
  { label: 'Credit Card', value: 'credit_card' },
  { label: 'Ghana Payment', value: 'ghana_payment' }
]

// Validation
const isValid = computed(() => {
  const paymentMethodValue = paymentMethod.value?.value || paymentMethod.value
  const isCash = paymentMethodValue === 'cash'
  
  return amount.value > 0 &&
    paymentMethod.value &&
    (isCash || (referenceNumber.value && referenceNumber.value.length > 0))
})

// Watch for sale changes
watch(() => props.sale, (newSale) => {
  if (newSale) {
    console.log('Setting amount from sale:', {
      final_amount: newSale.final_amount,
      type: typeof newSale.final_amount
    })
    amount.value = Number(newSale.final_amount || 0)
  }
}, { immediate: true })

// Methods
const processPayment = async () => {
  if (!isValid.value) return

  try {
    loading.value = true
    const paymentMethodValue = paymentMethod.value?.value || paymentMethod.value
    const paymentAmount = Number(amount.value)
    const saleAmount = Number(props.sale.final_amount)
    
    console.log('Debug Amounts:', {
      saleAmount: saleAmount,
      saleAmountType: typeof saleAmount,
      paymentAmount: paymentAmount,
      paymentAmountType: typeof paymentAmount,
      saleAmountRounded: saleAmount.toFixed(2),
      paymentAmountRounded: paymentAmount.toFixed(2)
    })

    const paymentData = {
      amount: paymentAmount,
      payment_method: paymentMethodValue,
      notes: notes.value
    }

    // Only add reference for non-cash payments
    if (paymentMethodValue !== 'cash') {
      paymentData.reference = referenceNumber.value
    }

    await posService.processPayment(
      props.businessId,
      props.branchId,
      props.sale.id,
      paymentData
    )

    $q.notify({
      color: 'positive',
      message: 'Payment processed successfully',
      icon: 'check_circle'
    })

    emit('payment-processed')
    show.value = false
  } catch (error) {
    console.error('Error processing payment:', error)
    console.error('Error details:', {
      response: error.response?.data,
      sale: props.sale,
      amount: amount.value
    })
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Failed to process payment',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}
</script> 