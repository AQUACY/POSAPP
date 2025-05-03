<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-card>
          <q-card-section class="row items-center">
            <div class="text-h6">Staff Management</div>
            <q-space />
            <q-btn color="primary" icon="add" label="Add Staff" @click="openAddDialog" />
          </q-card-section>

          <q-card-section>
            <q-table
              :rows="staff"
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
                  <q-btn flat round color="primary" icon="edit" @click="editStaff(props.row)" />
                  <q-btn flat round color="info" icon="assessment" @click="viewPerformance(props.row)" />
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
          <div class="text-h6">{{ isEdit ? 'Edit Staff' : 'Add New Staff' }}</div>
        </q-card-section>

        <q-card-section>
          <q-input v-model="form.name" label="Staff Name" />
          <q-input v-model="form.email" label="Email" type="email" />
          <q-input v-model="form.phone" label="Phone" />
          <q-select
            v-model="form.role"
            :options="roleOptions"
            label="Role"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Save" color="primary" @click="saveStaff" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Performance Dialog -->
    <q-dialog v-model="performanceDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Staff Performance</div>
        </q-card-section>

        <q-card-section v-if="selectedStaff">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <div class="text-subtitle2">Total Sales: ${{ selectedStaff.total_sales }}</div>
              <div class="text-subtitle2">Transactions: {{ selectedStaff.transactions }}</div>
              <div class="text-subtitle2">Average Rating: {{ selectedStaff.rating }}/5</div>
            </div>
            <div class="col-12">
              <q-table
                :rows="selectedStaff.recent_sales"
                :columns="performanceColumns"
                row-key="id"
                flat
                bordered
              />
            </div>
          </div>
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
  name: 'BranchManagerStaff',

  setup () {
    const store = useBranchManagerStore()
    const $q = useQuasar()
    const loading = ref(false)
    const filter = ref('')
    const dialog = ref(false)
    const performanceDialog = ref(false)
    const isEdit = ref(false)
    const selectedStaff = ref(null)
    const form = ref({
      name: '',
      email: '',
      phone: '',
      role: ''
    })

    const roleOptions = [
      { label: 'Cashier', value: 'cashier' },
      { label: 'Inventory Manager', value: 'inventory_manager' }
    ]

    const columns = [
      { name: 'id', label: 'ID', field: 'id', align: 'left' },
      { name: 'name', label: 'Name', field: 'name', align: 'left' },
      { name: 'email', label: 'Email', field: 'email', align: 'left' },
      { name: 'phone', label: 'Phone', field: 'phone', align: 'left' },
      { name: 'role', label: 'Role', field: 'role', align: 'left' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
    ]

    const performanceColumns = [
      { name: 'date', label: 'Date', field: 'date', align: 'left' },
      { name: 'amount', label: 'Amount', field: 'amount', align: 'right' },
      { name: 'items', label: 'Items', field: 'items', align: 'right' },
      { name: 'rating', label: 'Rating', field: 'rating', align: 'center' }
    ]

    const openAddDialog = () => {
      isEdit.value = false
      form.value = {
        name: '',
        email: '',
        phone: '',
        role: ''
      }
      dialog.value = true
    }

    const editStaff = (staff) => {
      isEdit.value = true
      form.value = { ...staff }
      dialog.value = true
    }

    const viewPerformance = (staff) => {
      selectedStaff.value = staff
      performanceDialog.value = true
    }

    const saveStaff = async () => {
      try {
        if (isEdit.value) {
          await store.updateCashier(form.value.id, form.value)
        } else {
          await store.createCashier(form.value)
        }
        dialog.value = false
        $q.notify({
          color: 'positive',
          message: `Staff ${isEdit.value ? 'updated' : 'added'} successfully`
        })
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Error saving staff'
        })
      }
    }

    onMounted(async () => {
      loading.value = true
      try {
        await store.fetchStaff()
      } catch (error) {
        console.error('Error loading staff:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      filter,
      dialog,
      performanceDialog,
      isEdit,
      form,
      selectedStaff,
      roleOptions,
      columns,
      performanceColumns,
      staff: computed(() => store.staff),
      openAddDialog,
      editStaff,
      viewPerformance,
      saveStaff
    }
  }
})
</script>
