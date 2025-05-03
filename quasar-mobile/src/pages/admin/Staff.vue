<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Staff</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Staff"
        @click="openStaffDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search staff..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.role"
          :options="roleOptions"
          dense
          outlined
          label="Filter by Role"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
      <div class="col-12 col-md-4">
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

    <!-- Staff Table -->
    <q-table
      :rows="staff"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Role Column -->
      <template v-slot:body-cell-role="props">
        <q-td :props="props">
          <q-chip
            :color="getRoleColor(props.row.role)"
            text-color="white"
            dense
          >
            {{ props.row.role }}
          </q-chip>
        </q-td>
      </template>

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
            @click="openStaffDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="key"
            @click="openPasswordDialog(props.row)"
          >
            <q-tooltip>Change Password</q-tooltip>
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

    <!-- Staff Dialog -->
    <q-dialog v-model="staffDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingStaff.id ? 'Edit' : 'Add' }} Staff</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingStaff.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingStaff.email"
              label="Email"
              type="email"
              :rules="[
                val => !!val || 'Email is required',
                val => isValidEmail(val) || 'Invalid email format'
              ]"
              outlined
            />

            <q-input
              v-if="!editingStaff.id"
              v-model="editingStaff.password"
              label="Password"
              type="password"
              :rules="[
                val => !editingStaff.id || !!val || 'Password is required',
                val => !editingStaff.id || val.length >= 8 || 'Password must be at least 8 characters'
              ]"
              outlined
            />

            <q-select
              v-model="editingStaff.role"
              :options="roleOptions"
              label="Role"
              :rules="[val => !!val || 'Role is required']"
              outlined
              emit-value
              map-options
            />

            <q-select
              v-model="editingStaff.branch_id"
              :options="branches"
              label="Branch"
              :rules="[val => !!val || 'Branch is required']"
              outlined
              emit-value
              map-options
            />

            <q-toggle
              v-model="editingStaff.is_active"
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

    <!-- Password Dialog -->
    <q-dialog v-model="passwordDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Change Password</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onPasswordSubmit" class="q-gutter-md">
            <q-input
              v-model="passwordData.password"
              label="New Password"
              type="password"
              :rules="[
                val => !!val || 'Password is required',
                val => val.length >= 8 || 'Password must be at least 8 characters'
              ]"
              outlined
            />

            <q-input
              v-model="passwordData.password_confirmation"
              label="Confirm Password"
              type="password"
              :rules="[
                val => !!val || 'Please confirm password',
                val => val === passwordData.password || 'Passwords do not match'
              ]"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onPasswordSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this staff member?</span>
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
import { useRoute } from 'vue-router'

export default {
  name: 'StaffPage',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const staff = ref([])
    const branches = ref([])
    const staffDialog = ref(false)
    const passwordDialog = ref(false)
    const deleteDialog = ref(false)
    const editingStaff = ref({
      name: '',
      email: '',
      password: '',
      role: '',
      branch_id: null,
      is_active: true
    })
    const passwordData = ref({
      password: '',
      password_confirmation: ''
    })
    const staffToDelete = ref(null)

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'email', label: 'Email', field: 'email', sortable: true },
      { name: 'role', label: 'Role', field: 'role', sortable: true },
      { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
      { name: 'status', label: 'Status', field: 'status' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const roleOptions = [
      { label: 'All', value: null },
      { label: 'Branch Manager', value: 'branch_manager' },
      { label: 'Cashier', value: 'cashier' },
      { label: 'Inventory Clerk', value: 'inventory_clerk' }
    ]

    const statusOptions = [
      { label: 'All', value: null },
      { label: 'Active', value: true },
      { label: 'Inactive', value: false }
    ]

    const filter = ref({
      search: '',
      role: null,
      status: null
    })

    const pagination = ref({
      sortBy: 'name',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const fetchStaff = async () => {
      loading.value = true
      try {
        const response = await api.get(`/business/${route.params.businessId}/staff`, {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        staff.value = response.data
        pagination.value.rowsNumber = response.data.length
      } catch (error) {
        console.error('Error fetching staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch staff',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchBranches = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/branches`)
        branches.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        console.error('Error fetching branches:', error)
      }
    }

    const openStaffDialog = (staff = null) => {
      if (staff) {
        editingStaff.value = { ...staff, password: '' }
      } else {
        editingStaff.value = {
          name: '',
          email: '',
          password: '',
          role: '',
          branch_id: null,
          is_active: true
        }
      }
      staffDialog.value = true
    }

    const openPasswordDialog = (staff) => {
      editingStaff.value = staff
      passwordData.value = {
        password: '', 
        password_confirmation: ''
      }
      passwordDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingStaff.value.id) {
          const { password, ...updateData } = editingStaff.value
          await api.put(`/staff/${editingStaff.value.id}`, updateData)
        } else {
          await api.post('/staff', editingStaff.value)
        }
        staffDialog.value = false
        fetchStaff()
        $q.notify({
          color: 'positive',
          message: `Staff ${editingStaff.value.id ? 'updated' : 'created'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save staff',
          icon: 'error'
        })
      }
    }

    const onPasswordSubmit = async () => {
      try {
        await api.put(`/staff/${editingStaff.value.id}/password`, passwordData.value)
        passwordDialog.value = false
        $q.notify({
          color: 'positive',
          message: 'Password updated successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error updating password:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to update password',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (staff) => {
      staffToDelete.value = staff
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/staff/${staffToDelete.value.id}`)
        fetchStaff()
        $q.notify({
          color: 'positive',
          message: 'Staff deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete staff',
          icon: 'error'
        })
      }
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchStaff()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchStaff()
    }

    const getRoleColor = (role) => {
      const colors = {
        branch_manager: 'purple',
        cashier: 'green',
        inventory_clerk: 'orange'
      }
      return colors[role] || 'grey'
    }

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    onMounted(() => {
      fetchStaff()
      fetchBranches()
    })

    return {
      loading,
      staff,
      branches,
      columns,
      roleOptions,
      statusOptions,
      filter,
      pagination,
      staffDialog,
      passwordDialog,
      deleteDialog,
      editingStaff,
      passwordData,
      openStaffDialog,
      openPasswordDialog,
      onSubmit,
      onPasswordSubmit,
      confirmDelete,
      onDelete,
      onRequest,
      onSearch,
      getRoleColor,
      isValidEmail
    }
  }
}
</script> 