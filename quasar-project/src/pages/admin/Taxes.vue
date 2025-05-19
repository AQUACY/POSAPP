<template>
    <q-page padding>
      <div class="row q-mb-md items-center justify-between">
        <div class="text-h6">Tax Management</div>
        <q-btn
          color="primary"
          icon="add"
          label="Add Tax"
          @click="openTaxDialog()"
        />
      </div>
  
      <!-- Tax List -->
      <q-table
        :rows="taxes"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination.sync="pagination"
      >
        <!-- Status Column -->
        <template v-slot:body-cell-status="props">
          <q-td :props="props">
            <q-toggle
              v-model="props.row.is_active"
              @update:model-value="toggleTaxStatus(props.row)"
              color="green"
            />
          </q-td>
        </template>
  
        <!-- Actions Column -->
        <template v-slot:body-cell-actions="props">
          <q-td :props="props" class="q-gutter-sm">
            <q-btn
              flat
              round
              color="primary"
              icon="edit"
              @click="openTaxDialog(props.row)"
            >
              <q-tooltip>Edit Tax</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              color="negative"
              icon="delete"
              @click="confirmDelete(props.row)"
            >
              <q-tooltip>Delete Tax</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
  
      <!-- Tax Dialog -->
      <q-dialog v-model="taxDialog" persistent>
        <q-card style="min-width: 350px">
          <q-card-section>
            <div class="text-h6">{{ isEditing ? 'Edit Tax' : 'Add New Tax' }}</div>
          </q-card-section>
  
          <q-card-section>
            <q-form @submit="saveTax" class="q-gutter-md">
              <q-input
                v-model="taxForm.name"
                label="Tax Name"
                :rules="[val => !!val || 'Tax name is required']"
              />
  
              <q-input
                v-model.number="taxForm.rate"
                label="Tax Rate (%)"
                type="number"
                :rules="[
                  val => !!val || 'Tax rate is required',
                  val => val >= 0 || 'Rate must be positive',
                  val => val <= 100 || 'Rate cannot exceed 100%'
                ]"
              />
  
              <q-input
                v-model.number="taxForm.order"
                label="Display Order"
                type="number"
                :rules="[
                  val => !!val || 'Order is required',
                  val => val >= 0 || 'Order must be positive'
                ]"
              />
  
              <div class="row justify-end q-mt-md">
                <q-btn
                  label="Cancel"
                  color="grey"
                  flat
                  v-close-popup
                  class="q-mr-sm"
                />
                <q-btn
                  label="Save"
                  type="submit"
                  color="primary"
                  :loading="saving"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-dialog>
  
      <!-- Delete Confirmation Dialog -->
      <q-dialog v-model="deleteDialog" persistent>
        <q-card>
          <q-card-section class="row items-center">
            <q-avatar icon="warning" color="negative" text-color="white" />
            <span class="q-ml-sm">Are you sure you want to delete this tax?</span>
          </q-card-section>
  
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="grey" v-close-popup />
            <q-btn flat label="Delete" color="negative" @click="deleteTax" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-page>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted } from 'vue'
  import { useQuasar } from 'quasar'
  import { api } from 'src/boot/axios'
  import { useRoute } from 'vue-router'
  
  export default defineComponent({
    name: 'TaxesPage',
  
    setup () {
      const $q = useQuasar()
      const route = useRoute()
      const loading = ref(false)
      const saving = ref(false)
      const taxes = ref([])
      const taxDialog = ref(false)
      const deleteDialog = ref(false)
      const isEditing = ref(false)
      const selectedTax = ref(null)
  
      const taxForm = ref({
        name: '',
        rate: 0,
        order: 0
      })
  
      const pagination = ref({
        sortBy: 'order',
        descending: false,
        page: 1,
        rowsPerPage: 10
      })
  
      const columns = [
        {
          name: 'name',
          required: true,
          label: 'Tax Name',
          align: 'left',
          field: row => row.name,
          sortable: true
        },
        {
          name: 'rate',
          required: true,
          label: 'Rate (%)',
          align: 'left',
          field: row => row.rate,
          sortable: true
        },
        {
          name: 'order',
          required: true,
          label: 'Order',
          align: 'left',
          field: row => row.order,
          sortable: true
        },
        {
          name: 'status',
          required: true,
          label: 'Status',
          align: 'left',
          field: row => row.is_active
        },
        {
          name: 'actions',
          required: true,
          label: 'Actions',
          align: 'right',
          field: row => row.id
        }
      ]
  
      const loadTaxes = async () => {
        try {
          loading.value = true
          const response = await api.get(`/admin/${route.params.businessId}/taxes`)
          taxes.value = response.data.taxes
        } catch (error) {
          $q.notify({
            color: 'negative',
            message: 'Failed to load taxes',
            icon: 'error'
          })
        } finally {
          loading.value = false
        }
      }
  
      const openTaxDialog = (tax = null) => {
        isEditing.value = !!tax
        if (tax) {
          taxForm.value = { ...tax }
          selectedTax.value = tax
        } else {
          taxForm.value = {
            name: '',
            rate: 0,
            order: taxes.value.length + 1
          }
          selectedTax.value = null
        }
        taxDialog.value = true
      }
  
      const saveTax = async () => {
        try {
          saving.value = true
          if (isEditing.value) {
            await api.put(`/admin/${route.params.businessId}/taxes/${selectedTax.value.id}`, taxForm.value)
            $q.notify({
              color: 'positive',
              message: 'Tax updated successfully',
              icon: 'check'
            })
          } else {
            await api.post(`/admin/${route.params.businessId}/taxes`, taxForm.value)
            $q.notify({
              color: 'positive',
              message: 'Tax created successfully',
              icon: 'check'
            })
          }
          taxDialog.value = false
          loadTaxes()
        } catch (error) {
          $q.notify({
            color: 'negative',
            message: error.response?.data?.message || 'Failed to save tax',
            icon: 'error'
          })
        } finally {
          saving.value = false
        }
      }
  
      const confirmDelete = (tax) => {
        selectedTax.value = tax
        deleteDialog.value = true
      }
  
      const deleteTax = async () => {
        try {
          await api.delete(`/admin/${route.params.businessId}/taxes/${selectedTax.value.id}`)
          $q.notify({
            color: 'positive',
            message: 'Tax deleted successfully',
            icon: 'check'
          })
          deleteDialog.value = false
          loadTaxes()
        } catch (error) {
          $q.notify({
            color: 'negative',
            message: 'Failed to delete tax',
            icon: 'error'
          })
        }
      }
  
      const toggleTaxStatus = async (tax) => {
        try {
          await api.post(`/admin/${route.params.businessId}/taxes/${tax.id}/toggle`)
          $q.notify({
            color: 'positive',
            message: 'Tax status updated successfully',
            icon: 'check'
          })
          loadTaxes()
        } catch (error) {
          $q.notify({
            color: 'negative',
            message: 'Failed to update tax status',
            icon: 'error'
          })
          // Revert the toggle if the API call fails
          tax.is_active = !tax.is_active
        }
      }
  
      onMounted(() => {
        loadTaxes()
      })
  
      return {
        loading,
        saving,
        taxes,
        columns,
        pagination,
        taxDialog,
        deleteDialog,
        isEditing,
        taxForm,
        openTaxDialog,
        saveTax,
        confirmDelete,
        deleteTax,
        toggleTaxStatus
      }
    }
  })
  </script>