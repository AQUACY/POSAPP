<template>
  <q-page class="q-pa-md">
    <div class="row q-col-gutter-md">
      <!-- Products Grid -->
      <div class="col-8">
        <div class="text-h6 q-mb-md">Products</div>
        <div class="row q-col-gutter-md">
          <div v-for="product in products" :key="product.id" class="col-4">
            <q-card class="product-card" @click="addToCart(product)">
              <q-card-section>
                <div class="text-h6">{{ product.name }}</div>
                <div class="text-subtitle2">${{ product.price }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <!-- Cart -->
      <div class="col-4">
        <q-card class="cart-card">
          <q-card-section>
            <div class="text-h6">Cart</div>
          </q-card-section>

          <q-card-section>
            <q-list>
              <q-item v-for="item in cart" :key="item.id">
                <q-item-section>
                  <q-item-label>{{ item.name }}</q-item-label>
                  <q-item-label caption>${{ item.price }} x {{ item.quantity }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn flat round dense icon="remove" @click="removeFromCart(item)" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>

          <q-card-section>
            <div class="text-h6">Total: ${{ total }}</div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn
              color="primary"
              label="Checkout"
              :disable="cart.length === 0"
              @click="checkout"
            />
          </q-card-actions>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Sample products data
const products = ref([
  { id: 1, name: 'Product 1', price: 10.99 },
  { id: 2, name: 'Product 2', price: 15.99 },
  { id: 3, name: 'Product 3', price: 20.99 },
  { id: 4, name: 'Product 4', price: 25.99 },
  { id: 5, name: 'Product 5', price: 30.99 },
  { id: 6, name: 'Product 6', price: 35.99 }
])

const cart = ref([])

const total = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)
})

const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id)
  if (existingItem) {
    existingItem.quantity++
  } else {
    cart.value.push({
      ...product,
      quantity: 1
    })
  }
}

const removeFromCart = (item) => {
  const index = cart.value.findIndex(cartItem => cartItem.id === item.id)
  if (index > -1) {
    if (cart.value[index].quantity > 1) {
      cart.value[index].quantity--
    } else {
      cart.value.splice(index, 1)
    }
  }
}

const checkout = () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Would you like to proceed with checkout?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    // TODO: Implement checkout logic
    cart.value = []
    $q.notify({
      color: 'positive',
      message: 'Checkout successful'
    })
  })
}
</script>

<style lang="scss" scoped>
.product-card {
  cursor: pointer;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
}

.cart-card {
  position: sticky;
  top: 20px;
}
</style> 