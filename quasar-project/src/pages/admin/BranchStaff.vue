<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">
        <template v-if="branchDetails?.data">
          Staff of Branch - {{ branchDetails.data.name }}
        </template>
        <template v-else>
          Staff of Branch - Loading...
        </template>
      </div>
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

    <!-- Staff Grid -->
    <div class="row q-col-gutter-md">
      <div
        v-for="staff in staffMembers"
        :key="staff.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3"
      >
        <q-card class="staff-card">
          <q-card-section class="text-center">
            <q-avatar size="100px" class="q-mb-md">
              <q-img
                :src="staff.avatar || 'https://cdn.quasar.dev/img/avatar.png'"
                :ratio="1"
              />
            </q-avatar>
            <div class="text-h6">{{ staff.name }}</div>
            <div class="text-subtitle2 text-grey">{{ staff.email }}</div>
            <q-chip
              :color="getRoleColor(staff.role)"
              text-color="white"
              class="q-mt-sm"
            >
              {{ staff.role }}
            </q-chip>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <div class="row q-col-gutter-sm justify-center">
              <div class="col-12 col-sm-4">
                <q-btn
                  flat
                  dense
                  color="primary"
                  icon="edit"
                  @click="openStaffDialog(staff)"
                >
                  <q-tooltip>Edit</q-tooltip>
                </q-btn>
              </div>
              <div class="col-12 col-sm-4">
                <q-btn
                  flat
                  dense
                  color="info"
                  icon="key"
                  @click="openPasswordDialog(staff)"
                >
                  <q-tooltip>Change Password</q-tooltip>
                </q-btn>
              </div>
              <div class="col-12 col-sm-4">
                <q-btn
                  flat
                  dense
                  color="negative"
                  icon="delete"
                  @click="confirmDelete(staff)"
                >
                  <q-tooltip>Delete</q-tooltip>
                </q-btn>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

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
              :rules="[val => !!val || 'Password is required']"
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
          <q-form @submit="onPasswordChange" class="q-gutter-md">
            <q-input
              v-model="passwordData.new_password"
              label="New Password"
              type="password"
              :rules="[val => !!val || 'New password is required']"
              outlined
            />

            <q-input
              v-model="passwordData.confirm_password"
              label="Confirm Password"
              type="password"
              :rules="[
                val => !!val || 'Confirm password is required',
                val => val === passwordData.new_password || 'Passwords do not match'
              ]"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Change" @click="onPasswordChange" />
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
import { ref, onMounted, computed } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { useBranchManagerStore } from 'src/stores/branchManager'

export default {
  name: 'BranchStaffPage',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const branchStore = useBranchManagerStore()
    const loading = ref(false)
    const staffMembers = ref([])
    const staffDialog = ref(false)
    const passwordDialog = ref(false)
    const deleteDialog = ref(false)
    const editingStaff = ref({
      name: '',
      email: '',
      password: '',
      role: null,
      is_active: true
    })
    const staffToDelete = ref(null)
    const passwordData = ref({
      new_password: '',
      confirm_password: ''
    })

    const roleOptions = [
      { label: 'All', value: null },
      { label: 'Branch Manager', value: 'branch_manager' },
      { label: 'Inventory Manager', value: 'inventory_clerk' },
      { label: 'Cashier', value: 'cashier' },
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

    const getRoleColor = (role) => {
      const colors = {
        manager: 'purple',
        cashier: 'blue',
        staff: 'green'
      }
      return colors[role] || 'grey'
    }

    const fetchBranchDetails = async () => {
      await branchStore.fetchBranchDetails(route.params.businessId, route.params.branchId)
    } 

    const fetchStaff = async () => {
      loading.value = true
      try {
        const response = await api.get(`/business/${route.params.businessId}/branch/${route.params.branchId}/staff`, {
          params: filter.value
        })
        staffMembers.value = response.data
      } catch (error) {
        console.error('Error fetching staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch staff members',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const openStaffDialog = (staff = null) => {
      if (staff) {
        editingStaff.value = { ...staff }
      } else {
        editingStaff.value = {
          name: '',
          email: '',
          password: '',
          role: null,
          is_active: true
        }
      }
      staffDialog.value = true
    }

    const openPasswordDialog = (staff) => {
      editingStaff.value = staff
      passwordData.value = {
        new_password: '',
        confirm_password: ''
      }
      passwordDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingStaff.value.id) {
          await api.put(`/staff/${editingStaff.value.id}`, editingStaff.value)
        } else {
          await api.post('/staff', {
            ...editingStaff.value,
            branch_id: route.params.branchId,
            business_id: route.params.businessId,
          })
        }
        staffDialog.value = false
        fetchStaff()
        $q.notify({
          color: 'positive',
          message: `Staff member ${editingStaff.value.id ? 'updated' : 'created'} succe                                         ssfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save staff member',
          icon: 'error'
        })
      }
    }

    const onPasswordChange = async () => {
      try {
        await api.put(`/staff/${editingStaff.value.id}/password`, {
          password: passwordData.value.new_password
        })
        passwordDialog.value = false
        $q.notify({
          color: 'positive',
          message: 'Password changed successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error changing password:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to change password',
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
          message: 'Staff member deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting staff:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete staff member',
          icon: 'error'
        })
      }
    }

    const onSearch = () => {
      fetchStaff()
    }

    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    onMounted(() => {
      fetchStaff()
      fetchBranchDetails()
    })

    return {
      loading,
      staffMembers,
      roleOptions,
      statusOptions,
      filter,
      staffDialog,
      passwordDialog,
      deleteDialog,
      editingStaff,
      passwordData,
      branchDetails: computed(() => branchStore.branchDetails),
      getRoleColor,
      openStaffDialog,
      openPasswordDialog,
      onSubmit,
      onPasswordChange,
      confirmDelete,
      onDelete,
      onSearch,
      isValidEmail
    }
  }
}
</script>

<style lang="scss" scoped>
.staff-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  background: linear-gradient(145deg, #ffffff, #f5f5f5);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  }

  .q-avatar {
    border: 3px solid #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
}
</style> 