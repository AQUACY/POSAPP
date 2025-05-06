<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Your Business</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Business"
        @click="openBusinessDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search businesses..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-6">
        <q-select
          v-model="filter.status"
          :options="statusOptions"
          dense
          outlined
          label="Status"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
    </div>

    <!-- Businesses Table -->
    <q-table
      :rows="businesses"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.is_active ? 'positive' : 'negative'"
            text-color="white"
            dense
          >
            {{ props.row.is_active ? 'Active' : 'Inactive' }}
          </q-chip>
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
            @click="openBusinessDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="store"
            @click="viewBranches(props.row)"
          >
            <q-tooltip>View Branches</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="negative"
            icon="delete"
            @click="confirmDelete(props.row)"
          >
            <q-tooltip>Delete</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Business Dialog -->
    <q-dialog v-model="businessDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingBusiness.id ? 'Edit' : 'Add' }} Business</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingBusiness.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingBusiness.description"
              label="Description"
              type="textarea"
              outlined
            />

            <q-input
              v-model="editingBusiness.address"
              label="Address"
              :rules="[val => !!val || 'Address is required']"
              outlined
            />

            <q-input
              v-model="editingBusiness.phone"
              label="Phone"
              :rules="[val => !!val || 'Phone is required']"
              outlined
            />

            <q-input
              v-model="editingBusiness.email"
              label="Email"
              type="email"
              :rules="[
                val => !!val || 'Email is required',
                val => isValidEmail(val) || 'Invalid email format'
              ]"
              outlined
            />

            <q-toggle
              v-model="editingBusiness.is_active"
              label="Active"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this business?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="onDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

export default {
  name: 'BusinessesPage',

  setup () {
    const $q = useQuasar()
    const router = useRouter()
    const loading = ref(false)
    const businesses = ref([])
    const businessDialog = ref(false)
    const deleteDialog = ref(false)
    const editingBusiness = ref({
      name: '',
      description: '',
      address: '',
      phone: '',
      email: '',
      is_active: true
    })
    const businessToDelete = ref(null)

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'description', label: 'Description', field: 'description' },
      { name: 'address', label: 'Address', field: 'address' },
      { name: 'phone', label: 'Phone', field: 'phone' },
      { name: 'email', label: 'Email', field: 'email' },
      { name: 'status', label: 'Status', field: 'is_active' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const statusOptions = [
      { label: 'All', value: null },
      { label: 'Active', value: true },
      { label: 'Inactive', value: false }
    ]

    const filter = ref({
      search: '',
      status: null
    })

    const pagination = ref({
      sortBy: 'name',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const fetchBusinesses = async () => {
      loading.value = true
      try {
        const response = await api.get('/businesses', {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        businesses.value = response.data.data
        pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Error fetching businesses:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch businesses',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const openBusinessDialog = (business = null) => {
      if (business) {
        editingBusiness.value = { ...business }
      } else {
        editingBusiness.value = {
          name: '',
          description: '',
          address: '',
          phone: '',
          email: '',
          is_active: true
        }
      }
      businessDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingBusiness.value.id) {
          await api.put(`/businesses/${editingBusiness.value.id}`, editingBusiness.value)
        } else {
          await api.post('/businesses', editingBusiness.value)
        }
        businessDialog.value = false
        fetchBusinesses()
        $q.notify({
          color: 'positive',
          message: `Business ${editingBusiness.value.id ? 'updated' : 'created'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving business:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save business',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (business) => {
      businessToDelete.value = business
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/businesses/${businessToDelete.value.id}`)
        fetchBusinesses()
        $q.notify({
          color: 'positive',
          message: 'Business deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting business:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete business',
          icon: 'error'
        })
      }
    }

    const viewBranches = (business) => {
      router.push(`/admin/branches?business_id=${business.id}`)
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchBusinesses()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchBusinesses()
    }

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    onMounted(() => {
      fetchBusinesses()
    })

    return {
      loading,
      businesses,
      columns,
      statusOptions,
      filter,
      pagination,
      businessDialog,
      deleteDialog,
      editingBusiness,
      openBusinessDialog,
      onSubmit,
      confirmDelete,
      onDelete,
      viewBranches,
      onRequest,
      onSearch,
      isValidEmail
    }
  }
}
</script>