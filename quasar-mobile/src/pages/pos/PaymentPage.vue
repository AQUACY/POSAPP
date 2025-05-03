<template>
  <q-page class="q-pa-md">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">
        <q-card class="q-pa-md">
          <q-card-section>
            <div class="text-h5 text-center q-mb-md">Payment Details</div>
            
            <!-- Sale Information -->
            <div v-if="sale" class="q-mb-lg">
              <div class="text-subtitle1 q-mb-sm">Sale #{{ sale.sale_number }}</div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                  <q-list bordered>
                    <q-item v-for="item in sale.items" :key="item.id">
                      <q-item-section>
                        <q-item-label>{{ item.inventory.name }}</q-item-label>
                        <q-item-label caption>
                          {{ item.quantity }} x ${{ item.unit_price }}
                        </q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        ${{ item.total_amount }}
                      </q-item-section>
                    </q-item>
                  </q-list>
                </div>
                <div class="col-12">
                  <div class="row justify-between q-mt-md">
                    <div>Subtotal:</div>
                    <div>${{ sale.total_amount }}</div>
                  </div>
                  <div class="row justify-between">
                    <div>Tax:</div>
                    <div>${{ sale.tax_amount }}</div>
                  </div>
                  <q-separator class="q-my-sm" />
                  <div class="row justify-between text-h6">
                    <div>Total:</div>
                    <div>${{ sale.final_amount }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Form -->
            <div v-if="!paymentProcessed" class="q-mt-lg">
              <div class="text-subtitle1 q-mb-md">Complete Payment</div>
              
              <!-- Credit Card Payment -->
              <template v-if="sale?.payment_method === 'credit_card'">
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <q-input
                      v-model="cardNumber"
                      label="Card Number"
                      outlined
                      mask="#### #### #### ####"
                      :rules="[val => val.length === 19 || 'Invalid card number']"
                    />
                  </div>
                  <div class="col-6">
                    <q-input
                      v-model="expiryDate"
                      label="Expiry Date"
                      outlined
                      mask="##/##"
                      :rules="[val => /^\d{2}\/\d{2}$/.test(val) || 'Invalid expiry date']"
                    />
                  </div>
                  <div class="col-6">
                    <q-input
                      v-model="cvv"
                      label="CVV"
                      outlined
                      mask="###"
                      type="password"
                      :rules="[val => val.length === 3 || 'Invalid CVV']"
                    />
                  </div>
                </div>
              </template>

              <!-- Ghana Payment -->
              <template v-if="sale?.payment_method === 'ghana_payment'">
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <q-select
                      v-model="ghanaPaymentMethod"
                      :options="ghanaPaymentOptions"
                      label="Select Payment Method"
                      outlined
                    />
                  </div>
                  <div class="col-12">
                    <q-input
                      v-model="mobileNumber"
                      label="Mobile Number"
                      outlined
                      mask="### ### ####"
                      :rules="[val => val.length === 12 || 'Invalid mobile number']"
                    />
                  </div>
                </div>
              </template>

              <div class="row justify-center q-mt-lg">
                <q-btn
                  color="primary"
                  label="Process Payment"
                  :loading="loading"
                  :disable="!isPaymentFormValid"
                  @click="processPayment"
                />
              </div>
            </div>

            <!-- Payment Success -->
            <div v-else class="text-center q-mt-lg">
              <q-icon name="check_circle" color="positive" size="4rem" />
              <div class="text-h6 q-mt-md">Payment Successful!</div>
              <div class="q-mt-md">
                <q-btn
                  color="primary"
                  label="Print Receipt"
                  @click="printReceipt"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { posService } from '../../services/pos.service'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const sale = ref(null)
const loading = ref(false)
const paymentProcessed = ref(false)

// Payment form fields
const cardNumber = ref('')
const expiryDate = ref('')
const cvv = ref('')
const ghanaPaymentMethod = ref(null)
const mobileNumber = ref('')

const ghanaPaymentOptions = [
  { label: 'Mobile Money', value: 'mobile_money' },
  { label: 'Visa Card', value: 'visa' },
  { label: 'Mastercard', value: 'mastercard' }
]

// Computed
const isPaymentFormValid = computed(() => {
  if (!sale.value) return false

  if (sale.value.payment_method === 'credit_card') {
    return cardNumber.value.length === 19 &&
           /^\d{2}\/\d{2}$/.test(expiryDate.value) &&
           cvv.value.length === 3
  }

  if (sale.value.payment_method === 'ghana_payment') {
    return ghanaPaymentMethod.value && mobileNumber.value.length === 12
  }

  return false
})

// Methods
const loadSale = async () => {
  try {
    loading.value = true
    const response = await posService.getSale(
      route.params.businessId,
      route.params.branchId,
      route.params.saleId
    )
    sale.value = response.data
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to load sale details',
      icon: 'error'
    })
    router.push('/pos')
  } finally {
    loading.value = false
  }
}

const processPayment = async () => {
  try {
    loading.value = true
    await posService.processPayment(
      route.params.businessId,
      route.params.branchId,
      sale.value.id,
      {
        payment_method: sale.value.payment_method,
        amount: Number(sale.value.final_amount.toFixed(2)),
        reference: generatePaymentReference()
      }
    )
    paymentProcessed.value = true
    $q.notify({
      color: 'positive',
      message: 'Payment processed successfully',
      icon: 'check_circle'
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.message || 'Failed to process payment',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const printReceipt = () => {
  // Implement receipt printing logic
  window.print()
}

const generatePaymentReference = () => {
  return 'PAY-' + Date.now().toString(36).toUpperCase()
}

// Lifecycle
onMounted(() => {
  loadSale()
})
</script>

<style lang="scss" scoped>
@media print {
  .q-btn {
    display: none;
  }
}
</style> 