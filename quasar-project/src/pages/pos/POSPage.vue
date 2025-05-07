<template>
  <q-page class="pos-page">
    <div class="row full-height">
      <!-- Left Side - Products Grid -->
      <div class="col-8 q-pa-md">
        <!-- Search and Categories -->
        <div class="row q-mb-md">
          <div class="col-8">
            <q-input
              v-model="search"
              dense
              outlined
              placeholder="Search products..."
              class="q-mr-sm"
            >
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          <div class="col-4">
            <q-select
              v-model="selectedCategory"
              :options="categories"
              dense
              outlined
              label="Category"
              emit-value
              map-options
            />
          </div>
        </div>

        <!-- Products Grid -->
        <div class="row q-col-gutter-md">
          <div v-for="product in filteredProducts" :key="product.id" class="col-3">
            <q-card class="product-card cursor-pointer" @click="addToCart(product)">
              <q-img
                :src="product.image || 'https://via.placeholder.com/150'"
                :ratio="1"
                class="product-image"
              />
              <q-card-section>
                <div class="text-subtitle2 text-weight-bold">{{ product.name }}</div>
                <div class="text-caption text-grey-8">{{ product.category?.name }}</div>
                <div class="text-h6 text-primary">${{ product.unit_price }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <!-- Right Side - Cart -->
      <div class="col-4 bg-grey-2 q-pa-md">
        <div class="text-h5 text-weight-bold q-mb-md">Current Order</div>

        <!-- Customer Selection with New Customer Button -->
        <div class="row q-mb-md">
          <div class="col-12">
            <div class="row q-col-gutter-sm">
              <div class="col">
                <q-select
                  v-model="customerId"
                  :options="customers"
                  label="Select Customer"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Customer is required']"
                  :loading="loading"
                  use-input
                  input-debounce="0"
                  @filter="filterCustomers"
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.name }}</q-item-label>
                        <q-item-label caption>
                          {{ scope.opt.email }} | {{ scope.opt.phone }}
                        </q-item-label>
                        <q-item-label caption class="text-grey-8">
                          {{ scope.opt.address }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        No customers found
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>
              <div class="col-auto">
                <q-btn
                  color="primary"
                  icon="person_add"
                  label="New Customer"
                  @click="showNewCustomerDialog = true"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div class="row q-mb-md">
          <div class="col-12">
            <q-input
              v-model="notes"
              label="Notes"
              type="textarea"
              outlined
              rows="2"
            />
          </div>
        </div>

        <!-- Cart Items -->
        <q-scroll-area style="height: calc(100vh - 300px)">
          <q-list>
            <q-item v-for="item in cart" :key="item.id" class="q-mb-sm">
              <q-item-section>
                <q-item-label>{{ item.name }}</q-item-label>
                <q-item-label caption>${{ item.price }} x {{ item.quantity }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="row items-center">
                  <q-btn flat dense icon="remove" @click="decreaseQuantity(item)" />
                  <span class="q-mx-sm">{{ item.quantity }}</span>
                  <q-btn flat dense icon="add" @click="increaseQuantity(item)" />
                  <q-btn flat dense icon="delete" color="negative" @click="removeFromCart(item)" />
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>

        <!-- Order Summary -->
        <div class="order-summary q-mt-md">
          <div class="row justify-between q-mb-sm">
            <div>Subtotal:</div>
            <div>${{ subtotal.toFixed(2) }}</div>
          </div>
          <div class="row justify-between q-mb-sm">
            <div>Tax (10%):</div>
            <div>${{ tax.toFixed(2) }}</div>
          </div>
          <q-separator class="q-my-md" />
          <div class="row justify-between text-h6">
            <div>Total:</div>
            <div>${{ total.toFixed(2) }}</div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="row q-col-gutter-md q-mt-md">
          <div class="col-6">
            <q-btn
              label="Clear"
              color="negative"
              class="full-width"
              @click="clearCart"
            />
          </div>
          <div class="col-6">
            <q-btn
              label="Checkout"
              color="primary"
              class="full-width"
              :disable="cart.length === 0"
              :loading="loading"
              @click="checkout"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Method Dialog -->
    <q-dialog v-model="showPaymentDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Payment Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <!-- Sale Number -->
            <div class="col-12">
              <q-input
                v-model="saleNumber"
                label="Sale Number"
                outlined
                readonly
              />
            </div>

            <!-- Payment Method Selection -->
            <div class="col-12">
              <q-select
                v-model="selectedPaymentMethod"
                :options="paymentOptions"
                label="Payment Method"
                outlined
                emit-value
                map-options
              >
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section avatar>
                      <q-icon :name="scope.opt.icon" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ scope.opt.label }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>

            <!-- Cash Payment Section -->
            <template v-if="selectedPaymentMethod === PAYMENT_METHODS.CASH">
              <div class="col-12">
                <q-input
                  v-model.number="paymentAmount"
                  type="number"
                  label="Amount Received"
                  outlined
                  :rules="[val => val >= total || 'Amount must be greater than total']"
                  @update:model-value="updatePaymentAmount"
                />
              </div>
              <div class="col-12">
                <q-input
                  v-model.number="changeAmount"
                  type="number"
                  label="Change"
                  outlined
                  readonly
                />
              </div>
            </template>

            <!-- Credit Card Section -->
            <template v-if="selectedPaymentMethod === PAYMENT_METHODS.CREDIT_CARD">
              <div class="col-12">
                <q-input
                  v-model="cardNumber"
                  label="Card Number"
                  outlined
                  mask="#### #### #### ####"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model="expiryDate"
                  label="Expiry Date"
                  outlined
                  mask="##/##"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model="cvv"
                  label="CVV"
                  outlined
                  mask="###"
                  type="password"
                />
              </div>
            </template>

            <!-- Ghana Payment Section -->
            <template v-if="selectedPaymentMethod === PAYMENT_METHODS.GHANA_PAYMENT">
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
                />
              </div>
            </template>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" v-close-popup />
          <q-btn
            flat
            label="Process Payment"
            color="primary"
            :loading="loading"
            :disable="!selectedPaymentMethod || (selectedPaymentMethod === PAYMENT_METHODS.CASH && paymentAmount < total)"
            @click="processPayment"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- New Customer Dialog -->
    <q-dialog v-model="showNewCustomerDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">New Customer</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-input
                v-model="newCustomer.name"
                label="Name"
                outlined
                :rules="[val => !!val || 'Name is required']"
              />
            </div>
            <div class="col-12">
              <q-input
                v-model="newCustomer.email"
                label="Email"
                type="email"
                outlined
                :rules="[
                  val => !!val || 'Email is required',
                  val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email format'
                ]"
              />
            </div>
            <div class="col-12">
              <q-input
                v-model="newCustomer.phone"
                label="Phone"
                outlined
                mask="### ### ####"
                :rules="[val => !!val || 'Phone is required']"
              />
            </div>
            <div class="col-12">
              <q-input
                v-model="newCustomer.address"
                label="Address"
                type="textarea"
                outlined
                rows="2"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" v-close-popup />
          <q-btn
            flat
            label="Create Customer"
            color="primary"
            :loading="loading"
            :disable="!newCustomer.name || !newCustomer.email || !newCustomer.phone"
            @click="createNewCustomer"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { posService } from '../../services/pos.service'

