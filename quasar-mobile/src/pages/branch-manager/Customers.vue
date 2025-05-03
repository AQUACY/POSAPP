<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-card>
          <q-card-section class="row items-center">
            <div class="text-h6">Customer Management</div>
            <q-space />
            <q-btn color="primary" icon="add" label="Add Customer" @click="openAddDialog" />
          </q-card-section>

          <q-card-section>
            <q-table
              :rows="customers"
              :columns="columns"
              row-key="id"
              :loading="loading"
              :filter="filter"
              flat
              bordered
            >
              <template v-slot:top-right>
                <q-input
                  v-model="filter"
                  placeholder="Search"
                  dense
                  debounce="300"
                >
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </template>

              <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                  <q-btn flat round color="primary" icon="edit" @click="editCustomer(props.row)" />
                  <q-btn flat round color="info" icon="history" @click="viewHistory(props.row)" />
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ isEdit ? 'Edit Customer' : 'Add New Customer' }}</div>
        </q-card-section>

        <q-card-section>
          <q-input v-model="form.name" label="Customer Name" />
          <q-input v-model="form.email" label="Email" type="email" />
          <q-input v-model="form.phone" label="Phone" />
          <q-input v-model="form.address" label="Address" type="textarea" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Save" color="primary" @click="saveCustomer" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- History Dialog -->
    <q-dialog v-model="historyDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Purchase History</div>
        </q-card-section>

        <q-card-section v-if="selectedCustomer">
          <q-table
            :rows="selectedCustomer.purchases"
            :columns="historyColumns"
            row-key="id"
            flat
            bordered
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useBranchManagerStore } from 'stores/branchManager'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'BranchManagerCustomers',

  setup () {
    const store = useBranchManagerStore()
    const $q = useQuasar()
    const loading = ref(false)
    const filter = ref('')
    const dialog = ref(false)
    const historyDialog = ref(false)
    const isEdit = ref(false)
    const selectedCustomer = ref(null)
    const form = ref({
      name: '',
      email: '',
      phone: '',
      address: ''
    })

    const columns = [
      { name: 'id', label: 'ID', field: 'id', align: 'left' },
      { name: 'name', label: 'Name', field: 'name', align: 'left' },
      { name: 'email', label: 'Email', field: 'email', align: 'left' },
      { name: 'phone', label: 'Phone', field: 'phone', align: 'left' },
      { name: 'total_purchases', label: 'Total Purchases', field: 'total_purchases', align: 'right' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
    ]

    const historyColumns = [
      { name: 'date', label: 'Date', field: 'date', align: 'left' },
      { name: 'amount', label: 'Amount', field: 'amount', align: 'right' },
      { name: 'items', label: 'Items', field: 'items', align: 'right' }
    ]

    const openAddDialog = () => {
      isEdit.value = false
      form.value = {
        name: '',
        email: '',
        phone: '',
        address: ''
      }
      dialog.value = true
    }

    const editCustomer = (customer) => {
      isEdit.value = true
      form.value = { ...customer }
      dialog.value = true
    }

    const viewHistory = (customer) => {
      selectedCustomer.value = customer
      historyDialog.value = true
    }

    const saveCustomer = async () => {
      try {
        // Implement save customer
        dialog.value = false
        $q.notify({
          color: 'positive',
          message: `Customer ${isEdit.value ? 'updated' : 'added'} successfully`
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error saving customer'
        })
      }
    }

    onMounted(async () => {
      loading.value = true
      try {
        await store.fetchCustomers()
      } catch (error) {
        console.error('Error loading customers:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      filter,
      dialog,
      historyDialog,
      isEdit,
      form,
      selectedCustomer,
      columns,
      historyColumns,
      customers: computed(() => store.customers),
      openAddDialog,
      editCustomer,
      viewHistory,
      saveCustomer
    }
  }
})
</script>