const $q = useQuasar()
const route = useRoute()

// Get business and branch IDs from route params
const businessId = computed(() => route.params.businessId)
const branchId = computed(() => route.params.branchId)

// State
const search = ref('')
const selectedCategory = ref(null)
const cart = ref([])
const products = ref([])
const categories = ref([])
const loading = ref(false)

// Add payment method constants
const PAYMENT_METHODS = {
  CASH: 'cash',
  CREDIT_CARD: 'credit_card',
  GHANA_PAYMENT: 'ghana_payment'
}

// Add payment method dialog state
const showPaymentDialog = ref(false)
const selectedPaymentMethod = ref(null)
const paymentAmount = ref(0)
const changeAmount = ref(0)
const customerId = ref(null)
const customers = ref([])
const notes = ref('')
const saleNumber = ref('')

// Add payment method options
const paymentOptions = [
  { label: 'Cash', value: PAYMENT_METHODS.CASH, icon: 'payments' },
  { label: 'Credit Card', value: PAYMENT_METHODS.CREDIT_CARD, icon: 'credit_card' },
  { label: 'Ghana Payment', value: PAYMENT_METHODS.GHANA_PAYMENT, icon: 'account_balance' }
]

// Add these to your script section
const showNewCustomerDialog = ref(false)
const newCustomer = ref({
  name: '',
  email: '',
  phone: '',
  address: ''
})

// Computed
const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(search.value.toLowerCase())
    const matchesCategory = !selectedCategory.value || product.category_id === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const subtotal = computed(() => {
  return Number(cart.value.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2))
})

const tax = computed(() => Number((subtotal.value * 0.1).toFixed(2)))

const total = computed(() => Number((subtotal.value + tax.value).toFixed(2)))

// Methods
const loadProducts = async () => {
  try {
    loading.value = true
    const [productsResponse, categoriesResponse] = await Promise.all([
      posService.getProducts(businessId.value, branchId.value),
      posService.getCategories(businessId.value, branchId.value)
    ])

    // Handle products data - products are in response.data.data
    products.value = productsResponse.data?.data || []

    // Handle categories data - categories are directly in response.data
    const categoriesData = categoriesResponse.data || []
    categories.value = [
      { label: 'All Categories', value: null },
      ...(Array.isArray(categoriesData) ? categoriesData.map(cat => ({
        label: cat.name,
        value: cat.id
      })) : [])
    ]

    console.log('Products:', products.value)
    console.log('Categories:', categories.value)
  } catch (error) {
    console.error('Error loading data:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to load products: ' + (error.message || 'Unknown error'),
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id)
  if (existingItem) {
    existingItem.quantity++
  } else {
    cart.value.push({
      ...product,
      price: parseFloat(product.unit_price), // Convert string price to number
      quantity: 1
    })
  }
}

const increaseQuantity = (item) => {
  item.quantity++
}

const decreaseQuantity = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  } else {
    removeFromCart(item)
  }
}

const removeFromCart = (item) => {
  const index = cart.value.findIndex(cartItem => cartItem.id === item.id)
  if (index !== -1) {
    cart.value.splice(index, 1)
  }
}

const clearCart = () => {
  $q.dialog({
    title: 'Clear Cart',
    message: 'Are you sure you want to clear the cart?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    cart.value = []
  })
}

const checkout = async () => {
  if (!customerId.value) {
    $q.notify({
      color: 'negative',
      message: 'Please select a customer',
      icon: 'error'
    })
    return
  }
  showPaymentDialog.value = true
  paymentAmount.value = total.value
  changeAmount.value = 0
}

// Add payment processing method
const processPayment = async () => {
  try {
    loading.value = true
    const saleData = {
      customer_id: customerId.value,
      items: cart.value.map(item => ({
        inventory_id: item.id,
        quantity: item.quantity,
        unit_price: Number(item.price.toFixed(2))
      })),
      subtotal: Number(subtotal.value.toFixed(2)),
      tax: Number(tax.value.toFixed(2)),
      total: Number(total.value.toFixed(2)),
      payment_method: selectedPaymentMethod.value,
      payment_amount: Number(paymentAmount.value.toFixed(2)),
      change_amount: Number(changeAmount.value.toFixed(2)),
      notes: notes.value || null
    }

    const createdSale = await posService.createSale(businessId.value, branchId.value, saleData)
    console.log('Sale created:', createdSale)

    // If payment is not cash, redirect to payment URL
    if (selectedPaymentMethod.value !== PAYMENT_METHODS.CASH) {
      if (createdSale.payment_url) {
        window.open(createdSale.payment_url, '_blank')
      }
    } else {
      // For cash payments, process immediately
      await posService.processPayment(businessId.value, branchId.value, createdSale.id, {
        payment_method: selectedPaymentMethod.value,
        amount: Number(createdSale.final_amount),
        reference: generatePaymentReference()
      })
    }

    $q.notify({
      color: 'positive',
      message: 'Order completed successfully',
      icon: 'check_circle'
    })
    cart.value = []
    showPaymentDialog.value = false
    customerId.value = null
    notes.value = ''
  } catch (error) {
    console.error('Error processing payment:', error)
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Failed to complete order',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

// Add method to generate payment reference
const generatePaymentReference = () => {
  return 'PAY-' + Date.now().toString(36).toUpperCase()
}

// Add method to load customers
const loadCustomers = async () => {
  try {
    const response = await posService.getCustomers(businessId.value, branchId.value)
    // Handle the paginated response structure
    customers.value = response.data.data.map(customer => ({
      label: customer.name,
      value: customer.id,
      name: customer.name,
      email: customer.email,
      phone: customer.phone,
      address: customer.address
    }))
  } catch (error) {
    console.error('Error loading customers:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to load customers',
      icon: 'error'
    })
  }
}

// Add this to your script section
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

// Add method to create new customer
const createNewCustomer = async () => {
  try {
    loading.value = true
    const response = await posService.createCustomer(businessId.value, branchId.value, {
      name: newCustomer.value.name,
      email: newCustomer.value.email,
      phone: newCustomer.value.phone,
      address: newCustomer.value.address,
      business_id: businessId.value
    })

    // Add new customer to the list
    const newCustomerData = {
      label: response.data.name,
      value: response.data.id,
      name: response.data.name,
      email: response.data.email,
      phone: response.data.phone
    }
    customers.value.push(newCustomerData)
    customerId.value = response.data.id

    // Close dialog and reset form
    showNewCustomerDialog.value = false
    newCustomer.value = {
      name: '',
      email: '',
      phone: '',
      address: ''
    }

    $q.notify({
      color: 'positive',
      message: 'Customer created successfully',
      icon: 'check_circle'
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.message || 'Failed to create customer',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

// Add customer filtering method
const filterCustomers = (val, update, abort) => {
  update(() => {
    const needle = val.toLowerCase()
    customers.value = customers.value.filter(customer =>
      customer.name.toLowerCase().includes(needle) ||
      customer.email.toLowerCase().includes(needle) ||
      customer.phone.includes(needle)
    )
  })
}

// Update the payment amount handling
const updatePaymentAmount = (val) => {
  paymentAmount.value = Number(val.toFixed(2))
  changeAmount.value = Number((val - total.value).toFixed(2))
}

// Lifecycle
onMounted(() => {
  if (!businessId.value || !branchId.value) {
    $q.notify({
      color: 'negative',
      message: 'Invalid business or branch ID',
      icon: 'error'
    })
    return
  }
  loadProducts()
  loadCustomers()
})
</script>

<style lang="scss" scoped>
.pos-page {
  height: calc(100vh - 50px);
}

.product-card {
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}

.product-image {
  border-radius: 8px 8px 0 0;
}

.order-summary {
  background: white;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
</style>